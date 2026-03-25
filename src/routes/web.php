<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;

// Public routes
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
    Route::post('/checkout/process', [App\Http\Controllers\CartController::class, 'process'])->name('checkout.process');
});

// ============= ORDER ROUTES =============
// Setelah checkout - KIRIM WA (sekali)
Route::get('/order/success/{order}', [App\Http\Controllers\OrderController::class, 'checkoutSuccess'])->name('order.success');

// Halaman upload bukti - TANPA WA
Route::get('/order/upload/{order}', [App\Http\Controllers\OrderController::class, 'uploadPage'])->name('order.upload');

// Short link - TANPA WA
Route::get('/o/{order}', [App\Http\Controllers\OrderController::class, 'uploadPage'])->name('order.short');

// Upload proof action
Route::post('/order/upload-proof/{order}', [App\Http\Controllers\OrderController::class, 'uploadProof'])->name('order.upload-proof');

// Tracking
Route::get('/track', [App\Http\Controllers\OrderController::class, 'trackForm'])->name('order.track.form');
Route::post('/track', [App\Http\Controllers\OrderController::class, 'track'])->name('order.track');

// ============= ADMIN ORDER MANAGEMENT =============
Route::prefix('admin/orders')->name('admin.orders.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('show');
    Route::post('/{order}/confirm-payment', [App\Http\Controllers\OrderController::class, 'confirmPayment'])->name('confirm-payment');
    Route::post('/{order}/process', [App\Http\Controllers\OrderController::class, 'processOrder'])->name('process');
    Route::post('/{order}/complete', [App\Http\Controllers\OrderController::class, 'complete'])->name('complete');
    Route::post('/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('cancel');
    Route::delete('/{order}', [App\Http\Controllers\OrderController::class, 'destroy'])->name('destroy');
    Route::post('/clear-all', [App\Http\Controllers\OrderController::class, 'clearAll'])->name('clear-all');
    // PERBAIKAN INI:
    Route::post('/clear-all-with-restore', [App\Http\Controllers\OrderController::class, 'clearAllWithRestore'])->name('clear-all-with-restore');
});

// ============= ADMIN PRODUCTS =============
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('dashboard');

// Dashboard & Scheduler routes (Admin only)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/scheduler/status', [App\Http\Controllers\DashboardController::class, 'schedulerStatus'])
        ->name('scheduler.status');
    Route::post('/scheduler/run', [App\Http\Controllers\DashboardController::class, 'runTask'])
        ->name('scheduler.run');
    Route::post('/orders/check-expired', [App\Http\Controllers\DashboardController::class, 'checkExpired'])
        ->name('orders.check-expired');
    Route::post('/queue/restart', [App\Http\Controllers\DashboardController::class, 'restartQueue'])
        ->name('queue.restart');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Invoice routes
Route::middleware('auth')->group(function () {
    Route::get('/invoice/{order}', [InvoiceController::class, 'view'])->name('invoice.view');
    Route::get('/invoice/{order}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('/invoice/{order}/print', [InvoiceController::class, 'print'])->name('invoice.print'); 
    Route::get('/invoice/{order}/thermal', [InvoiceController::class, 'thermal'])->name('invoice.thermal');
});

require __DIR__.'/auth.php';