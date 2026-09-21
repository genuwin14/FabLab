<?php

use App\Http\Controllers\Webhooks\UniSmsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Delivery receipts from UniSMS. Public by necessity — the gateway cannot log
// in — and guarded by the shared secret in the 'webhook-secret-key' header.
// Paste the full URL into the dashboard's Webhooks page:
//   https://your-domain.com/api/webhooks/unisms
Route::post('/webhooks/unisms', UniSmsWebhookController::class)->name('webhooks.unisms');
