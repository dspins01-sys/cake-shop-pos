<?php
// queue_debug.php
echo "=== Laravel Queue + Redis Debug ===\n";

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Redis;
use App\Jobs\SendExpiredOrderWhatsApp;
use App\Models\Order;

// Pastikan environment pakai Redis
config(['queue.default' => 'redis']);
config(['database.redis.default.host' => env('REDIS_HOST', 'redis')]);
config(['database.redis.default.port' => env('REDIS_PORT', 6379)]);

// Cek koneksi Redis
try {
    $pong = Redis::ping();
    echo "✅ Redis connected! PING response: $pong\n";
} catch (\Exception $e) {
    echo "❌ Redis connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Cek panjang queue
$queueLength = Redis::llen('queues:default');
echo "🔹 Length of 'queues:default': $queueLength\n";

// Optional: dispatch dummy job
$order = Order::latest()->first();
if ($order) {
    SendExpiredOrderWhatsApp::dispatch($order);
    echo "🚀 Job dispatched to queue!\n";
    echo "🔹 New queue length: " . Redis::llen('queues:default') . "\n";
} else {
    echo "⚠️ No orders found to dispatch.\n";
}

echo "=== Debug finished ===\n";
