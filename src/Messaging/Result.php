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
