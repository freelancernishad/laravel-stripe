<?php

namespace FreelancerNishad\Stripe\Listeners;

use FreelancerNishad\Stripe\Events\StripePaymentEvent;
use Illuminate\Support\Facades\Log;

class CheckStripePaymentStatus
{
    public function handle(StripePaymentEvent $event)
    {
        Log::info("CheckStripePaymentStatus Listener Triggered for Type: {$event->type}");

        // Project specific logic can be added here or via another listener in the project
    }
}
