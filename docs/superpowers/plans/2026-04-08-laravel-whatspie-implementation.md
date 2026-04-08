# Laravel Whatspie Package v1.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Laravel package that provides a fluent API for sending WhatsApp messages via Whatspie API and a complete webhook system for receiving messages with event dispatching.

**Architecture:** Facade-based fluent API with MessageBuilder pattern, HTTP client for API communication, event-driven webhook handling, and Result objects for predictable error handling.

**Tech Stack:** PHP 8.2+, Laravel 11+, Laravel HTTP facade, Laravel Events, Laravel Storage, Pest for testing

---

## File Structure Overview

```
src/
├── LaravelWhatspieServiceProvider.php      # Package service provider
├── Facades/
│   └── Whatspie.php                        # Facade for fluent API
├── Contracts/
│   └── WhatspieClient.php                  # Client interface
├── Http/
│   ├── Controllers/
│   │   └── WebhookController.php           # Webhook endpoint
│   └── WhatspieClient.php                  # HTTP client implementation
├── Messaging/
│   ├── MessageBuilder.php                  # Fluent message builder
│   ├── FileUploader.php                    # File upload handler
│   └── Result.php                          # API result wrapper
├── Events/
│   ├── WebhookReceived.php                 # Base webhook event
│   ├── TextMessageReceived.php
│   ├── ImageMessageReceived.php
│   ├── AudioMessageReceived.php
│   ├── FileMessageReceived.php
│   ├── ContactMessageReceived.php
│   └── LocationMessageReceived.php
config/
└── whatspie.php                            # Package config
tests/
├── Messaging/
│   ├── MessageBuilderTest.php
│   ├── FileUploaderTest.php
│   └── ResultTest.php
├── Http/
│   ├── WhatspieClientTest.php
│   └── WebhookControllerTest.php
└── Events/
    └── WebhookEventTest.php
```

---

### Task 1: Update Config File

**Files:**
- Modify: `config/whatspie.php`

- [ ] **Step 1: Replace config file contents**

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Whatspie API Token
    |--------------------------------------------------------------------------
    |
    | Your Whatspie API token for authentication.
    | Get it from https://whatspie.com/dashboard
    |
    */

    'api_token' => env('WHATSPIE_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Device Number
    |--------------------------------------------------------------------------
    |
    | Your registered WhatsApp device number in international format
    | without the + symbol (e.g., 6281234567890).
    |
    */

    'device' => env('WHATSPIE_DEVICE'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the webhook endpoint for receiving incoming messages.
    |
    */

    'webhook' => [
        'enabled' => env('WHATSPIE_WEBHOOK_ENABLED', true),
        'path' => env('WHATSPIE_WEBHOOK_PATH', '/whatspie/webhook'),
        'secret' => env('WHATSPIE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where local files are uploaded before sending to Whatspie.
    | The disk must be publicly accessible.
    |
    */

    'storage' => [
        'disk' => env('WHATSPIE_STORAGE_DISK', 'public'),
        'path' => env('WHATSPIE_STORAGE_PATH', 'whatspie'),
    ],
];
```

- [ ] **Step 2: Commit**

```bash
git add config/whatspie.php
git commit -m "feat: add complete whatspie configuration"
```

---

### Task 2: Create Result Class

**Files:**
- Create: `src/Messaging/Result.php`
- Create: `tests/Messaging/ResultTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use MasRodjie\LaravelWhatspie\Messaging\Result;

beforeEach(function () {
    $this->successData = [
        'code' => 200,
        'message' => 'Success',
        'data' => [
            'id' => 'msg_123',
            'status' => 'pending',
        ],
    ];
});

test('successful result returns true', function () {
    $result = new Result(200, $this->successData);

    expect($result->successful())->toBeTrue();
    expect($result->failed())->toBeFalse();
});

test('failed result returns false', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
        'error' => 'Phone number format invalid',
    ]);

    expect($result->successful())->toBeFalse();
    expect($result->failed())->toBeTrue();
});

test('result returns message id when successful', function () {
    $result = new Result(200, $this->successData);

    expect($result->id())->toBe('msg_123');
});

