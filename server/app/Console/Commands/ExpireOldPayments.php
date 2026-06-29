<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\NowPaymentsService;
use Illuminate\Console\Command;

class ExpireOldPayments extends Command
{
    protected $signature = 'payments:expire';
    protected $description = 'Check and expire old pending payments from NowPayments';

    public function __construct(private NowPaymentsService $nowPayments)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Checking for expired payments...');

        // Get all pending orders (waiting or confirming status)
        $pendingOrders = Order::whereIn('status', ['waiting', 'confirming'])
            ->whereNotNull('nowpayments_payment_id')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending payments found.');
            return 0;
        }

        $expiredCount = 0;

        foreach ($pendingOrders as $order) {
            try {
                // Check status from NowPayments
                $liveStatus = $this->nowPayments->getPaymentStatus($order->nowpayments_payment_id);

                if (isset($liveStatus['payment_status'])) {
                    $newStatus = $liveStatus['payment_status'];

                    // Update if status has changed
                    if ($newStatus !== $order->status) {
                        $order->update([
                            'status'        => $newStatus,
                            'actually_paid' => $liveStatus['actually_paid'] ?? $order->actually_paid,
                            'paid_at'       => $newStatus === 'finished' ? now() : $order->paid_at,
                        ]);

                        if (in_array($newStatus, ['expired', 'failed'])) {
                            $expiredCount++;
                            $this->line("Order {$order->uuid} marked as {$newStatus}");
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to check order {$order->uuid}: {$e->getMessage()}");
            }
        }

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} payment(s).");
        } else {
            $this->info('No expired payments found.');
        }

        return 0;
    }
}
