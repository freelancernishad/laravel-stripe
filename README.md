# Laravel Stripe Payment Gateway

A reusable Laravel package for integrating Stripe Payment Gateway.

## Installation

Install the package via composer:

```bash
composer require freelancernishad/laravel-stripe
```

## Setup

Publish the config and migrations:

```bash
php artisan vendor:publish --tag=stripe-config
php artisan vendor:publish --tag=stripe-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

Add your Stripe credentials to your `.env` file:

```env
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
STRIPE_WEBHOOK_SECRET=your_webhook_secret
```

## Usage

### Creating a Checkout Session

```php
use FreelancerNishad\Stripe\Services\StripeService;

$stripe = app(StripeService::class);

$session = $stripe->createCheckoutSession($user, [
    [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => ['name' => 'T-shirt'],
            'unit_amount' => 2000,
        ],
        'quantity' => 1,
    ]
], url('/success'), url('/cancel'));

return redirect()->to($session->url);
```

## License

The MIT License (MIT).
# laravel-stripe
