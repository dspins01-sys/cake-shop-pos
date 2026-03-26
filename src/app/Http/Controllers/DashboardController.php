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
        $lastExpiredCheck = Cache::get('last_expired_check', 'Never');
        
        // System status
        $systemStatus = [
            'scheduler' => $this->checkScheduler(),
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
            
            return response()->json([
                'expired_orders_today' => $expiredOrdersToday,
                'last_expired_check' => Cache::get('last_expired_check', 'Never'),
                'system_status' => [
                    'scheduler' => $this->checkScheduler(),
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
     * Check if scheduler is running
     */
    private function checkScheduler()
    {
        try {
            // Cek dari cache last run
            $lastRun = Cache::get('scheduler_last_run');
            if ($lastRun) {
                $lastRunTime = \Carbon\Carbon::parse($lastRun);
                if (now()->diffInMinutes($lastRunTime) < 10) {
                    return 'Running';
                }
            }
            return 'Not Running';
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