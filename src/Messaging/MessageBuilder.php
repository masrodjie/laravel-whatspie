<?php

namespace MasRodjie\LaravelWhatspie\Messaging;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient;

class MessageBuilder
{
    protected string $receiver;

    protected ?WhatspieClient $client = null;

    protected ?FileUploader $uploader = null;

    protected ?string $messageType = null;

    protected array $messageParams = [];

    protected bool $withTyping = false;

    protected function __construct(string $receiver)
    {
        $this->receiver = $receiver;
    }

    public static function to(string $receiver): self
    {
        return new self($receiver);
    }

    public function using(WhatspieClient $client, ?FileUploader $uploader = null): self
    {
        $this->client = $client;
        $this->uploader = $uploader;

        return $this;
    }

    public function text(string $message): self
    {
        $this->messageType = 'chat';
        $this->messageParams['text'] = $message;

        return $this;
    }

    public function file(string|UploadedFile $file, string $mimetype): self
    {
        $this->messageType = 'file';

        // Upload file if not a public URL
        if (is_string($file) && $this->isPublicUrl($file)) {
            $this->messageParams['document'] = ['url' => $file];
        } else {
            if ($this->uploader === null) {
                throw new InvalidArgumentException('FileUploader is required for uploading files. Call using() with a FileUploader instance.');
            }
            $url = $this->uploader->upload($file);
            $this->messageParams['document'] = ['url' => $url];
        }

        $this->messageParams['mimetype'] = $mimetype;

        return $this;
    }

    public function fileName(string $name): self
    {
        $this->messageParams['fileName'] = $name;

        return $this;
    }

    public function image(string|UploadedFile $file): self
    {
        $this->messageType = 'image';

        // Upload file if not a public URL
        if (is_string($file) && $this->isPublicUrl($file)) {
            $this->messageParams['image'] = ['url' => $file];
        } else {
            if ($this->uploader === null) {
                throw new InvalidArgumentException('FileUploader is required for uploading images. Call using() with a FileUploader instance.');
            }
            $url = $this->uploader->upload($file);
            $this->messageParams['image'] = ['url' => $url];
        }

        return $this;
    }

    public function caption(string $text): self
    {
        $this->messageParams['caption'] = $text;

        return $this;
    }

    public function location(float $lat, float $long): self
    {
        $this->messageType = 'location';
        $this->messageParams['lat'] = $lat;
        $this->messageParams['long'] = $long;

        return $this;
    }

    public function name(string $name): self
    {
        $this->messageParams['name'] = $name;

        return $this;
    }

    public function address(string $address): self
    {
        $this->messageParams['address'] = $address;

        return $this;
    }

    public function withTyping(): self
    {
        $this->withTyping = true;

        return $this;
    }

    public function send(): Result
    {
        if ($this->client === null) {
            throw new InvalidArgumentException('WhatspieClient is required. Call using() with a WhatspieClient instance before calling send().');
        }

        if ($this->messageType === null) {
            throw new InvalidArgumentException('Message type is required. Call a message builder method (text, file, image, or location) before calling send().');
        }

        $payload = [
            'type' => $this->messageType,
            'params' => $this->messageParams,
        ];

        if ($this->withTyping) {
            $payload['simulate_typing'] = 1;
        }

        return $this->client->send($this->receiver, $payload);
    }

    protected function isPublicUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
