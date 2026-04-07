<?php

namespace MasRodjie\LaravelWhatspie\Facades;

use Illuminate\Support\Facades\Facade;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;

/**
 * @see \MasRodjie\LaravelWhatspie\Messaging\MessageBuilder
 */
class Whatspie extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return MessageBuilder::class;
    }

    /**
     * Forward static method calls to the MessageBuilder.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return forward_static_call([MessageBuilder::class, $method], ...$args);
    }
}
