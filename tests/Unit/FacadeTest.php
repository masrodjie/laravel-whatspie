<?php

use MasRodjie\LaravelWhatspie\Facades\Whatspie;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;

test('facade provides access to message builder', function () {
    $builder = Whatspie::to('6281234567890');

    expect($builder)->toBeInstanceOf(MessageBuilder::class);
});
