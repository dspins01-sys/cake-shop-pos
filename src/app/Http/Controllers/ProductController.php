<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
{
    $categories = Category::all();
    $products = Product::with('category')
        ->where('is_active', true)
        ->latest()
        ->paginate(12);
    
    // Hitung pending orders untuk semua produk (optimasi)
    $productIds = $products->pluck('id');
    $pendingCounts = \App\Models\OrderItem::whereIn('product_id', $productIds)
        ->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'waiting_confirmation']))
        ->groupBy('product_id')
        ->selectRaw('product_id, sum(quantity) as total_pending')
        ->pluck('total_pending', 'product_id');
    
    return view('products.index', compact('products', 'categories', 'pendingCounts'));
}

public function category(Category $category)
{
    $categories = Category::all();
    $products = Product::with('category')
        ->where('category_id', $category->id)
        ->where('is_active', true)
        ->latest()
        ->paginate(12);
    
    $productIds = $products->pluck('id');
    $pendingCounts = \App\Models\OrderItem::whereIn('product_id', $productIds)
        ->whereHas('order', fn($q) => $q->whereIn('status', ['pending', 'waiting_confirmation']))
        ->groupBy('product_id')
        ->selectRaw('product_id, sum(quantity) as total_pending')
        ->pluck('total_pending', 'product_id');
    
    return view('products.index', compact('products', 'categories', 'category', 'pendingCounts'));
}

   public function show(Product $product)
{
    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->where('is_active', true)
        ->limit(4)
        ->get();
    
    return view('products.show', compact('product', 'relatedProducts'));
}
}