<?php

namespace Creagia\LaravelWebMailer;

use Carbon\Carbon;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class LaravelWebMailDto
{
    /**
     * @param array<int,string> $to
     * @param array<int,string> $replyTo
     * @param array<int,string> $cc
     * @param array<int,string> $bcc
     * @param array<int, DataPart> $attachments
     */
    public function __construct(
        public readonly string $messageId,
        public readonly string $from,
        public readonly array $to,
        public readonly array $replyTo,
        public readonly array $cc,
        public readonly array $bcc,
        public readonly string $headers,
        public readonly string $subject,
        public readonly ?string $textBody,
        public readonly ?string $htmlBody,
        public readonly Carbon $sentAt,
        public readonly array $attachments,
        public bool $isRead = false,
    ) {
    }

    public static function fromEmail(string $messageId, Email $email): self
    {
        return new LaravelWebMailDto(
            messageId: $messageId,
            from: $email->getFrom()[0]->toString(),
            to: collect($email->getTo())->map(fn (Address $address) => $address->toString())->toArray(),
            replyTo: collect($email->getReplyTo())->map(fn (Address $address) => $address->toString())->toArray(),
            cc: collect($email->getCc())->map(fn (Address $address) => $address->toString())->toArray(),
            bcc: collect($email->getBcc())->map(fn (Address $address) => $address->toString())->toArray(),
            headers: $email->getPreparedHeaders()->toString(),
            subject: $email->getSubject() ?? '',
            textBody: ($email->getTextBody()) ? (string)$email->getTextBody() : null,
            htmlBody: ($email->getHtmlBody()) ? (string)$email->getHtmlBody() : null,
            sentAt: ($email->getDate()) ? Carbon::createFromImmutable($email->getDate()) : now(),
            attachments: $email->getAttachments(),
        );
    }
}
