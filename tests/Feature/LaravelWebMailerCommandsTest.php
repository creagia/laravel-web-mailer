<?php

use Carbon\Carbon;
use Creagia\LaravelWebMailer\Tests\TestClasses\TestMailable;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Mail::send(new TestMailable());
});

it('can delete all stored emails', function () {
    expect(laravelWebMailRepository()->all())->toHaveCount(1);

    artisan('laravel-web-mailer:clear-all');

    expect(laravelWebMailRepository()->all())->toHaveCount(0);
});

it('can delete stored emails older than 7 days', function () {
    expect(laravelWebMailRepository()->all())->toHaveCount(1);

    config()->set('web-mailer.delete_emails_older_than_days', 7);

    // Send an email with an old fake date
    $oldDate = Carbon::now()->subDays(8);
    Carbon::setTestNow($oldDate); // set the mock
    Mail::send(new TestMailable()); // send an email. It will store the send at date with $oldDate
    Carbon::setTestNow(); // clear the mock

    expect(laravelWebMailRepository()->all())->toHaveCount(2);

    artisan('laravel-web-mailer:cleanup');

    expect(laravelWebMailRepository()->all())->toHaveCount(1);
});
