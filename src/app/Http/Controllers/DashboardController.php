<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function index()
    {
        // Cek apakah user adalah admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $recentOrders = Order::with('items')->latest()->take(5)->get();
        $lowStockProducts = Product::where('stock', '<', 5)->take(5)->get();
        
        // ========== DATA UNTUK SCHEDULER STATUS BOX ==========
        
        // Expired orders today
        $expiredOrdersToday = Order::where('status', 'cancelled')
            ->where('payment_status', 'expired')
            ->whereDate('updated_at', today())
            ->count();
        
        // Last expired check - ambil dari log atau cache
        $lastExpiredCheck = Cache::get('last_expired_check', 'Today');
        
        // Update last check time
        if ($expiredOrdersToday > 0) {
            Cache::put('last_expired_check', now()->format('H:i:s'), 3600);
        }
        
        // Queue stats
        $pendingJobs = 0;
        $processedJobsToday = Cache::get('processed_jobs_today', 0);
        
        try {
            // Cek queue size dari Redis
            $pendingJobs = Redis::llen('queues:default');
        } catch (\Exception $e) {
            $pendingJobs = 0;
        }
        
        // System status - check container langsung
        $systemStatus = [
            'scheduler' => $this->checkScheduler(),
            'queue' => $this->checkQueue(),
            'redis' => $this->checkRedis(),
            'mysql' => $this->checkMySQL(),
        ];
        
        return view('dashboard', compact(
            'totalProducts', 
            'totalCategories', 
            'totalOrders', 
            'recentOrders',
            'lowStockProducts',
            'expiredOrdersToday',
            'lastExpiredCheck',
            'pendingJobs',
            'processedJobsToday',
            'systemStatus'
        ));
    }
    
    /**
     * Get scheduler status for AJAX refresh
     */
    public function schedulerStatus()
    {
        try {
            // Expired orders today
            $expiredOrdersToday = Order::where('status', 'cancelled')
                ->where('payment_status', 'expired')
                ->whereDate('updated_at', today())
                ->count();
            
            // Update last check time
            if ($expiredOrdersToday > 0) {
                Cache::put('last_expired_check', now()->format('H:i:s'), 3600);
            }
            
            return response()->json([
                'expired_orders_today' => $expiredOrdersToday,
                'last_expired_check' => Cache::get('last_expired_check', 'Today'),
                'pending_jobs' => Redis::llen('queues:default'),
                'processed_jobs_today' => Cache::get('processed_jobs_today', 0),
                'system_status' => [
                    'scheduler' => $this->checkScheduler(),
                    'queue' => $this->checkQueue(),
                    'redis' => $this->checkRedis(),
                    'mysql' => $this->checkMySQL(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Run manual task
     */
    public function runTask(Request $request)
    {
        $task = $request->task;
        
        if ($task === 'orders:cancel-expired') {
            // Run expired check manually
            \Artisan::call('orders:cancel-expired');
            $output = \Artisan::output();
            
            // Update last check time
            Cache::put('last_expired_check', now()->format('H:i:s'), 3600);
            
            return response()->json([
                'success' => true,
                'message' => 'Expired orders check completed',
                'output' => $output
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unknown task'
        ]);
    }
    
    /**
     * Check expired orders manually
     */
    public function checkExpired()
    {
        \Artisan::call('orders:cancel-expired');
        $output = \Artisan::output();
        
        // Update last check time
        Cache::put('last_expired_check', now()->format('H:i:s'), 3600);
        
        return response()->json([
            'success' => true,
            'message' => 'Expired orders checked successfully',
            'output' => $output
        ]);
    }
    
    /**
     * Restart queue worker
     */
    public function restartQueue()
    {
        try {
            // Restart queue worker container
            shell_exec('docker restart cake-queue 2>/dev/null');
            
            return response()->json([
                'success' => true,
                'message' => 'Queue worker restarted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart queue worker: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if scheduler is running (container based)
     */
    /**
 * Check if scheduler is running (based on last activity)
 */
private function checkScheduler()
{
    try {
        // Cek log terakhir scheduler
        $lastLog = Cache::get('scheduler_last_run', null);
        
        if ($lastLog && now()->diffInMinutes($lastLog) < 5) {
            return 'Running';
        }
        
        // Alternative: cek apakah ada file log yang baru di-update
        $logFile = storage_path('logs/scheduler.log');
        if (file_exists($logFile) && (time() - filemtime($logFile)) < 300) {
            return 'Running';
        }
        
        // Cek dari container via HTTP request (optional)
        // Bisa juga cek dari proses yang jalan
        $output = shell_exec('ps aux | grep "orders:cancel-expired" | grep -v grep');
        if ($output) {
            return 'Running';
        }
        
        return 'Not Running';
    } catch (\Exception $e) {
        return 'Unknown';
    }
}

/**
 * Check if queue worker is running
 */
private function checkQueue()
{
    try {
        // Cek apakah ada pending jobs yang terproses
        $lastQueueJob = Cache::get('queue_last_job', null);
        
        if ($lastQueueJob && now()->diffInMinutes($lastQueueJob) < 10) {
            return 'Active';
        }
        
        // Cek dari proses
        $output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
        if ($output) {
            return 'Active';
        }
        
        return 'Not Active';
    } catch (\Exception $e) {
        return 'Unknown';
    }
}
    /**
     * Check Redis connection
     */
    private function checkRedis()
    {
        try {
            Redis::ping();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }
    
    /**
     * Check MySQL connection
     */
    private function checkMySQL()
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }
}