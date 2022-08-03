<?php

use Creagia\LaravelWebMailer\LaravelWebMailDto;
use Creagia\LaravelWebMailer\Tests\TestClasses\TestMailable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;

it('has a mails index page', function () {
    get(route('laravelWebMailer.index'))
        ->assertStatus(Response::HTTP_OK)
        ->assertViewIs('web-mailer::index')
        ->assertSeeText(config('app.name'));
});


it('can fetch all mails', function () {
    Mail::send(new TestMailable());

    $response = get(route('laravelWebMailer.fetchAll'))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(1);

    expect($response->json())
        ->toBeArray()
        ->and($response->json()[0])
        ->toHaveKeys([
            'messageId',
            'subject',
            'to',
            'diffDate',
            'isRead',
            'fetchRoute',
        ]);
});

it('can fetch a mail', function () {
    Mail::send(new TestMailable());

    $response = get(route('laravelWebMailer.fetch', lastSentMail()->messageId))
        ->assertStatus(Response::HTTP_OK);

    expect($response->json())
        ->toBeArray()
        ->toHaveKeys([
            'sentAtFormatted',
            'eml',
            'attachments',
            'messageId',
            'htmlBody',
            'textBody',
            'subject',
            'isRead',
            'headers',
            'from',
            'to',
            'replyTo',
            'cc',
            'bcc',
        ]);
});

it('can delete all mails', function () {
    Mail::send(new TestMailable());

    expect(lastSentMail())->toBeInstanceOf(LaravelWebMailDto::class);

    delete(route('laravelWebMailer.destroy'))->assertStatus(Response::HTTP_OK);

    expect(lastSentMail())->toBeNull();
});
