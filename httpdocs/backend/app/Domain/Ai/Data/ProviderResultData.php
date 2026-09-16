<?php

declare(strict_types=1);

namespace App\Domain\Ai\Data;

final readonly class ProviderResultData
{
    public function __construct(public string $status, public ?array $result = null, public ?string $error = null)
    {
    }
    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled', 'incomplete'], true);
    }
}
