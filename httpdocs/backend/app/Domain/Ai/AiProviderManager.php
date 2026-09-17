<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Domain\Ai\Contracts\AiProviderContract;
use InvalidArgumentException;

final class AiProviderManager
{
    /** @param array<string, AiProviderContract> $providers */
    public function __construct(private array $providers)
    {
    }

    public function driver(string $key): AiProviderContract
    {
        $normalizedKey = strtolower(trim($key));

        return $this->providers[$normalizedKey] ?? throw new InvalidArgumentException("AI provider [$key] is not registered.");
    }
}
