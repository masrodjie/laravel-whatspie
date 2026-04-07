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

    protected array $messageData = [];

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
        $this->messageType = 'text';
        $this->messageData['text'] = $message;

        return $this;
    }

    public function file(string|UploadedFile $file, string $mimetype): self
    {
        $this->messageType = 'file';

        // Upload file if not a public URL
        if (is_string($file) && $this->isPublicUrl($file)) {
            $this->messageData['file'] = $file;
        } else {
            if ($this->uploader === null) {
                throw new InvalidArgumentException('FileUploader is required for uploading files. Call using() with a FileUploader instance.');
            }
            $this->messageData['file'] = $this->uploader->upload($file);
        }

        $this->messageData['mimetype'] = $mimetype;

        return $this;
    }

    public function fileName(string $name): self
    {
        $this->messageData['filename'] = $name;

        return $this;
    }

    public function image(string|UploadedFile $file): self
    {
        $this->messageType = 'image';

        // Upload file if not a public URL
        if (is_string($file) && $this->isPublicUrl($file)) {
            $this->messageData['image'] = $file;
        } else {
            if ($this->uploader === null) {
                throw new InvalidArgumentException('FileUploader is required for uploading images. Call using() with a FileUploader instance.');
            }
            $this->messageData['image'] = $this->uploader->upload($file);
        }

        return $this;
    }

    public function caption(string $text): self
    {
        $this->messageData['caption'] = $text;

        return $this;
    }

    public function location(float $lat, float $long): self
    {
        $this->messageType = 'location';
        $this->messageData['lat'] = $lat;
        $this->messageData['long'] = $long;

        return $this;
    }

    public function name(string $name): self
    {
        $this->messageData['name'] = $name;

        return $this;
    }

    public function address(string $address): self
    {
        $this->messageData['address'] = $address;

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
            'message' => array_merge([
                'type' => $this->messageType,
            ], $this->messageData),
        ];

        if ($this->withTyping) {
            $payload['with_typing'] = true;
        }

        return $this->client->send($this->receiver, $payload);
    }

    protected function isPublicUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
