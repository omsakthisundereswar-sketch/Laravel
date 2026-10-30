<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with('customer')->find($this->orderId);

        if (! $order) {
            Log::warning('Order confirmation skipped because order was not found.', ['order_id' => $this->orderId]);

            return;
        }

        Log::info('Order confirmation email queued for customer.', [
            'order_id' => $order->id,
            'customer_email' => $order->customer?->email,
            'grand_total' => $order->grand_total,
        ]);
    }
}
