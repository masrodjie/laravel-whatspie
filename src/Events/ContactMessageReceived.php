<?php

namespace MasRodjie\LaravelWhatspie\Events;

class ContactMessageReceived extends WebhookReceived
{
    public function contacts(): array
    {
        return $this->payload()['message']['contacts'] ?? [];
    }
}
