<?php

namespace FreelancerNishad\Stripe\Models;

use Illuminate\Database\Eloquent\Model;

class StripeLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'stripe_customer_id',
        'session_id',
        'subscription_id',
        'payment_intent_id',
        'amount',
        'currency',
        'status',
        'payload',
        'meta_data',
    ];

    protected $casts = [
        'payload' => 'array',
        'meta_data' => 'array',
    ];

    public function user()
    {
        $userModel = config('auth.providers.users.model');
        return $this->belongsTo($userModel);
    }
}
