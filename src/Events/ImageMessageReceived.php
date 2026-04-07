<?php

namespace MasRodjie\LaravelWhatspie\Events;

class ImageMessageReceived extends WebhookReceived
{
    public function fileUrl(): ?string
    {
        return $this->payload()['message']['url'] ?? null;
    }

    public function caption(): ?string
    {
        return $this->payload()['message']['caption'] ?? null;
    }
}
