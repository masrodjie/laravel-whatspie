<?php

namespace MasRodjie\LaravelWhatspie\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;

class WebhookController
{
    public function __invoke(array $payload): Response
    {
        // Dispatch the generic WebhookReceived event with raw payload
        Event::dispatch(new WebhookReceived($payload));

        // Determine message type and dispatch specific event
        $messageType = $payload['message']['type'] ?? null;

        $eventClass = match ($messageType) {
            'image' => ImageMessageReceived::class,
            'audio' => AudioMessageReceived::class,
            'file' => FileMessageReceived::class,
            'contact' => ContactMessageReceived::class,
            'location' => LocationMessageReceived::class,
            default => TextMessageReceived::class,
        };

        Event::dispatch(new $eventClass($payload));

        return response('OK', 200);
    }
}
