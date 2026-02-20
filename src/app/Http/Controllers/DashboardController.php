<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Cek admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // STATS
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        
        // LOW STOCK (produk dengan stok < 5)
        $lowStockProducts = Product::with('category')
            ->where('stock', '<', 5)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();
        
        // RECENT ORDERS (5 order terbaru)
        $recentOrders = Order::with('items')
            ->latest()
            ->take(5)
            ->get();
        
        // QUICK ACTIONS (nanti di view)
        
        return view('dashboard', compact(
            'totalProducts',
            'totalCategories', 
            'totalOrders',
            'lowStockProducts',
            'recentOrders'
        ));
    }
}