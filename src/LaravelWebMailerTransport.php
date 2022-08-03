<?php

namespace Creagia\LaravelWebMailer;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;

class LaravelWebMailerTransport extends AbstractTransport
{
    public function __construct(
        private readonly LaravelWebMailRepository $laravelWebMailRepository
    ) {
        parent::__construct();
    }

    public function doSend(SentMessage $message): void
    {
        /** @var Message $originalMessage */
        $originalMessage = $message->getOriginalMessage();
        $email = MessageConverter::toEmail($originalMessage);

        $laravelWebMailDto = LaravelWebMailDto::fromEmail($message->getMessageId(), $email);

        $this->laravelWebMailRepository->store(
            sentMessage: $message,
            laravelWebMailDto: $laravelWebMailDto,
        );
    }

    public function __toString(): string
    {
        return 'web';
    }
}
