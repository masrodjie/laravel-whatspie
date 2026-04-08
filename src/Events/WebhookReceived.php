<?php

namespace MasRodjie\LaravelWhatspie\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WebhookReceived
{
    use Dispatchable;

    public function __construct(
        protected array $payload
    ) {}

    public function from(): ?string
    {
        return $this->payload['from'] ?? null;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
