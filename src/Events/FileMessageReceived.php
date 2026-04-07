<?php

namespace MasRodjie\LaravelWhatspie\Events;

class FileMessageReceived extends WebhookReceived
{
    public function fileUrl(): ?string
    {
        return $this->payload()['message']['url'] ?? null;
    }

    public function fileName(): ?string
    {
        return $this->payload()['message']['filename'] ?? null;
    }
}
