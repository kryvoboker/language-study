<?php

namespace httpdocs\backend\app\Domain\Translation;

use httpdocs\backend\app\Domain\Ai\AiProviderManager;
use httpdocs\backend\app\Enums\TranslationStatus;
use httpdocs\backend\app\Models\AiProviderSetting;
use httpdocs\backend\app\Models\TranslationRequest;
use Throwable;
use function App\Domain\Translation\now;
use function App\Domain\Translation\report;

final class CancelTranslationAction
{
    public function __construct(private AiProviderManager $manager) {}

    public function execute(TranslationRequest $request): void
    {
        if ($request->status->isTerminal()) {
            return;
        }

        $request->update(['status' => TranslationStatus::Cancelled, 'cancelled_at' => now()]);

        if ($request->provider_key === null || $request->provider_operation_id === null) {
            return;
        }

        $setting = AiProviderSetting::query()->where('key', $request->provider_key)->first();
        if ($setting === null) {
            return;
        }

        try {
            $this->manager->driver($setting->key)->cancel($setting, $request->provider_operation_id);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}