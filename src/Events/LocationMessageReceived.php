<?php

namespace MasRodjie\LaravelWhatspie\Events;

class LocationMessageReceived extends WebhookReceived
{
    public function latitude(): ?float
    {
        return $this->payload()['message']['latitude'] ?? null;
    }

    public function longitude(): ?float
    {
        return $this->payload()['message']['longitude'] ?? null;
    }

    public function name(): ?string
    {
        return $this->payload()['message']['name'] ?? null;
    }

    public function address(): ?string
    {
        return $this->payload()['message']['address'] ?? null;
    }
}
