# Laravel Whatspie

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masrodjie/laravel-whatspie.svg?style=flat-square)](https://packagist.org/packages/masrodjie/laravel-whatspie)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/masrodjie/laravel-whatspie/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/masrodjie/laravel-whatspie/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/masrodjie/laravel-whatspie/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/masrodjie/laravel-whatspie/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/masrodjie/laravel-whatspie.svg?style=flat-square)](https://packagist.org/packages/masrodjie/laravel-whatspie)

A Laravel package that provides a fluent interface for sending WhatsApp messages through the [Whatspie](https://whatspie.com) API. This package allows you to send text messages, images, files, and location messages, as well as receive webhooks for incoming messages.

## Features

- Send text messages with optional typing indicator
- Send images with captions
- Send files with custom filenames and MIME types
- Send location messages with name and address
- Handle incoming webhooks with typed events
- Automatic file upload to configured storage
- Fluent, expressive API

## Installation

You can install the package via composer:

```bash
composer require masrodjie/laravel-whatspie
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-whatspie-config"
```

This is the default contents of the published config file:

```php
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

## Configuration

Add the following environment variables to your `.env` file:

```env
WHATSPIE_API_TOKEN=your_api_token_here
WHATSPIE_DEVICE=6281234567890

# Optional - Webhook configuration
WHATSPIE_WEBHOOK_ENABLED=true
WHATSPIE_WEBHOOK_PATH=/whatspie/webhook
WHATSPIE_WEBHOOK_SECRET=your_webhook_secret

# Optional - Storage configuration
WHATSPIE_STORAGE_DISK=public
WHATSPIE_STORAGE_PATH=whatspie
```

### Getting Your API Token

1. Sign up or log in at [https://whatspie.com](https://whatspie.com)
2. Navigate to the dashboard
3. Copy your API token
4. Register your WhatsApp device number

## Usage

### Sending Messages

The package provides a fluent API for sending messages through the `Whatspie` facade.

#### Text Messages

Send a simple text message:

```php
use MasRodjie\LaravelWhatspie\Facades\Whatspie;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;

$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->text('Hello from Laravel!')
    ->send();

if ($result->successful()) {
    echo "Message sent! ID: " . $result->id();
}
```

Send a text message with typing indicator:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->text('Hello from Laravel!')
    ->withTyping()
    ->send();
```

#### Image Messages

Send an image from a URL:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->image('https://example.com/photo.jpg')
    ->send();
```

Send an image with a caption:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->image('https://example.com/photo.jpg')
    ->caption('Check this out!')
    ->send();
```

Upload and send a local image:

```php
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;

$uploader = new FileUploader(
    config('whatspie.storage.disk'),
    config('whatspie.storage.path')
);

$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class), $uploader)
    ->image('/path/to/local/image.jpg')
    ->caption('Local image')
    ->send();
```

#### File Messages

Send a file from a URL:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->file('https://example.com/document.pdf', 'application/pdf')
    ->send();
```

Send a file with a custom filename:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->file('https://example.com/document.pdf', 'application/pdf')
    ->fileName('Monthly Report.pdf')
    ->send();
```

Upload and send a local file:

```php
use Illuminate\Http\UploadedFile;
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;

$uploader = new FileUploader(
    config('whatspie.storage.disk'),
    config('whatspie.storage.path')
);

// From a file path
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class), $uploader)
    ->file('/path/to/document.pdf', 'application/pdf')
    ->send();

// From an UploadedFile (e.g., form upload)
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class), $uploader)
    ->file($uploadedFile, 'application/pdf')
    ->send();
```

#### Location Messages

Send a location:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->location(-6.2088, 106.8456)
    ->send();
```

Send a location with name and address:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->location(-6.2088, 106.8456)
    ->name('Monas')
    ->address('Jakarta, Indonesia')
    ->send();
```

### Handling Results

The `send()` method returns a `Result` object:

```php
$result = Whatspie::to('6281234567890')
    ->using(app(WhatspieClient::class))
    ->text('Hello!')
    ->send();

// Check if successful
if ($result->successful()) {
    // Get message ID
    $messageId = $result->id();
}

// Check if failed
if ($result->failed()) {
    // Get error message
    $error = $result->error();
    $code = $result->errorCode();
}

// Get full response data
$data = $result->data();
```

### Webhooks

The package automatically registers a webhook route when enabled in the configuration. Webhooks are dispatched as Laravel events that you can listen to in your application.

#### Available Events

| Event | Description |
|-------|-------------|
| `WebhookReceived` | Base event for all incoming webhooks |
| `TextMessageReceived` | Fired when a text message is received |
| `ImageMessageReceived` | Fired when an image message is received |
| `AudioMessageReceived` | Fired when an audio message is received |
| `FileMessageReceived` | Fired when a file/document message is received |
| `ContactMessageReceived` | Fired when a contact message is received |
| `LocationMessageReceived` | Fired when a location message is received |

#### Event Listeners

Create an event listener. For example, using `php artisan make:listener`:

```bash
php artisan make:listener HandleIncomingWhatsAppMessage
```

In your `AppServiceProvider` or a dedicated service provider:

```php
use Illuminate\Support\Facades\Event;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use App\Listeners\HandleIncomingWhatsAppMessage;

protected $listen = [
    TextMessageReceived::class => [
        HandleIncomingWhatsAppMessage::class,
    ],
    ImageMessageReceived::class => [
        HandleIncomingWhatsAppMessage::class,
    ],
];
```

Example listener:

```php
namespace App\Listeners;

use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;

class HandleIncomingWhatsAppMessage
{
    public function handle(TextMessageReceived $event)
    {
        $from = $event->fromUser();
        $message = $event->message();
        $timestamp = $event->timestamp();

        // Process the incoming message
        // Store in database, trigger auto-reply, etc.
    }
}
```

#### Event Methods

**TextMessageReceived:**
- `fromUser()` - Sender's phone number
- `message()` - Message text content
- `timestamp()` - Message timestamp
- `messageId()` - Unique message ID
- `isForwarded()` - Whether message was forwarded
- `isBroadcast()` - Whether message was a broadcast

**ImageMessageReceived:**
- `fileUrl()` - URL to the image
- `caption()` - Image caption (if any)
- Inherits all methods from `WebhookReceived`

**AudioMessageReceived:**
- `fileUrl()` - URL to the audio file
- `duration()` - Audio duration in seconds (if available)
- Inherits all methods from `WebhookReceived`

**FileMessageReceived:**
- `fileUrl()` - URL to the file
- `fileName()` - File name (if available)
- Inherits all methods from `WebhookReceived`

**ContactMessageReceived:**
- `contacts()` - Array of contact data
- Inherits all methods from `WebhookReceived`

**LocationMessageReceived:**
- `latitude()` - Latitude coordinate
- `longitude()` - Longitude coordinate
- `name()` - Location name (if available)
- `address()` - Location address (if available)
- Inherits all methods from `WebhookReceived`

**WebhookReceived (base event):**
- `from()` - Sender's phone number
- `payload()` - Raw webhook payload array

#### Webhook Security

For production use, you should verify webhook requests. Configure a webhook secret in your `.env`:

```env
WHATSPIE_WEBHOOK_SECRET=your_random_secret_string
```

Then create middleware to verify incoming webhook signatures.

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
