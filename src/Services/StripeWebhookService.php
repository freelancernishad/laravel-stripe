<?php

namespace FreelancerNishad\Stripe\Services;

use FreelancerNishad\Stripe\Events\StripePaymentEvent;
use Stripe\Webhook;
use Stripe\Stripe;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;

class StripeWebhookService
{
    public function handleWebhook($payload, $sigHeader)
    {
        Stripe::setApiKey(config('stripe.secret'));
        $endpointSecret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch(\UnexpectedValueException $e) {
            Log::error('Stripe Webhook Error: Invalid payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(SignatureVerificationException $e) {
            Log::error('Stripe Webhook Error: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object, $event->type);
                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object, $event->type);
                break;
            default:
                StripePaymentEvent::dispatch($event->type, $event->data->object->toArray(), 'received');
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutSessionCompleted($session, $eventType)
    {
        $logModel = config('stripe.models.log');
        $log = $logModel::where('session_id', $session->id)->first();

        if ($log) {
            $log->update([
                'status' => $session->payment_status,
                'payment_intent_id' => $session->payment_intent,
                'payload' => array_merge($log->payload ?? [], ['webhook_event' => $eventType, 'session_details' => $session->toArray()]),
            ]);
            
            StripePaymentEvent::dispatch($eventType, $session->toArray(), 'success', $log->user_id);
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent, $eventType)
    {
        $logModel = config('stripe.models.log');
        $log = $logModel::where('payment_intent_id', $paymentIntent->id)->first();

        if ($log) {
            $log->update([
                'status' => 'paid',
                'payload' => array_merge($log->payload ?? [], ['webhook_event' => $eventType, 'intent_details' => $paymentIntent->toArray()]),
            ]);
            StripePaymentEvent::dispatch($eventType, $paymentIntent->toArray(), 'success', $log->user_id);
        }
    }
}
