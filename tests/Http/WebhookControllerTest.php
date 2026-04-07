<?php

use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController;

beforeEach(function () {
    Event::fake();
});

test('dispatches WebhookReceived event', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $controller = new WebhookController;
    $response = $controller->__invoke($payload);

    Event::assertDispatched(WebhookReceived::class, function ($event) use ($payload) {
        return $event->payload() === $payload;
    });
});

test('dispatches TextMessageReceived for text messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello World'],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(TextMessageReceived::class);
});

test('dispatches ImageMessageReceived for image messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => [
            'type' => 'image',
            'url' => 'https://example.com/image.jpg',
            'caption' => 'Test image',
        ],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(ImageMessageReceived::class);
});

test('dispatches AudioMessageReceived for audio messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => [
            'type' => 'audio',
            'url' => 'https://example.com/audio.mp3',
            'duration' => 30,
        ],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(AudioMessageReceived::class);
});

test('dispatches FileMessageReceived for file messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => [
            'type' => 'file',
            'url' => 'https://example.com/document.pdf',
            'filename' => 'document.pdf',
        ],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(FileMessageReceived::class);
});

test('dispatches ContactMessageReceived for contact messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => [
            'type' => 'contact',
            'contacts' => [
                ['name' => 'John Doe', 'phone' => '6289876543210'],
            ],
        ],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(ContactMessageReceived::class);
});

test('dispatches LocationMessageReceived for location messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => [
            'type' => 'location',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ],
    ];

    $controller = new WebhookController;
    $controller->__invoke($payload);

    Event::assertDispatched(LocationMessageReceived::class);
});

test('returns 200 response', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $controller = new WebhookController;
    $response = $controller->__invoke($payload);

    expect($response->status())->toBe(200);
    expect($response->getContent())->toBe('OK');
});
