<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NowPaymentsService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.nowpayments.api_key');
        $this->apiUrl = config('services.nowpayments.api_url');
    }

    // Create a payment
    public function createPayment(array $data): array
    {
        $response = Http::withHeaders([
            'x-api-key'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/payment", [
            'price_amount'     => $data['amount'],
            'price_currency'   => 'usd',
            'pay_currency'     => $data['pay_currency'], // e.g. 'btc', 'eth', 'usdttrc20'
            'order_id'         => $data['order_id'],
            'order_description' => $data['description'],
            'ipn_callback_url' => route('payments.webhook'),
            'success_url'      => config('app.frontend_url') . '/payment/success',
            'cancel_url'       => config('app.frontend_url') . '/payment/cancel',
        ]);

        return $response->json();
    }

    // Get payment status
    public function getPaymentStatus(string $paymentId): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->get("{$this->apiUrl}/payment/{$paymentId}");

        return $response->json();
    }

    // Verify IPN webhook signature
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac(
            'sha512',
            $payload,
            config('services.nowpayments.ipn_secret')
        );

        return hash_equals($expectedSignature, $signature);
    }
}
