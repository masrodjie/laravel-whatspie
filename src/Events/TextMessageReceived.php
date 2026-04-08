<?php

namespace MasRodjie\LaravelWhatspie\Events;

class TextMessageReceived extends WebhookReceived
{
    public function message(): ?string
    {
        return $this->payload()['message']['body'] ?? null;
    }

    public function fromUser(): ?string
    {
        return $this->from();
    }

    public function timestamp(): ?int
    {
        return $this->payload()['timestamp'] ?? null;
    }

    public function messageId(): ?string
    {
        return $this->payload()['id'] ?? null;
    }

    public function isForwarded(): bool
    {
        return (bool) ($this->payload()['forwarded'] ?? false);
    }

    public function isBroadcast(): bool
    {
        return (bool) ($this->payload()['broadcast'] ?? false);
    }
}
