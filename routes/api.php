<?php

use App\Http\Controllers\Dashboard\DpPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('v2/webhooks/xendit', [DpPaymentController::class, 'webhook'])
    ->name('webhooks.xendit');
