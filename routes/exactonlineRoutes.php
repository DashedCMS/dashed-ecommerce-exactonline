<?php

use Illuminate\Support\Facades\Route;
use Qubiqx\QcommerceCore\Middleware\AdminMiddleware;
use Qubiqx\QcommerceEcommerceCore\Controllers\Api\Checkout\CheckoutApiController;
use Qubiqx\QcommerceEcommerceExactonline\Controllers\ExactonlineController;

Route::middleware(['web', AdminMiddleware::class])->prefix(config('filament.path') . '/exactonline')->group(function () {
    Route::get('/{siteId}/authenticate', [ExactonlineController::class, 'authenticate'])->name('qcommerce.exactonline.authenticate');
    Route::get('/{siteId}/save-authentication', [CheckoutApiController::class, 'saveAuthentication'])->name('qcommerce.exactonline.save-authentication');
});
