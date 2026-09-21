<?php

declare(strict_types=1);

namespace App\Domain\Contact\Contracts;

interface ContactTelegramGateway
{
    public function isConfigured(): bool;

    public function sendMessage(string $html): void;

    /** @param list<string> $paths */
    public function sendPhotos(array $paths): void;
}
