<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;
use MasRodjie\LaravelWhatspie\Messaging\Result;

use function Pest\Laravel\mock;

beforeEach(function () {
    Storage::fake('public');

    $this->client = mock(WhatspieClient::class);
    $this->uploader = new FileUploader('public', 'whatspie');

    // Set default config
    config([
        'whatspie.storage.disk' => 'public',
        'whatspie.storage.path' => 'whatspie',
    ]);
});

test('sets receiver number', function () {
    $builder = MessageBuilder::to('6281234567890');

    expect($builder)->toBeInstanceOf(MessageBuilder::class);
});

test('builds text message payload', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'text',
                'text' => 'Hello World',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->text('Hello World')
        ->send();

    expect($result->successful())->toBeTrue();
    expect($result->id())->toBe('msg123');
});

test('builds text message with typing indicator', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'text',
                'text' => 'Hello World',
            ],
            'with_typing' => true,
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->text('Hello World')
        ->withTyping()
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds file message from url', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'file',
                'file' => 'https://example.com/document.pdf',
                'mimetype' => 'application/pdf',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->file('https://example.com/document.pdf', 'application/pdf')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds file message with filename', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'file',
                'file' => 'https://example.com/document.pdf',
                'mimetype' => 'application/pdf',
                'filename' => 'report.pdf',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->file('https://example.com/document.pdf', 'application/pdf')
        ->fileName('report.pdf')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds image message', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'image',
                'image' => 'https://example.com/photo.jpg',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->image('https://example.com/photo.jpg')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds image message with caption', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'image',
                'image' => 'https://example.com/photo.jpg',
                'caption' => 'Check this out!',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->image('https://example.com/photo.jpg')
        ->caption('Check this out!')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds location message', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'location',
                'lat' => -6.2088,
                'long' => 106.8456,
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->location(-6.2088, 106.8456)
        ->send();

    expect($result->successful())->toBeTrue();
});

test('builds location message with name and address', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'location',
                'lat' => -6.2088,
                'long' => 106.8456,
                'name' => 'Monas',
                'address' => 'Jakarta, Indonesia',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->location(-6.2088, 106.8456)
        ->name('Monas')
        ->address('Jakarta, Indonesia')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('uploads local file before sending', function () {
    // Create a temporary file
    $tempFile = tmpfile();
    fwrite($tempFile, 'file content');
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    $capturedPayload = null;

    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', Mockery::on(function ($payload) use (&$capturedPayload) {
            $capturedPayload = $payload;

            return true;
        }))
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->file($tempPath, 'application/pdf')
        ->send();

    expect($result->successful())->toBeTrue();
    expect($capturedPayload['message']['type'])->toBe('file');
    expect($capturedPayload['message']['mimetype'])->toBe('application/pdf');
    expect($capturedPayload['message']['file'])->toContain('/storage/whatspie/');

    fclose($tempFile);
});

test('uploads uploaded file before sending', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $capturedPayload = null;

    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', Mockery::on(function ($payload) use (&$capturedPayload) {
            $capturedPayload = $payload;

            return true;
        }))
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->file($file, 'application/pdf')
        ->send();

    expect($result->successful())->toBeTrue();
    expect($capturedPayload['message']['type'])->toBe('file');
    expect($capturedPayload['message']['mimetype'])->toBe('application/pdf');
    expect($capturedPayload['message']['file'])->toContain('/storage/whatspie/');
});

test('uploads local image before sending', function () {
    $tempFile = tmpfile();
    fwrite($tempFile, 'image content');
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    $capturedPayload = null;

    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', Mockery::on(function ($payload) use (&$capturedPayload) {
            $capturedPayload = $payload;

            return true;
        }))
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client, $this->uploader)
        ->image($tempPath)
        ->send();

    expect($result->successful())->toBeTrue();
    expect($capturedPayload['message']['type'])->toBe('image');
    expect($capturedPayload['message']['image'])->toContain('/storage/whatspie/');

    fclose($tempFile);
});

test('throws exception when sending without calling using', function () {
    MessageBuilder::to('6281234567890')
        ->text('Hello World')
        ->send();
})->throws(InvalidArgumentException::class, 'WhatspieClient is required');

test('throws exception when sending without setting message type', function () {
    MessageBuilder::to('6281234567890')
        ->using($this->client)
        ->send();
})->throws(InvalidArgumentException::class, 'Message type is required');

test('throws exception when uploading file without uploader', function () {
    $tempFile = tmpfile();
    fwrite($tempFile, 'file content');
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    try {
        MessageBuilder::to('6281234567890')
            ->using($this->client)
            ->file($tempPath, 'application/pdf');
    } finally {
        fclose($tempFile);
    }
})->throws(InvalidArgumentException::class, 'FileUploader is required for uploading files');

test('throws exception when uploading image without uploader', function () {
    $tempFile = tmpfile();
    fwrite($tempFile, 'image content');
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    try {
        MessageBuilder::to('6281234567890')
            ->using($this->client)
            ->image($tempPath);
    } finally {
        fclose($tempFile);
    }
})->throws(InvalidArgumentException::class, 'FileUploader is required for uploading images');

test('allows sending text message without uploader', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'text',
                'text' => 'Hello World',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client) // No uploader provided
        ->text('Hello World')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('allows sending file message with public url without uploader', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'file',
                'file' => 'https://example.com/document.pdf',
                'mimetype' => 'application/pdf',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client) // No uploader provided
        ->file('https://example.com/document.pdf', 'application/pdf')
        ->send();

    expect($result->successful())->toBeTrue();
});

test('allows sending image message with public url without uploader', function () {
    $this->client
        ->shouldReceive('send')
        ->once()
        ->with('6281234567890', [
            'message' => [
                'type' => 'image',
                'image' => 'https://example.com/photo.jpg',
            ],
        ])
        ->andReturn(new Result(200, ['data' => ['id' => 'msg123']]));

    $result = MessageBuilder::to('6281234567890')
        ->using($this->client) // No uploader provided
        ->image('https://example.com/photo.jpg')
        ->send();

    expect($result->successful())->toBeTrue();
});
