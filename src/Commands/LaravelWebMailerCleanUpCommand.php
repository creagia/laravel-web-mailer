<?php

namespace Creagia\LaravelWebMailer\Commands;

use Creagia\LaravelWebMailer\LaravelWebMailDto;
use Creagia\LaravelWebMailer\LaravelWebMailRepository;
use Illuminate\Console\Command;

class LaravelWebMailerCleanUpCommand extends Command
{
    public $signature = 'laravel-web-mailer:cleanup';

    public $description = 'Delete all stored emails older than N days';

    public function handle(LaravelWebMailRepository $laravelWebMailRepository): int
    {
        $numberOfDays = intval(config('web-mailer.delete_emails_older_than_days'));

        $this->comment("Deleting all stored emails older than {$numberOfDays} days...");

        $currentTime = now();

        $laravelWebMailRepository
            ->all()
            ->filter(
                fn (LaravelWebMailDto $laravelWebMailDto) => $laravelWebMailDto->sentAt->diffInDays($currentTime) > $numberOfDays
            )
            ->each(fn (LaravelWebMailDto $laravelWebMailDto) => $laravelWebMailRepository->delete($laravelWebMailDto->messageId));

        $this->comment('All done');

        return self::SUCCESS;
    }
}
