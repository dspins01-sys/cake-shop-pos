<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;  // <-- TAMBAHIN INI
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;


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
    // Di routes/web.php, tambahin ini di group cart routes:
Route::post('/checkout/process', [App\Http\Controllers\CartController::class, 'process'])->name('checkout.process');
});
// Routes admin - HANYA UNTUK ADMIN
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('dashboard');
    Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Invoice routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/invoice/{order}', [InvoiceController::class, 'view'])->name('invoice.view');
    Route::get('/invoice/{order}/download', [InvoiceController::class, 'generate'])->name('invoice.download');
});


require __DIR__.'/auth.php';
