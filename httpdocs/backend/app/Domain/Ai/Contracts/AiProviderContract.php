<?php

namespace httpdocs\backend\app\Domain\Ai\Contracts;

use httpdocs\backend\app\Domain\Ai\Data\ProviderOperationData;
use httpdocs\backend\app\Domain\Ai\Data\ProviderResultData;
use httpdocs\backend\app\Domain\Translation\Data\TranslationPromptData;
use httpdocs\backend\app\Models\AiProviderSetting;

interface AiProviderContract
{
    public function start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData;

    public function retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData;

    public function cancel(AiProviderSetting $setting, string $operation_id): void;
}