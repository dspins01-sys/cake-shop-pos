<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Jobs\SendExpiredOrderWhatsApp;
use App\Models\Order;

class QueueDebug extends Command
{
    protected $signature = 'queue:debug';
    protected $description = 'Debug Laravel Queue + Redis connection';

    public function handle()
    {
        $this->info('=== Laravel Queue + Redis Debug ===');

        // Cek Redis
        try {
            $ping = Redis::ping();
            $this->info("✅ Redis connected! PING response: $ping");
        } catch (\Exception $e) {
            $this->error("❌ Redis connection failed: ".$e->getMessage());
            return 1;
        }

        // Cek panjang queue
        $length = Redis::llen('queues:default');
        $this->info("🔹 Length of 'queues:default': $length");

        // Dispatch job test
        $order = Order::latest()->first();
        if ($order) {
            SendExpiredOrderWhatsApp::dispatch($order);
            $newLength = Redis::llen('queues:default');
            $this->info("🚀 Job dispatched to queue!");
            $this->info("🔹 New queue length: $newLength");
        } else {
            $this->warn("⚠ No orders found to dispatch");
        }

        $this->info('=== Debug finished ===');
        return 0;
    }
}