<?php

namespace Creagia\LaravelWebMailer;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Mailer\SentMessage;

class LaravelWebMailRepository
{
    public function __construct(
        protected Filesystem $filesystem
    ) {
    }

    public function store(SentMessage $sentMessage, LaravelWebMailDto $laravelWebMailDto): void
    {
        $this->ensureEmailsDirectoryExists();

        $this->filesystem->put(
            self::serializedEmailPath($laravelWebMailDto->messageId),
            serialize($laravelWebMailDto)
        );

        $this->filesystem->put(
            self::emlPath($laravelWebMailDto->messageId),
            $sentMessage->toString()
        );
    }

    public function delete(string $messageId): void
    {
        $this->filesystem->delete(self::serializedEmailPath($messageId));
        $this->filesystem->delete(self::emlPath($messageId));
    }

    /**
     * @return Collection<int, LaravelWebMailDto>
     */
    public function all(): Collection
    {
        $filenamePattern = config('web-mailer.storage_path') . DIRECTORY_SEPARATOR . '*.serialized';

        return collect($this->filesystem->glob($filenamePattern))
            ->map(fn (string $filePath) => unserialize($this->filesystem->get($filePath)))
            ->sortByDesc(fn (LaravelWebMailDto $laravelWebMailDto) => $laravelWebMailDto->sentAt);
    }

    public function find(string $messageId, bool $markAsRead = false): ?LaravelWebMailDto
    {
        /** @var LaravelWebMailDto $laravelWebMailDto */
        $laravelWebMailDto = unserialize(
            $this->filesystem->get(self::serializedEmailPath($messageId))
        );

        if ($markAsRead) {
            $laravelWebMailDto = $this->markEmailAsRead($laravelWebMailDto);
        }

        return $laravelWebMailDto;
    }

    public function findEml(string $messageId): ?string
    {
        try {
            return $this->filesystem->get(self::emlPath($messageId));
        } catch (FileNotFoundException) {
            return null;
        }
    }

    private function ensureEmailsDirectoryExists(): void
    {
        $this->filesystem->ensureDirectoryExists(config('web-mailer.storage_path'));

        $this->filesystem->put(
            config('web-mailer.storage_path') . DIRECTORY_SEPARATOR . '.gitignore',
            '*' . PHP_EOL . '!.gitignore'
        );
    }

    private static function serializedEmailPath(string $messageId): string
    {
        return config('web-mailer.storage_path') . DIRECTORY_SEPARATOR . "{$messageId}.serialized";
    }

    private static function emlPath(string $messageId): string
    {
        return config('web-mailer.storage_path') . DIRECTORY_SEPARATOR . "{$messageId}.eml";
    }

    private function markEmailAsRead(LaravelWebMailDto $laravelWebMailDto): LaravelWebMailDto
    {
        if ($laravelWebMailDto->isRead) {
            return $laravelWebMailDto;
        }

        $laravelWebMailDto->isRead = true;
        $this->filesystem->put(
            self::serializedEmailPath($laravelWebMailDto->messageId),
            serialize($laravelWebMailDto)
        );

        return $laravelWebMailDto;
    }
}