test('result returns null id when failed', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
    ]);

    expect($result->id())->toBeNull();
});

test('result returns error message when failed', function () {
    $result = new Result(400, [
        'code' => 400,
        'message' => 'Invalid request',
        'error' => 'Phone number format invalid',
    ]);

    expect($result->error())->toBe('Phone number format invalid');
    expect($result->errorCode())->toBe(400);
});

test('result returns full data array', function () {
    $result = new Result(200, $this->successData);

    expect($result->data())->toBe($this->successData);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Messaging/ResultTest.php`

Expected: FAIL with "Class MasRodjie\LaravelWhatspie\Messaging\Result not found"

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Messaging;

class Result
{
    protected int $statusCode;
    protected array $data;

    public function __construct(int $statusCode, array $data)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function failed(): bool
    {
        return !$this->successful();
    }

    public function id(): ?string
    {
        return $this->data['data']['id'] ?? null;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function error(): ?string
    {
        return $this->data['error'] ?? $this->data['message'] ?? null;
    }

    public function errorCode(): ?int
    {
        return $this->failed() ? $this->statusCode : null;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/pest tests/Messaging/ResultTest.php`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Messaging/Result.php tests/Messaging/ResultTest.php
git commit -m "feat: add Result class for API responses"
```

---

### Task 3: Create WhatspieClient Interface and Implementation

**Files:**
- Create: `src/Contracts/WhatspieClient.php`
- Create: `src/Http/WhatspieClient.php`
- Create: `tests/Http/WhatspieClientTest.php`

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Http/WhatspieClientTest.php`

Expected: FAIL with "Class MasRodjie\LaravelWhatspie\Http\WhatspieClient not found"

- [ ] **Step 3: Create interface**

```php
<?php

namespace MasRodjie\Contracts;

use MasRodjie\LaravelWhatspie\Messaging\Result;

interface WhatspieClient
{
    public function send(string $receiver, array $payload): Result;
}
```

- [ ] **Step 4: Write implementation**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Http;

use Illuminate\Support\Facades\Http;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient as WhatspieClientContract;
use MasRodjie\LaravelWhatspie\Messaging\Result;

class WhatspieClient implements WhatspieClientContract
{
    protected string $apiToken;
    protected string $device;
    protected string $baseUrl = 'https://api.whatspie.com';

    public function __construct(string $apiToken, string $device)
    {
        $this->apiToken = $apiToken;
        $this->device = $device;
    }

    public function send(string $receiver, array $payload): Result
    {
        $payload = array_merge([
            'device' => $this->device,
            'receiver' => $receiver,
        ], $payload);

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/messages", $payload);

        return new Result(
            $response->status(),
            $response->json() ?? []
        );
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `vendor/bin/pest tests/Http/WhatspieClientTest.php`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/Contracts/WhatspieClient.php src/Http/WhatspieClient.php tests/Http/WhatspieClientTest.php
git commit -m "feat: add WhatspieClient for API communication"
```

---

### Task 4: Create FileUploader

**Files:**
- Create: `src/Messaging/FileUploader.php`
- Create: `tests/Messaging/FileUploaderTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;

beforeEach(function () {
    Storage::fake('public');
    $this->uploader = new FileUploader('public', 'whatspie');
});

test('returns url directly if already public url', function () {
    $url = 'https://example.com/image.jpg';

    $result = $this->uploader->upload($url);

    expect($result)->toBe($url);
});

test('uploads local file and returns url', function () {
    Storage::disk('public')->put('local/test.jpg', 'content');

    $result = $this->uploader->upload(storage_path('app/public/local/test.jpg'));

    expect($result)->toBeString();
    expect($result)->toContain('/storage/whatspie/');
    Storage::disk('public')->assertExists('whatspie/');
});

test('uploads UploadedFile and returns url', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $result = $this->uploader->upload($file);

    expect($result)->toBeString();
    Storage::disk('public')->assertExists('whatspie/');
});

test('generates unique filename for uploads', function () {
    $file = UploadedFile::fake()->create('photo.jpg', 500);

    $result1 = $this->uploader->upload($file);
    $result2 = $this->uploader->upload($file);

    expect($result1)->not->toBe($result2);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Messaging/FileUploaderTest.php`

Expected: FAIL with "Class MasRodjie\LaravelWhatspie\Messaging\FileUploader not found"

- [ ] **Step 3: Write implementation**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Messaging;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploader
{
    protected string $disk;
    protected string $path;

    public function __construct(string $disk, string $path)
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    public function upload(string|UploadedFile $file): string
    {
        // If it's already a public URL, return as-is
        if (is_string($file) && $this->isPublicUrl($file)) {
            return $file;
        }

        // Upload UploadedFile
        if ($file instanceof UploadedFile) {
            return $this->uploadUploadedFile($file);
        }

        // Upload local file
        return $this->uploadLocalFile($file);
    }

    protected function isPublicUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    protected function uploadUploadedFile(UploadedFile $file): string
    {
        $fileName = $this->generateUniqueFileName($file->getClientOriginalExtension());
        $path = $file->storeAs($this->path, $fileName, ['disk' => $this->disk]);

        return Storage::disk($this->disk)->url($path);
    }

    protected function uploadLocalFile(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $fileName = $this->generateUniqueFileName($extension);

        $contents = file_get_contents($path);
        $fullPath = "{$this->path}/{$fileName}";

        Storage::disk($this->disk)->put($fullPath, $contents);

        return Storage::disk($this->disk)->url($fullPath);
    }

    protected function generateUniqueFileName(string $extension): string
    {
        return Str::random(40) . '.' . $extension;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/pest tests/Messaging/FileUploaderTest.php`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Messaging/FileUploader.php tests/Messaging/FileUploaderTest.php
git commit -m "feat: add FileUploader for local file handling"
```

---

### Task 5: Create MessageBuilder

**Files:**
- Create: `src/Messaging/MessageBuilder.php`
- Create: `tests/Messaging/MessageBuilderTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;

beforeEach(function () {
    $this->client = Mockery::mock(WhatspieClient::class);
    $this->builder = new MessageBuilder($this->client, '6289876543210');
});

test('sets receiver number', function () {
    $builder = MessageBuilder::to('6281234567890');

    expect($builder)->toBeInstanceOf(MessageBuilder::class);
});

test('builds text message payload', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['type'] === 'chat' &&
                $payload['params']['text'] === 'Hello World';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->text('Hello World')->send();
});

test('builds text message with typing indicator', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['simulate_typing'] === 1;
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->text('Hello')->withTyping()->send();
});

test('builds file message from url', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['type'] === 'file' &&
                $payload['params']['document']['url'] === 'https://example.com/doc.pdf' &&
                $payload['params']['mimetype'] === 'application/pdf';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->file('https://example.com/doc.pdf', 'application/pdf')->send();
});

test('builds file message with filename', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['params']['fileName'] === 'Report.pdf';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->file('https://example.com/doc.pdf', 'application/pdf')
        ->fileName('Report.pdf')
        ->send();
});

test('builds image message', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['type'] === 'image';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->image('https://example.com/photo.jpg')->send();
});

