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
        // Cek apakah user adalah admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $recentOrders = Order::with('items')->latest()->take(5)->get();
        $lowStockProducts = Product::where('stock', '<', 5)->take(5)->get();
        
        return view('dashboard', compact(
            'totalProducts', 
            'totalCategories', 
            'totalOrders', 
            'recentOrders',
            'lowStockProducts'
        ));
    }
}