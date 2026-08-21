<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/category/{category:slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [App\Http\Controllers\CartController::class, 'add'])->name('add');
    Route::put('/update/{id}', [App\Http\Controllers\CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [App\Http\Controllers\CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('clear');
    Route::get('/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [App\Http\Controllers\CartController::class, 'process'])->name('checkout.process');
});

Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::get('/provinces', [App\Http\Controllers\ShippingController::class, 'provinces'])->name('provinces');
    Route::get('/cities/{provinceId}', [App\Http\Controllers\ShippingController::class, 'cities'])->name('cities');
    Route::get('/districts/{cityId}', [App\Http\Controllers\ShippingController::class, 'districts'])->name('districts');
    Route::post('/costs', [App\Http\Controllers\ShippingController::class, 'costs'])->name('costs');
});

Route::get('/payment/midtrans/{order}', [App\Http\Controllers\PaymentController::class, 'midtrans'])->name('payment.midtrans');
Route::get('/payment/midtrans/finish/{order}', [App\Http\Controllers\MidtransController::class, 'finish'])->name('payment.midtrans.finish');
Route::post('/payment/midtrans/token/{order}', [App\Http\Controllers\MidtransController::class, 'token'])->name('midtrans.token');
Route::post('/payment/midtrans/notification', [App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification');

Route::get('/order/success/{order}', [App\Http\Controllers\OrderController::class, 'checkoutSuccess'])->name('order.success');
Route::get('/order/upload/{order}', [App\Http\Controllers\OrderController::class, 'uploadPage'])->name('order.upload');
Route::get('/o/{order}', [App\Http\Controllers\OrderController::class, 'uploadPage'])->name('order.short');
Route::post('/order/upload-proof/{order}', [App\Http\Controllers\OrderController::class, 'uploadProof'])->name('order.upload-proof');
Route::get('/track', [App\Http\Controllers\OrderController::class, 'trackForm'])->name('order.track.form');
Route::post('/track', [App\Http\Controllers\OrderController::class, 'track'])->name('order.track');

Route::prefix('admin/orders')->name('admin.orders.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('show');
    Route::post('/{order}/confirm-payment', [App\Http\Controllers\OrderController::class, 'confirmPayment'])->name('confirm-payment');
    Route::post('/{order}/process', [App\Http\Controllers\OrderController::class, 'processOrder'])->name('process');
    Route::post('/{order}/complete', [App\Http\Controllers\OrderController::class, 'complete'])->name('complete');
    Route::post('/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('cancel');
    Route::delete('/{order}', [App\Http\Controllers\OrderController::class, 'destroy'])->name('destroy');
    Route::post('/clear-all', [App\Http\Controllers\OrderController::class, 'clearAll'])->name('clear-all');
    Route::post('/clear-all-with-restore', [App\Http\Controllers\OrderController::class, 'clearAllWithRestore'])->name('clear-all-with-restore');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'admin'])->name('dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/scheduler/status', [App\Http\Controllers\DashboardController::class, 'schedulerStatus'])->name('scheduler.status');
    Route::post('/scheduler/run', [App\Http\Controllers\DashboardController::class, 'runTask'])->name('scheduler.run');
    Route::post('/orders/check-expired', [App\Http\Controllers\DashboardController::class, 'checkExpired'])->name('orders.check-expired');
    Route::post('/queue/restart', [App\Http\Controllers\DashboardController::class, 'restartQueue'])->name('queue.restart');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/invoice/{order}', [InvoiceController::class, 'view'])->name('invoice.view');
    Route::get('/invoice/{order}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('/invoice/{order}/print', [InvoiceController::class, 'print'])->name('invoice.print');
    Route::get('/invoice/{order}/thermal', [InvoiceController::class, 'thermal'])->name('invoice.thermal');
});

require __DIR__.'/auth.php';
