<?php

namespace MasRodjie\LaravelWhatspie;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MasRodjie\LaravelWhatspie\Commands\LaravelWhatspieCommand;

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
            ->hasViews()
            ->hasMigration('create_laravel_whatspie_table')
            ->hasCommand(LaravelWhatspieCommand::class)
            ->hasFacade('Whatspie', \MasRodjie\LaravelWhatspie\Facades\Whatspie::class);
    }
}
