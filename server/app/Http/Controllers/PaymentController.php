<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(private NowPaymentsService $nowPayments) {}

    public function currencies()
    {
        return response()->json(config('types.order.accepted_currencies'));
    }

    public function initiatePayment(InitiatePaymentRequest $request, string $uuid)
    {

        $user   = auth()->user();
        $course = Course::where('uuid', $uuid)->firstOrFail();

        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Already enrolled.'], 409);
        }

        // Resolve coupon
        $coupon   = null;
        $couponId = null;
        $amount   = (float) $course->price;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))
                ->where('course_id', $course->id)
                ->first();

            if ($coupon && $coupon->isValid()) {
                $discount = $amount * $coupon->discount_percentage / 100;
                $amount   = max(0, $amount - $discount);
                $couponId = $coupon->id;
            }
        }

        // 100% coupon — enroll directly without payment
        if ($amount == 0 && $couponId) {
            $enrollment = Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['enrolled_at' => now(), 'type' => 'coupon', 'coupon_id' => $couponId]
            );

            return response()->json([
                'enrolled'   => true,
                'message'    => 'Enrolled successfully with coupon.',
                'course_uuid' => $course->uuid,
            ]);
        }

        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['waiting', 'confirming'])
            ->first();

        if ($existingOrder) {
            // Check if order has expired based on timestamp
            if ($existingOrder->expires_at && $existingOrder->expires_at < now()) {
                $existingOrder->update(['status' => 'expired']);
                // Continue to create new order below
            }
            // Previous attempt failed before NowPayments responded — delete and retry
            elseif (!$existingOrder->nowpayments_payment_id) {
                $existingOrder->delete();
            } else {
                // Check if course price has changed since the order was created
                $priceChanged = abs((float)$existingOrder->amount - $amount) > 0.01;

                // Check if payment currency has changed
                $currencyChanged = $existingOrder->pay_currency !== $request->pay_currency;

                if ($priceChanged || $currencyChanged) {
                    // Price or currency changed - mark old order as expired and create new one
                    $existingOrder->update(['status' => 'expired']);
                } else {
                    // Check if the payment is still active on NowPayments
                    try {
                        $liveStatus = $this->nowPayments->getPaymentStatus($existingOrder->nowpayments_payment_id);

                        // If payment has expired/failed on NowPayments, update our status and allow new payment
                        if (isset($liveStatus['payment_status']) && in_array($liveStatus['payment_status'], ['expired', 'failed', 'refunded'])) {
                            $existingOrder->update(['status' => $liveStatus['payment_status']]);
                            // Payment is no longer active, allow creating a new one
                        } else {
                            // Valid pending payment — return stored details so frontend can restore step 2
                            return response()->json([
                                'payment_id'               => $existingOrder->nowpayments_payment_id,
                                'pay_address'              => $existingOrder->pay_address,
                                'pay_amount'               => $existingOrder->pay_amount,
                                'pay_currency'             => $existingOrder->pay_currency,
                                'order_uuid'               => $existingOrder->uuid,
                                'expiration_estimate_date' => $existingOrder->expires_at?->toIso8601String(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        // If we can't check NowPayments, assume the payment is still active
                        return response()->json([
                            'payment_id'               => $existingOrder->nowpayments_payment_id,
                            'pay_address'              => $existingOrder->pay_address,
                            'pay_amount'               => $existingOrder->pay_amount,
                            'pay_currency'             => $existingOrder->pay_currency,
                            'order_uuid'               => $existingOrder->uuid,
                            'expiration_estimate_date' => $existingOrder->expires_at?->toIso8601String(),
                        ]);
                    }
                }
            }
        }

        $order = Order::create([
            'user_id'      => $user->id,
            'course_id'    => $course->id,
            'coupon_id'    => $couponId,
            'amount'       => $amount,
            'currency'     => 'USD',
            'status'       => 'waiting',
            'pay_currency' => $request->pay_currency,
        ]);

        $minAmount = $this->nowPayments->getMinAmount($request->pay_currency);
        if ($amount < $minAmount) {
            $order->update(['status' => 'failed']);
            return response()->json([
                'message' => "The minimum payment amount for {$request->pay_currency} is \${$minAmount} USD. Please select a different currency.",
            ], 422);
        }

        $payment = $this->nowPayments->createPayment([
            'amount'       => $amount,
            'pay_currency' => $request->pay_currency,
            'order_id'     => $order->uuid,
            'description'  => "Enrollment: {$course->title} | {$user->email}",
        ]);

        \Log::info('NOWPayments full response', $payment);

        if (!isset($payment['payment_id'])) {
            $order->update(['status' => 'failed']);
            $apiMessage = $payment['message'] ?? ($payment['error'] ?? null);
            return response()->json([
                'message' => $apiMessage ?? 'Failed to create payment. Please try again.',
            ], 500);
        }

        $order->update([
            'nowpayments_payment_id' => $payment['payment_id'],
            'nowpayments_order_id'   => $payment['order_id'] ?? null,
            'pay_address'            => $payment['pay_address'] ?? null,
            'pay_amount'             => $payment['pay_amount'] ?? null,
            'expires_at'             => now()->addDay(),
        ]);

        return response()->json([
            'payment_id'               => $payment['payment_id'],
            'pay_address'              => $payment['pay_address'],
            'pay_amount'               => $payment['pay_amount'],
            'pay_currency'             => $payment['pay_currency'],
            'order_uuid'               => $order->uuid,
            'expiration_estimate_date' => $order->expires_at?->toIso8601String(),
        ]);
    }

    public function checkStatus(string $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $terminalStatuses = ['finished', 'failed', 'expired', 'refunded'];

        // Sync live status from NowPayments while the payment is still active.
        // This is a fallback for when the webhook is delayed or missed.
        if ($order->nowpayments_payment_id && !in_array($order->status, $terminalStatuses)) {
            $live = $this->nowPayments->getPaymentStatus($order->nowpayments_payment_id);

            if (isset($live['payment_status']) && $live['payment_status'] !== $order->status) {
                $newStatus = $live['payment_status'];

                DB::transaction(function () use ($order, $live, $newStatus) {
                    $order->update([
                        'status'        => $newStatus,
                        'actually_paid' => $live['actually_paid'] ?? $order->actually_paid,
                        'paid_at'       => $newStatus === 'finished' ? now() : $order->paid_at,
                    ]);

                    if ($newStatus === 'finished') {
                        Enrollment::firstOrCreate(
                            ['user_id' => $order->user_id, 'course_id' => $order->course_id],
                            [
                                'enrolled_at' => now(),
                                'type'        => $order->coupon_id ? 'coupon' : 'paid',
                                'coupon_id'   => $order->coupon_id,
                            ]
                        );
                    }
                });

                $order->refresh();
            }
        }

        $course = Course::find($order->course_id);

        return response()->json([
            'status'         => $order->status,
            'payment_amount' => (float) $order->amount,
            'actually_paid'  => (float) $order->actually_paid,
            'is_enrolled'    => $order->status === 'finished',
            'course_uuid'    => $course?->uuid,
        ]);
    }

    public function cancelPayment(string $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['waiting', 'confirming'])
            ->firstOrFail();

        // Sync with NowPayments to check current status
        if ($order->nowpayments_payment_id) {
            try {
                $live = $this->nowPayments->getPaymentStatus($order->nowpayments_payment_id);
                
                // If already finished/paid, don't allow cancellation
                if (isset($live['payment_status']) && in_array($live['payment_status'], ['finished', 'confirmed', 'sending'])) {
                    return response()->json([
                        'message' => 'Payment has already been completed or is being processed.',
                        'status' => $live['payment_status'],
                    ], 422);
                }
            } catch (\Exception $e) {
                // Continue with cancellation even if status check fails
                \Log::warning("Could not verify payment status before cancellation for order {$orderUuid}: " . $e->getMessage());
            }
        }

        $order->update(['status' => 'expired']);

        return response()->json([
            'message' => 'Payment cancelled successfully.',
        ]);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-nowpayments-sig');
        $payload   = $request->getContent();

        if (!$this->nowPayments->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $data  = $request->all();
        $order = Order::where('nowpayments_payment_id', $data['payment_id'])->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status'        => $data['payment_status'],
                'actually_paid' => $data['actually_paid'] ?? $order->actually_paid,
                'paid_at'       => $data['payment_status'] === 'finished' ? now() : null,
            ]);

            if ($data['payment_status'] === 'finished') {
                Enrollment::firstOrCreate(
                    ['user_id' => $order->user_id, 'course_id' => $order->course_id],
                    [
                        'enrolled_at' => now(),
                        'type'        => $order->coupon_id ? 'coupon' : 'paid',
                        'coupon_id'   => $order->coupon_id,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Webhook received.']);
    }
}
