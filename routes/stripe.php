<?php

use FreelancerNishad\Stripe\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

$prefix = config('stripe.route_prefix', 'v1/payments/stripe');
$middleware = config('stripe.route_middleware', ['api']);

Route::prefix($prefix)->middleware($middleware)->group(function () {
    Route::post('/webhook', [StripeController::class, 'webhook']);
    // Add other routes as needed
});
