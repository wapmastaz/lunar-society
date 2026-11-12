<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PayPalWebhookController;
use Illuminate\Support\Facades\Route;

// Public site
Route::livewire('/', 'pages::home')->name('home');
Route::livewire('claim/{tier?}', 'pages::claim')->name('claim');
Route::livewire('founders', 'pages::founders')->name('founders');
Route::livewire('souvenirs', 'pages::souvenirs')->name('souvenirs');
Route::livewire('souvenirs/{product}', 'pages::souvenir-checkout')->name('souvenirs.show');
Route::livewire('orders/{order}', 'pages::order-status')->name('orders.show');

Route::view('charter', 'site.charter')->name('charter');
Route::view('faq', 'site.faq')->name('faq');
Route::view('about', 'site.about')->name('about');
Route::view('terms', 'site.terms')->name('terms');
Route::view('privacy', 'site.privacy')->name('privacy');
Route::view('disclaimer', 'site.disclaimer')->name('disclaimer');

// PayPal
Route::middleware('throttle:30,1')->controller(CheckoutController::class)->group(function () {
    Route::post('checkout/{order}/paypal', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('checkout/{order}/capture', [CheckoutController::class, 'capture'])->name('checkout.capture');
});
Route::post('paypal/webhook', PayPalWebhookController::class)->name('paypal.webhook');

// Admin
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin')->name('dashboard');
    Route::livewire('admin', 'pages::admin.orders')->name('admin.orders');
    Route::get('admin/exports/engraving.csv', [ExportController::class, 'engraving'])->name('admin.exports.engraving');
    Route::get('admin/exports/shipping.csv', [ExportController::class, 'shipping'])->name('admin.exports.shipping');
});

require __DIR__.'/settings.php';
