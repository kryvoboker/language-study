<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use App\Domain\Ai\AiProviderManager;
use App\Enums\TranslationStatus;
use App\Models\AiProviderSetting;
use App\Models\TranslationRequest;
use Throwable;

final class CancelTranslationAction
{
    public function __construct(private AiProviderManager $manager)
    {
    }

    public function execute(TranslationRequest $request): void
    {
        if ($request->status->isTerminal()) {
            return;
        }

        $request->update(['status' => TranslationStatus::Cancelled, 'cancelled_at' => now()]);

        if ($request->provider_key === null || $request->provider_operation_id === null) {
            return;
        }

        $setting = AiProviderSetting::query()
            ->where('key', $request->provider_key)
            ->first();
        if ($setting === null) {
            return;
        }

        try {
            $this->manager
                ->driver($setting->key)
                ->cancel($setting, $request->provider_operation_id);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
