<?php

use Carbon\Carbon;
use Creagia\LaravelWebMailer\Tests\TestClasses\TestMailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Part\DataPart;

beforeEach(function () {
    Mail::send(new TestMailable());
});

it('can get mails', function () {
    expect(laravelWebMailRepository()->all())->toHaveCount(1);
});

it('can assert the from address', function () {
    expect(lastSentMail()->from)
        ->toBeString()
        ->toContain('ringo@example.com')
        ->toContain('Ringo')
        ->toBe('"Ringo" <ringo@example.com>');
});

it('can assert the to addresses', function () {
    expect(lastSentMail()->to)
        ->toBeArray()
        ->toMatchArray([
            'john@example.com',
            'john1@example.com',
        ]);
});

it('can assert the reply-to addresses', function () {
    expect(lastSentMail()->replyTo)
        ->toBeArray()
        ->toMatchArray([
            'support@example.com',
        ]);
});

it('can assert the cc addresses', function () {
    expect(lastSentMail()->cc)
        ->toBeArray()
        ->toMatchArray([
            'paul@example.com',
            'paul1@example.com',
        ]);
});

it('can assert the bcc addresses', function () {
    expect(lastSentMail()->bcc)
        ->toBeArray()
        ->toMatchArray([
            'george@example.com',
        ]);
});

test('headers are present', function () {
    expect(lastSentMail()->headers)
        ->toContain('From: Ringo <ringo@example.com>')
        ->toContain('Subject: this is the subject');
});

it('can assert the subject', function () {
    expect(lastSentMail()->subject)
        ->toBeString()
        ->toBe('this is the subject');
});

it('can assert the html body', function () {
    expect(lastSentMail()->htmlBody)
        ->toBeString()
        ->toBe('<h1>title</h1><p>this is the html</p>');
});

test('sent at date is today', function () {
    expect(lastSentMail()->sentAt)
        ->toBeInstanceOf(Carbon::class)
        ->and(lastSentMail()->sentAt->isToday())
        ->toBeTrue();
});

it('has attachments', function () {
    expect(lastSentMail()->attachments)
        ->toBeArray()
        ->toHaveCount(1)
        ->and(lastSentMail()->attachments[0])
        ->toBeInstanceOf(DataPart::class)
        ->and(lastSentMail()->attachments[0]->getFilename())
        ->toBe('dummy.text');
});

it('can be found and mark as read', function () {
    $messageId = lastSentMail()->messageId;
    expect(lastSentMail()->isRead)->toBeFalse();
    laravelWebMailRepository()->find($messageId, markAsRead: true);
    expect(lastSentMail()->isRead)->toBeTrue();
});

it('can be found without mark as read', function () {
    $messageId = lastSentMail()->messageId;
    expect(lastSentMail()->isRead)->toBeFalse();
    laravelWebMailRepository()->find($messageId, markAsRead: false);
    expect(lastSentMail()->isRead)->toBeFalse();
});
