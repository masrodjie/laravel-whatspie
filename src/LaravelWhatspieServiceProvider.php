<?php

namespace MasRodjie\LaravelWhatspie;

use Illuminate\Support\Facades\Route;
use MasRodjie\LaravelWhatspie\Commands\LaravelWhatspieCommand;
use MasRodjie\LaravelWhatspie\Contracts\WhatspieClient as WhatspieClientContract;
use MasRodjie\LaravelWhatspie\Http\Controllers\WebhookController;
use MasRodjie\LaravelWhatspie\Http\WhatspieClient;
use MasRodjie\LaravelWhatspie\Messaging\FileUploader;
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
            ->hasCommand(LaravelWhatspieCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind WhatspieClient with api_token, device, and base_url from config
        $this->app->bind(WhatspieClientContract::class, function () {
            return new WhatspieClient(
                config('whatspie.api_token'),
                config('whatspie.device'),
                config('whatspie.base_url')
            );
        });

        // Bind FileUploader as a singleton with storage configuration
        $this->app->singleton(FileUploader::class, function () {
            return new FileUploader(
                config('whatspie.storage.disk', 'public'),
                config('whatspie.storage.path', 'whatspie')
            );
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
