<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;  // <-- TAMBAHIN INI
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// GANTI JADI INI:
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/category/{category:slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');
// Cart Routes (Guest)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('add');
    Route::put('/update/{id}', [App\Http\Controllers\CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [App\Http\Controllers\CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('clear');
    Route::get('/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
});
// Routes admin
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
});
Route::get('/dashboard', function () {
    $totalProducts = App\Models\Product::count();
    $totalCategories = App\Models\Category::count();
    $totalOrders = App\Models\Order::count();
    $recentOrders = App\Models\Order::with('items')->latest()->take(5)->get();
    $lowStockProducts = App\Models\Product::where('stock', '<', 5)->take(5)->get();
    
    return view('dashboard', compact(
        'totalProducts', 
        'totalCategories', 
        'totalOrders', 
        'recentOrders',
        'lowStockProducts'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
