<?php

declare(strict_types=1);

namespace App\Domain\Ai\Contracts;

use App\Domain\Ai\Data\ProviderOperationData;
use App\Domain\Ai\Data\ProviderResultData;
use App\Domain\Translation\Data\TranslationPromptData;
use App\Models\AiProviderSetting;

interface AiProviderContract
{
    public function start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData;

    public function retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData;

    public function cancel(AiProviderSetting $setting, string $operation_id): void;
}
