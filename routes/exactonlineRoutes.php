<?php

use Illuminate\Support\Facades\Route;
use Dashed\DashedCore\Middleware\AdminMiddleware;
use Dashed\DashedEcommerceExactonline\Controllers\ExactonlineController;

Route::middleware(['web', AdminMiddleware::class])->prefix(config('filament.path') . '/exactonline')->group(function () {
    Route::get('/{siteId}/authenticate', [ExactonlineController::class, 'authenticate'])->name('dashed.exactonline.authenticate');
    Route::get('/{siteId}/save-authentication', [ExactonlineController::class, 'saveAuthentication'])->name('dashed.exactonline.save-authentication');
});
