<?php

declare(strict_types=1);

namespace App\Domain\Ai\Contracts;

use App\Domain\Ai\Data\ProviderOperationData;
use App\Domain\Ai\Data\ProviderResultData;
use App\Domain\Translation\Data\TranslationPromptData;
use App\Models\AiProviderSetting;

interface AiProviderContract
{
    /**
     * @param AiProviderSetting     $setting
     * @param TranslationPromptData $data
     *
     * @return ProviderOperationData
     */
    public function start(AiProviderSetting $setting, TranslationPromptData $data): ProviderOperationData;

    /**
     * @param AiProviderSetting $setting
     * @param string            $operation_id
     *
     * @return ProviderResultData
     */
    public function retrieve(AiProviderSetting $setting, string $operation_id): ProviderResultData;

    /**
     * @param AiProviderSetting $setting
     * @param string            $operation_id
     *
     * @return void
     */
    public function cancel(AiProviderSetting $setting, string $operation_id): void;
}
