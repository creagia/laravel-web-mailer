<?php

namespace Creagia\LaravelWebMailer\Controllers;

use Creagia\LaravelWebMailer\LaravelWebMailDto;
use Creagia\LaravelWebMailer\LaravelWebMailRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\Part\DataPart;

class LaravelWebMailController
{
    protected LaravelWebMailRepository $laravelWebMailRepository;

    public function __construct()
    {
        $this->laravelWebMailRepository = app(LaravelWebMailRepository::class);
    }

    public function index(): View
    {
        return view('web-mailer::index');
    }

    public function fetchAll(): JsonResponse
    {
        $mails = $this->laravelWebMailRepository
            ->all()
            ->map(function (LaravelWebMailDto $laravelWebMailDto) {
                return (object)[
                    'messageId' => $laravelWebMailDto->messageId,
                    'subject' => $laravelWebMailDto->subject,
                    'to' => $laravelWebMailDto->to,
                    'diffDate' => $laravelWebMailDto->sentAt->diffForHumans(null, null, true),
                    'isRead' => $laravelWebMailDto->isRead,
                    'fetchRoute' => route('laravelWebMailer.fetch', $laravelWebMailDto->messageId),
                    'hasAttachments' => count($laravelWebMailDto->attachments) > 0,
                ];
            })
            ->values()
            ->toArray();

        return response()->json($mails);
    }

    public function fetch(string $messageId): JsonResponse
    {
        $laravelWebMailDto = $this->laravelWebMailRepository->find($messageId, markAsRead: true);
        abort_unless($laravelWebMailDto instanceof LaravelWebMailDto, 404, 'Message not found');

        $attachments = collect($laravelWebMailDto->attachments)
            ->map(function (DataPart $dataPart, $index) use ($messageId) {
                return (object)[
                    'filename' => $dataPart->getFilename(),
                    'downloadRoute' => route('laravelWebMailer.downloadAttachment', [$messageId, $index]),
                ];
            })
            ->toArray();

        return response()->json((object)[
            'sentAtFormatted' => $laravelWebMailDto->sentAt->format('Y-m-d H:i'),
            'eml' => $this->laravelWebMailRepository->findEml($messageId),
            'attachments' => $attachments,
            'messageId' => $laravelWebMailDto->messageId,
            'htmlBody' => $laravelWebMailDto->htmlBody,
            'textBody' => nl2br($laravelWebMailDto->textBody ?? ''),
            'subject' => $laravelWebMailDto->subject,
            'isRead' => $laravelWebMailDto->isRead,
            'headers' => $laravelWebMailDto->headers,
            'from' => $laravelWebMailDto->from,
            'to' => $laravelWebMailDto->to,
            'replyTo' => $laravelWebMailDto->replyTo,
            'cc' => $laravelWebMailDto->cc,
            'bcc' => $laravelWebMailDto->bcc,
        ]);
    }

    public function downloadAttachment(string $messageId, int $attachmentIndex): StreamedResponse
    {
        $content = $this->laravelWebMailRepository->find($messageId);
        abort_unless($content instanceof LaravelWebMailDto, 404, 'Message not found');

        /** @var ?DataPart $attachment */
        $attachment = $content->attachments[$attachmentIndex] ?? null;
        abort_if(is_null($attachment), 404, 'Attachment not found');

        return response()->streamDownload(
            function () use ($attachment) {
                echo $attachment->getBody();
            },
            $attachment->getFilename()
        );
    }

    public function destroy(): JsonResponse
    {
        $this->laravelWebMailRepository
            ->all()
            ->each(fn (LaravelWebMailDto $laravelWebMailDto) => $this->laravelWebMailRepository->delete($laravelWebMailDto->messageId));

        return response()->json();
    }
}
