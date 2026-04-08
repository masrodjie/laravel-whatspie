<?php

use Illuminate\Support\Facades\Http;
use MasRodjie\LaravelWhatspie\Http\WhatspieClient;

beforeEach(function () {
    $this->client = new WhatspieClient('test_token', '6281234567890');
});

test('sends text message successfully', function () {
    Http::fake([
        'api.whatspie.com/*' => Http::response([
            'code' => 200,
            'message' => 'Success',
            'data' => ['id' => 'msg_123', 'status' => 'pending'],
        ], 200),
    ]);

    $result = $this->client->send('6289876543210', [
        'type' => 'chat',
        'params' => ['text' => 'Hello World'],
    ]);

    expect($result->successful())->toBeTrue();
    expect($result->id())->toBe('msg_123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.whatspie.com/messages' &&
            $request->header('Authorization')[0] === 'Bearer test_token' &&
            $request['device'] === '6281234567890' &&
            $request['receiver'] === '6289876543210';
    });
});

test('returns failed result on api error', function () {
    Http::fake([
        'api.whatspie.com/*' => Http::response([
            'code' => 400,
            'message' => 'Invalid request',
            'error' => 'Invalid phone number',
        ], 400),
    ]);

    $result = $this->client->send('invalid', [
        'type' => 'chat',
        'params' => ['text' => 'Hello'],
    ]);

    expect($result->failed())->toBeTrue();
    expect($result->error())->toBe('Invalid phone number');
    expect($result->errorCode())->toBe(400);
});

test('sends file message with url', function () {
    Http::fake([
        'api.whatspie.com/*' => Http::response([
            'code' => 200,
            'message' => 'Success',
            'data' => ['id' => 'msg_file_123', 'status' => 'pending'],
        ], 200),
    ]);

    $result = $this->client->send('6289876543210', [
        'type' => 'file',
        'params' => [
            'document' => ['url' => 'https://example.com/doc.pdf'],
            'fileName' => 'document.pdf',
            'mimetype' => 'application/pdf',
        ],
    ]);

    expect($result->successful())->toBeTrue();
    expect($result->id())->toBe('msg_file_123');
});

test('sends message with typing indicator', function () {
    Http::fake([
        'api.whatspie.com/*' => Http::response([
            'code' => 200,
            'message' => 'Success',
            'data' => ['id' => 'msg_456', 'status' => 'pending'],
        ], 200),
    ]);

    $result = $this->client->send('6289876543210', [
        'type' => 'chat',
        'params' => ['text' => 'Hello'],
        'simulate_typing' => 1,
    ]);

    expect($result->successful())->toBeTrue();

    Http::assertSent(function ($request) {
        return $request['simulate_typing'] === 1;
    });
});

test('uses custom base url when provided', function () {
    $customClient = new WhatspieClient('test_token', '6281234567890', 'https://custom.api.example.com');

    Http::fake([
        'custom.api.example.com/*' => Http::response([
            'code' => 200,
            'message' => 'Success',
            'data' => ['id' => 'msg_custom', 'status' => 'pending'],
        ], 200),
    ]);

    $result = $customClient->send('6289876543210', [
        'type' => 'chat',
        'params' => ['text' => 'Hello'],
    ]);

    expect($result->successful())->toBeTrue();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://custom.api.example.com/');
    });
});
