<?php

namespace FreelancerNishad\Stripe\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StripePaymentEvent
{
    use Dispatchable, SerializesModels;

    public $type;
    public $payload;
    public $status;
    public $userId;

    public function __construct(string $type, array $payload, string $status, ?int $userId = null)
    {
        $this->type = $type;
        $this->payload = $payload;
        $this->status = $status;
        $this->userId = $userId;
    }
}
