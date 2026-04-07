<?php

use MasRodjie\LaravelWhatspie\Events\AudioMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ContactMessageReceived;
use MasRodjie\LaravelWhatspie\Events\FileMessageReceived;
use MasRodjie\LaravelWhatspie\Events\ImageMessageReceived;
use MasRodjie\LaravelWhatspie\Events\LocationMessageReceived;
use MasRodjie\LaravelWhatspie\Events\TextMessageReceived;
use MasRodjie\LaravelWhatspie\Events\WebhookReceived;

describe('WebhookReceived', function () {
    it('can be instantiated with a payload', function () {
        $payload = ['from' => '6281234567890'];
        $event = new WebhookReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the phone number from payload', function () {
        $payload = ['from' => '6281234567890'];
        $event = new WebhookReceived($payload);

        expect($event->from())->toBe('6281234567890');
    });

    it('returns null when from is not in payload', function () {
        $payload = ['message' => 'test'];
        $event = new WebhookReceived($payload);

        expect($event->from())->toBeNull();
    });
});

describe('TextMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the message body', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->message())->toBe('Hello World');
    });

    it('returns the from user phone number', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->fromUser())->toBe('6281234567890');
    });

    it('returns the timestamp', function () {
        $payload = [
            'from' => '6281234567890',
            'timestamp' => 1698765432,
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->timestamp())->toBe(1698765432);
    });

    it('returns the message id', function () {
        $payload = [
            'from' => '6281234567890',
            'id' => 'msg123',
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->messageId())->toBe('msg123');
    });

    it('returns whether message is forwarded', function () {
        $payload = [
            'from' => '6281234567890',
            'forwarded' => true,
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->isForwarded())->toBeTrue();
    });

    it('returns whether message is broadcast', function () {
        $payload = [
            'from' => '6281234567890',
            'broadcast' => true,
            'message' => ['body' => 'Hello World'],
        ];
        $event = new TextMessageReceived($payload);

        expect($event->isBroadcast())->toBeTrue();
    });
});

describe('ImageMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'image',
                'url' => 'https://example.com/image.jpg',
                'caption' => 'Test image',
            ],
        ];
        $event = new ImageMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the file url', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'image',
                'url' => 'https://example.com/image.jpg',
            ],
        ];
        $event = new ImageMessageReceived($payload);

        expect($event->fileUrl())->toBe('https://example.com/image.jpg');
    });

    it('returns the caption', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'image',
                'url' => 'https://example.com/image.jpg',
                'caption' => 'Test image',
            ],
        ];
        $event = new ImageMessageReceived($payload);

        expect($event->caption())->toBe('Test image');
    });

    it('returns null when caption is not present', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'image',
                'url' => 'https://example.com/image.jpg',
            ],
        ];
        $event = new ImageMessageReceived($payload);

        expect($event->caption())->toBeNull();
    });
});

describe('AudioMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'audio',
                'url' => 'https://example.com/audio.mp3',
                'duration' => 30,
            ],
        ];
        $event = new AudioMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the file url', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'audio',
                'url' => 'https://example.com/audio.mp3',
            ],
        ];
        $event = new AudioMessageReceived($payload);

        expect($event->fileUrl())->toBe('https://example.com/audio.mp3');
    });

    it('returns the duration', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'audio',
                'url' => 'https://example.com/audio.mp3',
                'duration' => 30,
            ],
        ];
        $event = new AudioMessageReceived($payload);

        expect($event->duration())->toBe(30);
    });

    it('returns null when duration is not present', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'audio',
                'url' => 'https://example.com/audio.mp3',
            ],
        ];
        $event = new AudioMessageReceived($payload);

        expect($event->duration())->toBeNull();
    });
});

describe('FileMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'file',
                'url' => 'https://example.com/document.pdf',
                'filename' => 'document.pdf',
            ],
        ];
        $event = new FileMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the file url', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'file',
                'url' => 'https://example.com/document.pdf',
            ],
        ];
        $event = new FileMessageReceived($payload);

        expect($event->fileUrl())->toBe('https://example.com/document.pdf');
    });

    it('returns the file name', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'file',
                'url' => 'https://example.com/document.pdf',
                'filename' => 'document.pdf',
            ],
        ];
        $event = new FileMessageReceived($payload);

        expect($event->fileName())->toBe('document.pdf');
    });

    it('returns null when filename is not present', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'file',
                'url' => 'https://example.com/document.pdf',
            ],
        ];
        $event = new FileMessageReceived($payload);

        expect($event->fileName())->toBeNull();
    });
});

describe('ContactMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'contact',
                'contacts' => [
                    ['name' => 'John Doe', 'phone' => '6289876543210'],
                ],
            ],
        ];
        $event = new ContactMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the contacts array', function () {
        $contacts = [
            ['name' => 'John Doe', 'phone' => '6289876543210'],
            ['name' => 'Jane Doe', 'phone' => '6285678901234'],
        ];
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'contact',
                'contacts' => $contacts,
            ],
        ];
        $event = new ContactMessageReceived($payload);

        expect($event->contacts())->toBe($contacts);
    });

    it('returns empty array when contacts are not present', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'contact',
            ],
        ];
        $event = new ContactMessageReceived($payload);

        expect($event->contacts())->toBe([]);
    });
});

describe('LocationMessageReceived', function () {
    it('extends WebhookReceived', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'name' => 'Monas',
                'address' => 'Jakarta, Indonesia',
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event)->toBeInstanceOf(WebhookReceived::class);
    });

    it('returns the latitude', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event->latitude())->toBe(-6.2088);
    });

    it('returns the longitude', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event->longitude())->toBe(106.8456);
    });

    it('returns the name', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'name' => 'Monas',
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event->name())->toBe('Monas');
    });

    it('returns the address', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Jakarta, Indonesia',
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event->address())->toBe('Jakarta, Indonesia');
    });

    it('returns null when optional fields are not present', function () {
        $payload = [
            'from' => '6281234567890',
            'message' => [
                'type' => 'location',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
            ],
        ];
        $event = new LocationMessageReceived($payload);

        expect($event->name())->toBeNull();
        expect($event->address())->toBeNull();
    });
});
