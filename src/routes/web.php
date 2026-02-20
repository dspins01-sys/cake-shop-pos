<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;  // <-- TAMBAHIN INI
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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
// Routes admin - HANYA UNTUK ADMIN
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('dashboard');

require __DIR__.'/auth.php';
