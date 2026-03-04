<?php

namespace Creagia\LaravelWebMailer;

use Creagia\LaravelWebMailer\Commands\LaravelWebMailerCleanUpCommand;
use Creagia\LaravelWebMailer\Commands\LaravelWebMailerClearAllCommand;
use Illuminate\Mail\MailManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelWebMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-web-mailer')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasCommands([
                LaravelWebMailerClearAllCommand::class,
                LaravelWebMailerCleanUpCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->app->make(MailManager::class)
            ->extend('web', fn (array $config = []) => app(LaravelWebMailerTransport::class));
    }
}
