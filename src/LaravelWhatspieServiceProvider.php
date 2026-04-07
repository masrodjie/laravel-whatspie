<?php

namespace MasRodjie\LaravelWhatspie;

use Illuminate\Support\Facades\Route;
use MasRodjie\LaravelWhatspie\Commands\LaravelWhatspieCommand;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient as WhatspieClientContract;
use MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController;
use MasRodjie\LaravelWhatspie\Http\WhatspieClient;
use MasRodjie\LaravelWhatspie\Messaging\MessageBuilder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelWhatspieServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-whatspie')
            ->hasConfigFile()
            ->hasMigration('create_laravel_whatspie_table')
            ->hasCommand(LaravelWhatspieCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind WhatspieClient with api_token and device from config
        $this->app->bind(WhatspieClientContract::class, function () {
            return new WhatspieClient(
                config('whatspie.api_token'),
                config('whatspie.device')
            );
        });

        // Bind MessageBuilder as a singleton
        $this->app->singleton(MessageBuilder::class, function () {
            return new MessageBuilder();
        });
    }

    public function packageBooted(): void
    {
        // Register webhook route when webhook.enabled is true
        if (config('whatspie.webhook.enabled', false)) {
            Route::post(config('whatspie.webhook.path', '/whatspie/webhook'), WebhookController::class)
                ->name('whatspie.webhook')
                ->middleware('api');
        }
    }
}
