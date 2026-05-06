<?php

namespace FreelancerNishad\Stripe;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use FreelancerNishad\Stripe\Events\StripePaymentEvent;
use FreelancerNishad\Stripe\Listeners\CheckStripePaymentStatus;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/stripe.php', 'stripe');

        $this->app->singleton(\FreelancerNishad\Stripe\Services\StripeService::class, function ($app) {
            return new \FreelancerNishad\Stripe\Services\StripeService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/stripe.php' => config_path('stripe.php'),
            ], 'stripe-config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_stripe_logs_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_stripe_logs_table.php'),
            ], 'stripe-migrations');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/stripe.php');

        Event::listen(
            StripePaymentEvent::class,
            [CheckStripePaymentStatus::class, 'handle']
        );
    }
}
