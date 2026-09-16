<?php

declare(strict_types=1);

namespace App\Domain\Ai\Data;

final readonly class ProviderOperationData
{
    public function __construct(public string $id, public string $status)
    {
    }
}
