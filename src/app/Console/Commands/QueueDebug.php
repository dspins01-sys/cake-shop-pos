<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use App\Jobs\SendExpiredOrderWhatsApp;

class QueueDebug extends Command
{
    protected $signature = 'queue:debug';
    protected $description = 'Debug Redis queue and test job dispatch';

    public function handle()
    {
        $this->info('=== Laravel Queue + Redis Debug ===');

        // Cek koneksi Redis
        try {
            $pong = Redis::ping();
            $this->info("✅ Redis connected! PING response: $pong");
        } catch (\Exception $e) {
            $this->error("❌ Redis connection failed: " . $e->getMessage());
            return 1;
        }

        // Cek panjang queue
        $queueLength = Redis::llen('queues:default');
        $this->info("🔹 Length of 'queues:default': $queueLength");

        // Dispatch job dummy
        $order = Order::latest()->first();
        if ($order) {
            SendExpiredOrderWhatsApp::dispatch($order);
            $this->info("🚀 Job dispatched to queue!");
            $this->info("🔹 New queue length: " . Redis::llen('queues:default'));
        } else {
            $this->warn("⚠️ No orders found to dispatch.");
        }

        $this->info('=== Debug finished ===');
        return 0;
    }
}
