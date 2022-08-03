<?php

namespace Creagia\LaravelWebMailer\Commands;

use Creagia\LaravelWebMailer\LaravelWebMailDto;
use Creagia\LaravelWebMailer\LaravelWebMailRepository;
use Illuminate\Console\Command;

class LaravelWebMailerClearAllCommand extends Command
{
    public $signature = 'laravel-web-mailer:clear-all';

    public $description = 'Delete all stored emails.';

    public function handle(LaravelWebMailRepository $laravelWebMailRepository): int
    {
        $this->comment('Deleting all stored emails...');

        $laravelWebMailRepository
            ->all()
            ->each(fn (LaravelWebMailDto $laravelWebMailDto) => $laravelWebMailRepository->delete($laravelWebMailDto->messageId));

        $this->comment('All done');

        return self::SUCCESS;
    }
}
