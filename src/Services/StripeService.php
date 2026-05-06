<?php

namespace FreelancerNishad\Stripe\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\Checkout\Session as CheckoutSession;
use Exception;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    public function createOrGetCustomer($user)
    {
        if (isset($user->stripe_id) && $user->stripe_id) {
            try {
                return Customer::retrieve($user->stripe_id);
            } catch (Exception $e) {
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        if (method_exists($user, 'update')) {
            $user->update(['stripe_id' => $customer->id]);
        }

        return $customer;
    }

    public function createCheckoutSession($user, array $items, string $successUrl, string $cancelUrl, bool $saveCard = false, array $metadata = [], array $extra_params = [])
    {
        $customer = $this->createOrGetCustomer($user);

        $params = [
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => $items,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'allow_promotion_codes' => true,
        ];

        $params = array_merge($params, $extra_params);

        if (isset($params['discounts']) || isset($params['coupon'])) {
            unset($params['allow_promotion_codes']);
        }

        if ($saveCard) {
            $params['payment_intent_data'] = [
                'setup_future_usage' => 'off_session', 
            ];
        }

        $session = CheckoutSession::create($params);

        $logModel = config('stripe.models.log');
        $logModel::create([
            'user_id' => $user->id,
            'type' => 'checkout',
            'stripe_customer_id' => $customer->id,
            'session_id' => $session->id,
            'payment_intent_id' => $session->payment_intent,
            'amount' => $session->amount_total ? $session->amount_total / 100 : 0,
            'currency' => $session->currency,
            'status' => $session->payment_status,
            'payload' => $session->toArray(),
            'meta_data' => $metadata,
        ]);

        return $session;
    }

    public function createSubscriptionSession($user, string $priceId, string $successUrl, string $cancelUrl, array $metadata = [])
    {
        $customer = $this->createOrGetCustomer($user);

        $session = CheckoutSession::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'allow_promotion_codes' => true,
        ]);

        $logModel = config('stripe.models.log');
        $logModel::create([
            'user_id' => $user->id,
            'type' => 'subscription',
            'stripe_customer_id' => $customer->id,
            'session_id' => $session->id,
            'subscription_id' => $session->subscription,
            'amount' => $session->amount_total ? $session->amount_total / 100 : 0,
            'currency' => $session->currency,
            'status' => $session->status,
            'payload' => $session->toArray(),
            'meta_data' => $metadata,
        ]);

        return $session;
    }

    public function cancelSubscription(string $subscriptionId)
    {
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        return $subscription->cancel();
    }

    public function createPaymentIntent($user, int $amount, string $currency = 'usd', array $metadata = [], bool $setupFutureUsage = false)
    {
        $customer = $this->createOrGetCustomer($user);

        $params = [
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customer->id,
            'metadata' => $metadata,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];

        if ($setupFutureUsage) {
            $params['setup_future_usage'] = 'off_session';
        }

        $intent = PaymentIntent::create($params);

        $logModel = config('stripe.models.log');
        $logModel::create([
            'user_id' => $user->id,
            'type' => 'payment_intent',
            'stripe_customer_id' => $customer->id,
            'payment_intent_id' => $intent->id,
            'amount' => $amount / 100,
            'currency' => $currency,
            'status' => $intent->status,
            'payload' => $intent->toArray(),
        ]);

        return $intent;
    }

    public function createSetupIntent($user)
    {
        $customer = $this->createOrGetCustomer($user);

        $intent = SetupIntent::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
        ]);

        $logModel = config('stripe.models.log');
        $logModel::create([
            'user_id' => $user->id,
            'type' => 'setup_intent',
            'payment_intent_id' => $intent->id,
            'status' => $intent->status,
            'payload' => $intent->toArray(),
        ]);

        return $intent;
    }

    public function listCards($user)
    {
        if (!isset($user->stripe_id) || !$user->stripe_id) {
            return [];
        }

        return PaymentMethod::all([
            'customer' => $user->stripe_id,
            'type' => 'card',
        ]);
    }

    public function deleteCard(string $paymentMethodId)
    {
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        return $paymentMethod->detach();
    }
}
