<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Loaded without the web middleware group: no session, no cookies, no CSRF token.
// The package's own verify() is the authentication.
Route::post('/webhooks/{package}/{key}', WebhookController::class)
    ->where('package', '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/[a-z0-9](?:[a-z0-9-]*[a-z0-9])?')
    ->where('key', '[a-z][a-z0-9_]*')
    ->middleware('throttle:webhooks')
    ->name('webhooks');
