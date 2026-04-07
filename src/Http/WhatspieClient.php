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
