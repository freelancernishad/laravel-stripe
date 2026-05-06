<?php

namespace FreelancerNishad\Stripe\Http\Controllers;

use Illuminate\Routing\Controller;
use FreelancerNishad\Stripe\Services\StripeService;
use FreelancerNishad\Stripe\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripeService;
    protected $webhookService;

    public function __construct(StripeService $stripeService, StripeWebhookService $webhookService)
    {
        $this->stripeService = $stripeService;
        $this->webhookService = $webhookService;
    }

    public function webhook(Request $request)
    {
        return $this->webhookService->handleWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );
    }

    // Add other methods (checkout, payment intent, etc.)
}
