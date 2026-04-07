<?php

namespace MasRodjie\LaravelWhatspie\Events;

class AudioMessageReceived extends WebhookReceived
{
    public function fileUrl(): ?string
    {
        return $this->payload()['message']['url'] ?? null;
    }

    public function duration(): ?int
    {
        return $this->payload()['message']['duration'] ?? null;
    }
}
