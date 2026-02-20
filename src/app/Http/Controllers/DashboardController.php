<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Cek admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Ambil data
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        // Kirim ke view
        return view('dashboard', compact('totalProducts', 'totalCategories'));
    }
}