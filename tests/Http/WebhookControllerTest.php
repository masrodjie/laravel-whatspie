<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Event::fake();
});

test('dispatches WebhookReceived event', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $response = $controller->__invoke($request);

    Event::assertDispatched(WebhookReceived::class, function ($event) use ($payload) {
        return $event->payload() === $payload;
    });
});

test('dispatches TextMessageReceived for text messages', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello World'],
    ];

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

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

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

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

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

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

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

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

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

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

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $controller->__invoke($request);

    Event::assertDispatched(LocationMessageReceived::class);
});

test('returns 200 response', function () {
    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $response = $controller->__invoke($request);

    expect($response->status())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

test('allows requests without secret when not configured', function () {
    config(['whatspie.webhook.secret' => null]);

    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;
    $response = $controller->__invoke($request);

    expect($response->status())->toBe(200);
});

test('allows requests with valid signature', function () {
    $secret = 'test-webhook-secret';
    config(['whatspie.webhook.secret' => $secret]);

    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);
    $request->headers->set('X-Webhook-Secret', $secret);

    $controller = new WebhookController;
    $response = $controller->__invoke($request);

    expect($response->status())->toBe(200);
});

test('rejects requests with invalid signature', function () {
    config(['whatspie.webhook.secret' => 'correct-secret']);

    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);
    $request->headers->set('X-Webhook-Secret', 'wrong-secret');

    $controller = new WebhookController;

    try {
        $controller->__invoke($request);
        expect(false)->toBeTrue('Should have thrown an exception');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(401);
        expect($e->getMessage())->toBe('Invalid webhook signature');
    }
});

test('rejects requests without signature when secret is configured', function () {
    config(['whatspie.webhook.secret' => 'configured-secret']);

    $payload = [
        'from' => '6281234567890',
        'message' => ['type' => 'chat', 'body' => 'Hello'],
    ];

    $request = Request::create('/', 'POST', $payload);

    $controller = new WebhookController;

    try {
        $controller->__invoke($request);
        expect(false)->toBeTrue('Should have thrown an exception');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(401);
        expect($e->getMessage())->toBe('Invalid webhook signature');
    }
});
