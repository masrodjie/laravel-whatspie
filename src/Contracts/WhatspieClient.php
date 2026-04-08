<?php

namespace MasRodjie\LaravelWhatspie\Contracts;

use MasRodjie\LaravelWhatspie\Messaging\Result;

interface WhatspieClient
{
    public function send(string $receiver, array $payload): Result;
}
