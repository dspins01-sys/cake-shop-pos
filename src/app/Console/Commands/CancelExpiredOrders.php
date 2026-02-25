<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use App\Jobs\SendExpiredOrderWhatsApp;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel orders that have expired payment time';

    public function handle()
    {
        $this->info('Checking expired orders...');

        $count = 0;

        Order::whereIn('status', ['pending', 'waiting_confirmation'])
        ->where('expired_at', '<', now())
        ->where('payment_status', '!=', 'paid')
        ->chunkById(100, function ($orders) use (&$count) {

            foreach ($orders as $order) {

                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired'
                ]);

                SendExpiredOrderWhatsApp::dispatch($order);

                $count++;
            }
        });

        $this->info("{$count} expired orders have been cancelled.");
    }
}