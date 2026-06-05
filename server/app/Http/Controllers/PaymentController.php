<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private NowPaymentsService $nowPayments) {}

    // Step 1 — Student selects crypto and initiates payment
    public function initiatePayment(Request $request, $courseId)
    {
        $request->validate([
            'pay_currency' => ['required', 'string'],
        ]);

        $user   = auth()->user();
        $course = Course::findOrFail($courseId);

        // Check already enrolled
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Already enrolled.'], 409);
        }

        // Check for existing pending order
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

        // Create order in database first
        $order = Order::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'amount'      => $course->price,
            'currency'    => 'USD',
            'status'      => 'waiting',
            'pay_currency' => $request->pay_currency,
        ]);

        // Call NOWPayments API
        $payment = $this->nowPayments->createPayment([
            'amount'      => $course->price,
            'pay_currency' => $request->pay_currency,
            'order_id'    => $order->uuid,
            'description' => "Enrollment: {$course->title}",
        ]);

        if (!isset($payment['payment_id'])) {
            $order->update(['status' => 'failed']);
            return response()->json(['message' => 'Failed to create payment. Please try again.'], 500);
        }

        // Update order with NOWPayments details
        $order->update([
            'nowpayments_payment_id' => $payment['payment_id'],
            'nowpayments_order_id'   => $payment['order_id'] ?? null,
            'pay_amount'             => $payment['pay_amount'] ?? null,
        ]);

        return response()->json([
            'payment_id'      => $payment['payment_id'],
            'pay_address'     => $payment['pay_address'],
            'pay_amount'      => $payment['pay_amount'],
            'pay_currency'    => $payment['pay_currency'],
            'order_uuid'      => $order->uuid,
            'expiration_estimate_date' => $payment['expiration_estimate_date'] ?? null,
        ]);
    }

    // Step 2 — Poll payment status from frontend
    public function checkStatus(Request $request, $orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'status'       => $order->status,
            'is_enrolled'  => $order->status === 'finished',
            'course_id'    => $order->course_id,
        ]);
    }

    // Step 3 — NOWPayments sends webhook
    public function webhook(Request $request)
    {
        // Verify signature
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

        // Update order status and actually paid amount
        $order->update([
            'status'        => $data['payment_status'],
            'actually_paid' => $data['actually_paid'] ?? $order->actually_paid,
            'paid_at'       => $data['payment_status'] === 'finished' ? now() : null,
        ]);

        // If finished — enroll the student
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