test('builds image message with caption', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return isset($payload['params']['caption']) &&
                $payload['params']['caption'] === 'Check this out';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->image('https://example.com/photo.jpg')->caption('Check this out')->send();
});

test('builds location message', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['type'] === 'location' &&
                $payload['params']['latitude'] === -6.2088 &&
                $payload['params']['longitude'] === 106.8456;
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->location(-6.2088, 106.8456)->send();
});

test('builds location message with name and address', function () {
    $this->client->shouldReceive('send')
        ->once()
        ->with('6289876543210', Mockery::on(function ($payload) {
            return $payload['params']['name'] === 'Jakarta' &&
                $payload['params']['address'] === 'Indonesia';
        }))
        ->andReturn(new \MasRodjie\LaravelWhatspie\Messaging\Result(200, [
            'code' => 200,
            'data' => ['id' => 'msg_123'],
        ]));

    $this->builder->location(-6.2088, 106.8456)->name('Jakarta')->address('Indonesia')->send();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Messaging/MessageBuilderTest.php`

Expected: FAIL with "Class MasRodjie\LaravelWhatspie\Messaging\MessageBuilder not found"

- [ ] **Step 3: Write implementation**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Messaging;

use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;

class MessageBuilder
{
    protected WhatspieClient $client;
    protected string $receiver;
    protected ?string $device;
    protected array $payload = [];
    protected FileUploader $uploader;
    protected bool $simulateTyping = false;

    public function __construct(WhatspieClient $client, string $receiver, ?string $device = null)
    {
        $this->client = $client;
        $this->receiver = $receiver;
        $this->device = $device;
        $this->uploader = new FileUploader(
            config('whatspie.storage.disk'),
            config('whatspie.storage.path')
        );
    }

    public static function to(string $receiver): self
    {
        $client = app(WhatspieClient::class);
        return new self($client, $receiver, config('whatspie.device'));
    }

    public function text(string $message): self
    {
        $this->payload['type'] = 'chat';
        $this->payload['params']['text'] = $message;

        return $this;
    }

    public function file(string|UploadedFile $file, string $mimetype): self
    {
        $url = $this->uploader->upload($file);

        $this->payload['type'] = 'file';
        $this->payload['params']['document'] = ['url' => $url];
        $this->payload['params']['mimetype'] = $mimetype;

        return $this;
    }

    public function fileName(string $name): self
    {
        $this->payload['params']['fileName'] = $name;

        return $this;
    }

    public function image(string|UploadedFile $file): self
    {
        $url = $this->uploader->upload($file);

        $this->payload['type'] = 'image';
        $this->payload['params']['url'] = $url;

        return $this;
    }

    public function caption(string $text): self
    {
        $this->payload['params']['caption'] = $text;

        return $this;
    }

    public function location(float $latitude, float $longitude): self
    {
        $this->payload['type'] = 'location';
        $this->payload['params']['latitude'] = $latitude;
        $this->payload['params']['longitude'] = $longitude;

        return $this;
    }

    public function name(string $name): self
    {
        $this->payload['params']['name'] = $name;

        return $this;
    }

    public function address(string $address): self
    {
        $this->payload['params']['address'] = $address;

        return $this;
    }

    public function withTyping(): self
    {
        $this->simulateTyping = true;

        return $this;
    }

    public function send(): Result
    {
        if ($this->simulateTyping) {
            $this->payload['simulate_typing'] = 1;
        }

        return $this->client->send($this->receiver, $this->payload);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/pest tests/Messaging/MessageBuilderTest.php`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Messaging/MessageBuilder.php tests/Messaging/MessageBuilderTest.php
git commit -m "feat: add MessageBuilder for fluent API"
```

---

### Task 6: Create Whatspie Facade

**Files:**
- Create: `src/Facades/Whatspie.php`
- Create: `tests/Unit/FacadeTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use MasRodjie\LaravelWhatspie\Facades\Whatspie;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;
use MasRodjie\LaravelWhatspie\Messaging\Result;

beforeEach(function () {
    Mockery::mock(WhatspieClient::class)
        ->shouldReceive('send')
        ->andReturn(new Result(200, ['code' => 200, 'data' => ['id' => 'msg_123']]))
        ->byDefault();

    Whatspie::shouldReceive('to')
        ->andReturnSelf();
});

test('facade provides access to message builder', function () {
    expect(Whatspie::to('6281234567890'))->toBeInstanceOf(\MasRodjie\LaravelWhatspie\Messaging\MessageBuilder::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Unit/FacadeTest.php`

Expected: FAIL with facade not found

- [ ] **Step 3: Create facade**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Facades;

use Illuminate\Support\Facades\Facade;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;

/**
 * @method static MessageBuilder to(string $receiver)
 *
 * @see MessageBuilder
 */
class Whatspie extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MessageBuilder::class;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/pest tests/Unit/FacadeTest.php`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Facades/Whatspie.php tests/Unit/FacadeTest.php
git commit -m "feat: add Whatspie facade"
```

---

### Task 7: Create Webhook Events

**Files:**
- Create: `src/Events/WebhookReceived.php`
- Create: `src/Events/TextMessageReceived.php`
- Create: `src/Events/ImageMessageReceived.php`
- Create: `src/Events/AudioMessageReceived.php`
- Create: `src/Events/FileMessageReceived.php`
- Create: `src/Events/ContactMessageReceived.php`
- Create: `src/Events/LocationMessageReceived.php`
- Create: `tests/Events/WebhookEventTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;

test('WebhookReceived contains raw payload', function () {
    $payload = ['message' => 'test', 'from' => '6281234567890'];
    $event = new WebhookReceived($payload);

    expect($event->payload)->toBe($payload);
    expect($event->from)->toBe('6281234567890');
});

test('TextMessageReceived has message properties', function () {
    $payload = [
        'message' => 'Hello World',
        'from' => '6281234567890',
        'timestamp' => 1581651709,
        'message_id' => '3219EDE2131',
        'from_user' => [
            'name' => 'John Doe',
            'jid' => '6281234567890@s.whatsapp.net',
        ],
        'is_forwarded' => false,
        'is_broadcast' => false,
    ];

    $event = new TextMessageReceived($payload);

    expect($event->message)->toBe('Hello World');
    expect($event->from)->toBe('6281234567890');
    expect($event->timestamp)->toBe(1581651709);
    expect($event->messageId)->toBe('3219EDE2131');
    expect($event->fromUser)->toBe(['name' => 'John Doe', 'jid' => '6281234567890@s.whatsapp.net']);
    expect($event->isForwarded)->toBeFalse();
    expect($event->isBroadcast)->toBeFalse();
});

test('ImageMessageReceived has file properties', function () {
    $payload = [
        'type' => 'imageMessage',
        'file' => [
            'url' => 'https://example.com/image.jpg',
            'caption' => 'Check this out',
        ],
        'from' => '6281234567890',
        'timestamp' => 1664932915,
        'message_id' => '3EB0237E918896635AD3',
    ];

    $event = new ImageMessageReceived($payload);

    expect($event->fileUrl)->toBe('https://example.com/image.jpg');
    expect($event->caption)->toBe('Check this out');
});

test('AudioMessageReceived has duration', function () {
    $payload = [
        'type' => 'audioMessage',
        'file' => [
            'url' => 'https://example.com/audio.oga',
            'seconds' => 15,
        ],
        'message_id' => '3EB0E9CAAA9BBC8C1081',
    ];

    $event = new AudioMessageReceived($payload);

    expect($event->fileUrl)->toBe('https://example.com/audio.oga');
    expect($event->duration)->toBe(15);
});

test('FileMessageReceived has filename', function () {
    $payload = [
        'type' => 'fileMessage',
        'file' => [
            'url' => 'https://example.com/doc.pdf',
            'fileName' => 'document.pdf',
        ],
        'message_id' => 'msg123',
    ];

    $event = new FileMessageReceived($payload);

    expect($event->fileUrl)->toBe('https://example.com/doc.pdf');
    expect($event->fileName)->toBe('document.pdf');
});

test('ContactMessageReceived has contacts', function () {
    $payload = [
        'type' => 'contactMessage',
        'contacts' => [
            [
                'displayName' => 'Tech Support',
                'vcard' => 'BEGIN:VCARD\nVERSION:3.0\nFN:Tech Support\nEND:VCARD',
            ],
        ],
        'message_id' => 'msg123',
    ];

    $event = new ContactMessageReceived($payload);

    expect($event->contacts)->toHaveCount(1);
    expect($event->contacts[0]['displayName'])->toBe('Tech Support');
});

test('LocationMessageReceived has coordinates', function () {
    $payload = [
        'type' => 'locationMessage',
        'latitude' => -6.2088,
        'longitude' => 106.8456,
        'message_id' => 'msg123',
    ];

    $event = new LocationMessageReceived($payload);

    expect($event->latitude)->toBe(-6.2088);
    expect($event->longitude)->toBe(106.8456);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Events/WebhookEventTest.php`

Expected: FAIL with events not found

- [ ] **Step 3: Create base event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $payload
    ) {
    }

    public function from(): ?string
    {
        return $this->payload['from'] ?? null;
    }
}
```

- [ ] **Step 4: Create text message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class TextMessageReceived extends WebhookReceived
{
    public function __construct(array $payload)
    {
        parent::__construct($payload);
    }

    public function message(): string
    {
        return $this->payload['message'];
    }

    public function fromUser(): ?array
    {
        return $this->payload['from_user'] ?? null;
    }

    public function timestamp(): int
    {
        return $this->payload['timestamp'];
    }

    public function messageId(): string
    {
        return $this->payload['message_id'];
    }

    public function isForwarded(): bool
    {
        return $this->payload['is_forwarded'] ?? false;
    }

    public function isBroadcast(): bool
    {
        return $this->payload['is_broadcast'] ?? false;
    }
}
```

- [ ] **Step 5: Create image message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class ImageMessageReceived extends WebhookReceived
{
    public function fileUrl(): string
    {
        return $this->payload['file']['url'];
    }

    public function caption(): ?string
    {
        return $this->payload['file']['caption'] ?? null;
    }
}
```

- [ ] **Step 6: Create audio message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class AudioMessageReceived extends WebhookReceived
{
    public function fileUrl(): string
    {
        return $this->payload['file']['url'];
    }

    public function duration(): int
    {
        return $this->payload['file']['seconds'];
    }
}
```

- [ ] **Step 7: Create file message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class FileMessageReceived extends WebhookReceived
{
    public function fileUrl(): string
    {
        return $this->payload['file']['url'];
    }

    public function fileName(): ?string
    {
        return $this->payload['file']['fileName'] ?? null;
    }
}
```

- [ ] **Step 8: Create contact message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class ContactMessageReceived extends WebhookReceived
{
    public function contacts(): array
    {
        return $this->payload['contacts'];
    }
}
```

- [ ] **Step 9: Create location message event**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Events;

class LocationMessageReceived extends WebhookReceived
{
    public function latitude(): float
    {
        return $this->payload['latitude'];
    }

    public function longitude(): float
    {
        return $this->payload['longitude'];
    }

    public function name(): ?string
    {
        return $this->payload['name'] ?? null;
    }

    public function address(): ?string
    {
        return $this->payload['address'] ?? null;
    }
}
```

- [ ] **Step 10: Run test to verify it passes**

Run: `vendor/bin/pest tests/Events/WebhookEventTest.php`

Expected: PASS

- [ ] **Step 11: Commit**

```bash
git add src/Events/ tests/Events/
git commit -m "feat: add webhook events"
```

---

### Task 8: Create WebhookController

**Files:**
- Create: `src/Http/Controllers/WebhookController.php`
- Create: `tests/Http/WebhookControllerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;

beforeEach(function () {
    $this->controller = new WebhookController();
});

test('dispatches WebhookReceived event', function () {
    Event::fake([WebhookReceived::class]);

    $payload = ['message' => 'test', 'from' => '6281234567890'];

    $response = $this->controller->handle($payload);

    $response->assertStatus(200);
    Event::assertDispatched(WebhookReceived::class);
});

test('dispatches TextMessageReceived for text messages', function () {
    Event::fake([TextMessageReceived::class]);

    $payload = [
        'message' => 'Hello World',
        'from' => '6281234567890',
        'timestamp' => 1581651709,
        'message_id' => '3219EDE2131',
        'from_user' => ['name' => 'John Doe', 'jid' => '6281234567890@s.whatsapp.net'],
        'is_forwarded' => false,
        'is_broadcast' => false,
    ];

    $this->controller->handle($payload);

    Event::assertDispatched(TextMessageReceived::class);
});

test('dispatches ImageMessageReceived for image messages', function () {
    Event::fake([ImageMessageReceived::class]);

    $payload = [
        'type' => 'imageMessage',
        'file' => ['url' => 'https://example.com/image.jpg', 'caption' => 'Test'],
        'from' => '6281234567890',
        'timestamp' => 1664932915,
        'message_id' => '3EB0237E918896635AD3',
    ];

    $this->controller->handle($payload);

    Event::assertDispatched(ImageMessageReceived::class);
});

test('returns 200 response', function () {
    $response = $this->controller->handle(['test' => 'data']);

    $response->assertStatus(200);
    expect($response->getContent())->toBe('OK');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Http/WebhookControllerTest.php`

Expected: FAIL with controller not found

- [ ] **Step 3: Write implementation**

```php
<?php

namespace MasRodjie\LaravelWhatspie\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;

class WebhookController
{
    public function handle(array $payload): Response
    {
        // Always dispatch the base webhook event
        WebhookReceived::dispatch($payload);

        // Dispatch specific event based on message type
        $eventType = $payload['type'] ?? null;

        $event = match ($eventType) {
            'imageMessage' => ImageMessageReceived::class,
            'audioMessage' => AudioMessageReceived::class,
            'fileMessage' => FileMessageReceived::class,
            'contactMessage' => ContactMessageReceived::class,
            'locationMessage' => LocationMessageReceived::class,
            default => TextMessageReceived::class,
        };

        $event::dispatch($payload);

        return response('OK', 200);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/pest tests/Http/WebhookControllerTest.php`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Http/Controllers/WebhookController.php tests/Http/WebhookControllerTest.php
git commit -m "feat: add WebhookController"
```

---

### Task 9: Update Service Provider

**Files:**
- Modify: `src/LaravelWhatspieServiceProvider.php`

- [ ] **Step 1: Update service provider**

```php
<?php

namespace MasRodjie\LaravelWhatspie;

use MasRodjie\LaravelWhatspie\Commands\LaravelWhatspieCommand;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient as WhatspieClientContract;
use MasRodjie\LaravelWhatspie\Http\WhatspieClient;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelWhatspieServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-whatspie')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(LaravelWhatspieCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(WhatspieClientContract::class, function () {
            return new WhatspieClient(
                config('whatspie.api_token'),
                config('whatspie.device')
            );
        });

        $this->app->bind(MessageBuilder::class, function () {
            return new MessageBuilder(
                $this->app->make(WhatspieClientContract::class),
                config('whatspie.device')
            );
        });
    }

    public function packageBooted(): void
    {
        if (config('whatspie.webhook.enabled', true)) {
            Route::post(config('whatspie.webhook.path', '/whatspie/webhook'), [
                'uses' => \MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController::class . '@handle',
            ])->name('whatspie.webhook');
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/LaravelWhatspieServiceProvider.php
git commit -m "feat: register bindings and webhook route in service provider"
```

---

### Task 10: Update Package Facade Alias

**Files:**
- Modify: `src/LaravelWhatspieServiceProvider.php`

- [ ] **Step 1: Remove hasMigration from package config**

The package doesn't need database migrations (Whatspie handles storage). Remove the migration reference from the skeleton.

Check the current service provider and remove `->hasMigration()` if present.

- [ ] **Step 2: Commit**

```bash
git add src/LaravelWhatspieServiceProvider.php
git commit -m "chore: remove unnecessary migration reference"
```

---

### Task 11: Update README

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Update README with usage examples**

```markdown
# Laravel Whatspie

A Laravel package that simplifies integration with the Whatspie API for sending and receiving WhatsApp messages.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masrodjie/laravel-whatspie.svg?style=flat-square)](https://packagist.org/packages/masrodjie/laravel-whatspie)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/masrodjie/laravel-whatspie/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/masrodjie/laravel-whatspie/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/masrodjie/laravel-whatspie.svg?style=flat-square)](https://packagist.org/packages/masrodjie/laravel-whatspie)

## Installation

You can install the package via composer:

```bash
composer require masrodjie/laravel-whatspie
```

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-whatspie-config"
```

Add your credentials to `.env`:

```env
WHATSPIE_API_TOKEN=your_api_token
WHATSPIE_DEVICE=6281234567890
```

## Usage

### Sending Messages

```php
use MasRodjie\LaravelWhatspie\Facades\Whatspie;

// Send a text message
$result = Whatspie::to('6289876543210')
    ->text('Hello World')
    ->send();

if ($result->successful()) {
    echo "Message ID: {$result->id()}";
} else {
    echo "Error: {$result->error()}";
}

// Send with typing indicator
Whatspie::to('6289876543210')
    ->text('Hello')
    ->withTyping()
    ->send();

// Send an image (from URL)
Whatspie::to('6289876543210')
    ->image('https://example.com/photo.jpg')
    ->caption('Check this out')
    ->send();

// Send an image (from UploadedFile)
Whatspie::to('6289876543210')
    ->image($request->file('photo'))
    ->caption('Check this out')
    ->send();

// Send a document
Whatspie::to('6289876543210')
    ->file('https://example.com/document.pdf', 'application/pdf')
    ->fileName('Invoice.pdf')
    ->send();

// Send a location
Whatspie::to('6289876543210')
    ->location(-6.2088, 106.8456)
    ->name('Jakarta')
    ->address('Indonesia')
    ->send();
```

### Receiving Webhooks

The package automatically registers a webhook route at `/whatspie/webhook`.

Configure your webhook URL in the Whatspie dashboard to point to:
```
https://your-domain.com/whatspie/webhook
```

#### Event Listeners

Create an event listener to handle incoming messages:

```php
// App\Listeners\HandleIncomingText.php

namespace App\Listeners;

use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use Illuminate\Support\Facades\Log;

class HandleIncomingText
{
    public function handle(TextMessageReceived $event)
    {
        Log::info("New message from {$event->from()}: {$event->message()}");
    }
}
```

Register the listener in `App\Providers\EventServiceProvider`:

```php
protected $listen = [
    \MasRodjie\LaravelWhatspie\Events\TextMessageReceived::class => [
        \App\Listeners\HandleIncomingText::class,
    ],
];
```

#### Available Events

| Event | Description |
|-------|-------------|
| `WebhookReceived` | Base event for all webhooks |
| `TextMessageReceived` | Text message received |
| `ImageMessageReceived` | Image message received |
| `AudioMessageReceived` | Audio message received |
| `FileMessageReceived` | Document/file received |
| `ContactMessageReceived` | Contact card received |
| `LocationMessageReceived` | Location message received |

## Configuration

```php
// config/whatspie.php
return [
    'api_token' => env('WHATSPIE_API_TOKEN'),
    'device' => env('WHATSPIE_DEVICE'),

    'webhook' => [
        'enabled' => true,
        'path' => '/whatspie/webhook',
        'secret' => env('WHATSPIE_WEBHOOK_SECRET'),
    ],

    'storage' => [
        'disk' => env('WHATSPIE_STORAGE_DISK', 'public'),
        'path' => 'whatspie',
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Arnas MasRodjie](https://github.com/masrodjie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: update README with usage examples"
```

---

### Task 12: Final Verification

**Files:**
- Run all tests

- [ ] **Step 1: Run all tests**

Run: `vendor/bin/pest`

Expected: All tests PASS

- [ ] **Step 2: Run static analysis**

Run: `vendor/bin/phpstan analyse`

Expected: No errors (or acceptable level)

- [ ] **Step 3: Check code style**

Run: `vendor/bin/pint`

Expected: No changes needed

- [ ] **Step 4: Verify package loads correctly**

Create a test Laravel app or use workbench to verify the facade works:

```bash
php artisan tinker
>>> Whatspie::to('6281234567890')
=> MasRodjie\LaravelWhatspie\Messaging\MessageBuilder {#...}
```

---

## Implementation Complete

After completing all tasks, the package will have:

1. ✅ Fluent API for sending messages
2. ✅ File upload support (local and remote)
3. ✅ Webhook handling with event dispatching
4. ✅ Config-driven authentication
5. ✅ Result objects for error handling
6. ✅ Full test coverage
