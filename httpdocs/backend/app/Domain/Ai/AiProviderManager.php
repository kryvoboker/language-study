<?php

namespace httpdocs\backend\app\Domain\Ai;

use httpdocs\backend\app\Domain\Ai\Contracts\AiProviderContract;
use InvalidArgumentException;

final class AiProviderManager
{
    /** @param array<string, AiProviderContract> $providers */
    public function __construct(private array $providers) {}

    public function driver(string $key): AiProviderContract
    {
        return $this->providers[$key] ?? throw new InvalidArgumentException("AI provider [{$key}] is not registered.");
    }
}