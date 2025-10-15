<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| These routes are loaded without any middleware, including CSRF protection.
| They are specifically for handling external webhook calls.
|
*/

Route::post('/webhook/razorpay', [WebhookController::class, 'razorpayWebhook'])->name('webhook.razorpay');
Route::any('/webhook/test', [WebhookController::class, 'testWebhook'])->name('webhook.test');