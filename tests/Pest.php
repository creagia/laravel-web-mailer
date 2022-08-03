<?php

use Creagia\LaravelWebMailer\LaravelWebMailDto;
use Creagia\LaravelWebMailer\LaravelWebMailRepository;
use Creagia\LaravelWebMailer\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function lastSentMail(): ?LaravelWebMailDto
{
    return laravelWebMailRepository()->all()->first();
}

function laravelWebMailRepository(): LaravelWebMailRepository
{
    return app(LaravelWebMailRepository::class);
}
