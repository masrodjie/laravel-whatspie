<?php

namespace MasRodjie\LaravelWhatspie\Http;

use Illuminate\Support\Facades\Http;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient as WhatspieClientContract;
use MasRodjie\LaravelWhatspie\Messaging\Result;

class WhatspieClient implements WhatspieClientContract
{
    protected string $apiToken;

    protected string $device;

    protected string $baseUrl;

    public function __construct(string $apiToken, string $device, ?string $baseUrl = null)
    {
        $this->apiToken = $apiToken;
        $this->device = $device;
        $this->baseUrl = $baseUrl ?? config('whatspie.base_url', 'https://api.whatspie.com');
    }

    public function send(string $receiver, array $payload): Result
    {
        $payload = array_merge([
            'device' => $this->device,
            'receiver' => $receiver,
        ], $payload);

        // Debug: log the request payload
        if (config('app.debug')) {
            logger()->debug('Whatspie Request', [
                'url' => "{$this->baseUrl}/messages",
                'payload' => $payload,
            ]);
        }

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/messages", $payload);

        // Debug: log the response
        if (config('app.debug')) {
            logger()->debug('Whatspie Response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return new Result(
            $response->status(),
            $response->json() ?? []
        );
    }
}
