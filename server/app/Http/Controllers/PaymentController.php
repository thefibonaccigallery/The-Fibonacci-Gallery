<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private NowPaymentsService $nowPayments) {}

    public function initiatePayment(Request $request, string $uuid)
    {
        $request->validate([
            'pay_currency' => ['required', 'string'],
        ]);

        $user   = auth()->user();
        $course = Course::where('uuid', $uuid)->firstOrFail();

        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Already enrolled.'], 409);
        }

        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['waiting', 'confirming'])
            ->first();

        if ($existingOrder) {
            return response()->json([
                'message' => 'You already have a pending payment for this course.',
                'order'   => $existingOrder,
            ], 409);
        }

        $order = Order::create([
            'user_id'      => $user->id,
            'course_id'    => $course->id,
            'amount'       => $course->price,
            'currency'     => 'USD',
            'status'       => 'waiting',
            'pay_currency' => $request->pay_currency,
        ]);

        $payment = $this->nowPayments->createPayment([
            'amount'       => $course->price,
            'pay_currency' => $request->pay_currency,
            'order_id'     => $order->uuid,
            'description'  => "Enrollment: {$course->title}",
        ]);

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
            'pay_amount'             => $payment['pay_amount'] ?? null,
        ]);

        return response()->json([
            'payment_id'               => $payment['payment_id'],
            'pay_address'              => $payment['pay_address'],
            'pay_amount'               => $payment['pay_amount'],
            'pay_currency'             => $payment['pay_currency'],
            'order_uuid'               => $order->uuid,
            'expiration_estimate_date' => $payment['expiration_estimate_date'] ?? null,
        ]);
    }

    public function checkStatus(string $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $course = Course::find($order->course_id);

        return response()->json([
            'status'      => $order->status,
            'is_enrolled' => $order->status === 'finished',
            'course_uuid' => $course?->uuid,
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

        $order->update([
            'status'        => $data['payment_status'],
            'actually_paid' => $data['actually_paid'] ?? $order->actually_paid,
            'paid_at'       => $data['payment_status'] === 'finished' ? now() : null,
        ]);

        if ($data['payment_status'] === 'finished') {
            Enrollment::firstOrCreate([
                'user_id'   => $order->user_id,
                'course_id' => $order->course_id,
            ], [
                'enrolled_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Webhook received.']);
    }
}
