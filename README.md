# Laravel Stripe Payment Gateway

A reusable and extensible Laravel package for integrating Stripe Payment Gateway. Supports Checkout Sessions, Payment Intents, and Subscriptions.

## Features
- **Flexible Models**: Link payments to any project entity using polymorphic relations.
- **Configurable**: Easily swap log models and table names.
- **Robust Webhooks**: Pre-configured webhook handlers for common Stripe events.
- **Metadata Support**: Store arbitrary JSON data with every transaction.

## Installation

1. Install the package via composer:
```bash
composer require freelancernishad/laravel-stripe
```

2. Publish the config and migrations:
```bash
php artisan vendor:publish --tag=stripe-config
php artisan vendor:publish --tag=stripe-migrations
```

3. Run the migrations:
```bash
php artisan migrate
```

## Configuration

Add your Stripe credentials to your `.env` file:
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Basic Usage

### Checkout Session Example
```php
use FreelancerNishad\Stripe\Services\StripeService;

public function checkout(StripeService $stripe) {
    $session = $stripe->createCheckoutSession(auth()->user(), [
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
}
```

## Customization

### Adding Extra Fields
Use the `meta_data` parameter to store additional info:
```php
$stripe->createCheckoutSession($user, $items, $successUrl, $cancelUrl, false, [
    'order_id' => 123,
    'referral' => 'friend'
]);
```

### Overriding the Log Model
1. Extend `FreelancerNishad\Stripe\Models\StripeLog`.
2. Map your model in `config/stripe.php`:
```php
'models' => [
    'log' => \App\Models\MyStripeLog::class,
],
```

## Webhooks
The package includes a default webhook handler. Point your Stripe Webhook URL to:
`https://your-domain.com/v1/payments/stripe/webhook`

## License
The MIT License (MIT).
