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
        $products = Product::with('category')->get();
foreach ($products as $product) {
    $product->available_stock = $product->available_stock; // pake accessor
}
        
        return view('products.index', compact('products', 'categories'));
    }

    public function category(Category $category)
    {
        $categories = Category::all();
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(12);
        
        return view('products.index', compact('products', 'categories', 'category'));
    }

    public function show(Product $product)
{
    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->limit(4)
        ->get();
    
    return view('products.show', compact('product', 'relatedProducts'));
}
}