<?php
namespace httpdocs\backend\app\Domain\Ai\Data;
final readonly class ProviderOperationData
{
    public function __construct(public string $id, public string $status) {}
}