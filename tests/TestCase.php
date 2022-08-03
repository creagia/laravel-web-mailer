<?php

namespace Creagia\LaravelWebMailer\Tests;

use Creagia\LaravelWebMailer\LaravelWebMailerServiceProvider;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.mailers.web.transport', 'web');
        config()->set('mail.default', 'web');

        File::cleanDirectory(config('web-mailer.storage_path'));
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelWebMailerServiceProvider::class,
        ];
    }
}
