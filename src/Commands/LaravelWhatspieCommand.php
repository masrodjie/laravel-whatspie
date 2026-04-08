<?php

namespace MasRodjie\LaravelWhatspie\Commands;

use Illuminate\Console\Command;

class LaravelWhatspieCommand extends Command
{
    public $signature = 'laravel-whatspie';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
