# Laravel Whatspie Package v1.0 Design

**Date:** 2026-04-08
**Author:** Arnas MasRodjie
**Status:** Approved

## Overview

Laravel Whatspie is a Laravel package that simplifies integration with the Whatspie API. It provides a fluent API for sending WhatsApp messages and a complete webhook system for receiving messages.

## Goals

- Simple, expressive API for sending messages via Whatspie
- Automatic webhook handling with Laravel event dispatching
- Config-driven authentication
- Predictable error handling without exceptions

## Architecture

### Components

| Component | Purpose |
|-----------|---------|
| `Whatspie` facade | Main entry point for the fluent API |
| `MessageBuilder` | Builds message payloads with chainable methods |
| `WhatspieClient` | HTTP client that communicates with Whatspie API |
| `WebhookController` | Receives incoming webhook POST requests |
| `EventDispatcher` | Dispatches Laravel events for each message type |
| `Result` class | Encapsulates API response (success/failure + data) |

## Fluent API Design

### Basic Usage

```php
use MasRodjie\LaravelWhatspie\Facades\Whatspie;

// Text message
$result = Whatspie::to('6281234567890')
    ->text('Hello World')
    ->send();

// With typing indicator
Whatspie::to('6281234567890')
    ->text('Hello')
    ->withTyping()
    ->send();

// File/document
Whatspie::to('6281234567890')
    ->file('https://example.com/doc.pdf', 'application/pdf')
    ->fileName('Report.pdf')
    ->send();

// Image with caption
Whatspie::to('6281234567890')
    ->image('https://example.com/photo.jpg')
    ->caption('Check this out')
    ->send();

// Location
Whatspie::to('6281234567890')
    ->location(-6.2088, 106.8456)
    ->name('Jakarta')
    ->address('Indonesia')
    ->send();
```

### Result Object

```php
$result = Whatspie::to('6281234567890')->text('Hello')->send();

if ($result->successful()) {
    echo "Message ID: {$result->id()}";
} else {
    echo "Error: {$result->error()}";
}
```

## Webhook System

### Configuration

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
];
```

### Events

| Event | Fired When | Payload Properties |
|-------|------------|-------------------|
| `TextMessageReceived` | Text message | `message`, `from`, `from_user`, `timestamp`, `message_id` |
| `ImageMessageReceived` | Image | + `file.url`, `file.caption` |
| `AudioMessageReceived` | Audio | + `file.url`, `file.seconds` |
| `FileMessageReceived` | Document | + `file.url`, `file.fileName` |
| `ContactMessageReceived` | Contact shared | + `contacts[]` with vCard |
| `LocationMessageReceived` | Location | + location data |
| `WebhookReceived` | Any webhook | Raw payload |

### Event Listener Example

```php
// App\Listeners\HandleIncomingText.php
class HandleIncomingText
{
    public function handle(TextMessageReceived $event)
    {
        $from = $event->from;
        $message = $event->message;

        // Handle the message
        Log::info("Message from {$from}: {$message}");
    }
}
```

## Error Handling

### Result Class Interface

```php
class Result
{
    public function successful(): bool;
    public function failed(): bool;
    public function id(): ?string;        // Message ID if successful
    public function data(): array;        // Full response data
    public function error(): ?string;     // Error message if failed
    public function errorCode(): ?int;    // HTTP status code
}
```

### Failure Cases

- Invalid API token
- Invalid phone number format
- File URL inaccessible
- Network timeout
- Whatspie API errors (4xx/5xx)

All errors captured in Result object - no exceptions thrown by default.

## Directory Structure

```
src/
├── LaravelWhatspieServiceProvider.php
├── Facades/
│   └── Whatspie.php
├── Contracts/
│   └── WhatspieClient.php
├── Http/
│   ├── Controllers/
│   │   └── WebhookController.php
│   └── WhatspieClient.php
├── Messaging/
│   ├── MessageBuilder.php
│   └── Result.php
├── Events/
│   ├── WebhookReceived.php
│   ├── TextMessageReceived.php
│   ├── ImageMessageReceived.php
│   ├── AudioMessageReceived.php
│   ├── FileMessageReceived.php
│   ├── ContactMessageReceived.php
│   └── LocationMessageReceived.php
└── config/
    └── whatspie.php
```

## Dependencies

- `illuminate/http` - HTTP client (already in Laravel)
- `illuminate/events` - Event dispatcher (already in Laravel)

Uses Laravel's native `Http` facade - no external HTTP clients.

## Testing

- Mock `Http::fake()` for API calls
- Test webhook payload parsing
- Test event dispatching
- Pest for all tests

## Out of Scope for v1.0

- Message queues
- Template management
- Multi-device support
- Retry logic
- Database migrations (Whatspie handles storage)

## API Reference

### Whatspie API Base URL
```
https://api.whatspie.com
```

### Send Message Endpoint
```
POST /messages
```

### Webhook Requirements
- Must accept POST requests
- Must respond with HTTP 200 within 5 seconds
- Should be accessible via HTTPS
