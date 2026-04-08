<?php

namespace MasRodjie\LaravelWhatspie\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;

class WebhookController
{
    public function __invoke(Request $request): Response
    {
        // Verify webhook secret if configured
        if ($secret = config('whatspie.webhook.secret')) {
            $signature = $request->header('X-Webhook-Secret');
            if ($signature === null || ! hash_equals($secret, $signature)) {
                abort(401, 'Invalid webhook signature');
            }
        }

        $payload = $request->all();

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
