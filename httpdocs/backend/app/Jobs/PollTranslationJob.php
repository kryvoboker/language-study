<?php

namespace httpdocs\backend\app\Jobs;

use httpdocs\backend\app\Domain\Ai\AiProviderManager;
use httpdocs\backend\app\Enums\TranslationStatus;
use httpdocs\backend\app\Models\AiProviderSetting;
use httpdocs\backend\app\Models\TranslationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use function App\Jobs\now;

class PollTranslationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 80;

    public function __construct(public string $translation_request_id)
    {
        $this->onQueue('translations');
    }

    public function handle(AiProviderManager $manager): void
    {
        $request = TranslationRequest::query()->findOrFail($this->translation_request_id);
        if ($request->status->isTerminal() || $request->provider_operation_id === null || $request->provider_key === null) {
            return;
        }

        $setting = AiProviderSetting::query()->where('key', $request->provider_key)->firstOrFail();
        $result = $manager->driver($setting->key)->retrieve($setting, $request->provider_operation_id);

        if (! $result->isTerminal()) {
            self::dispatch($request->id)->delay(now()->addMilliseconds(500));
            return;
        }

        if ($result->status === 'completed' && $result->result !== null) {
            $request->update(['status' => TranslationStatus::Completed, 'result' => $result->result, 'completed_at' => now()]);
            return;
        }

        $request->update(['status' => $result->status === 'cancelled' ? TranslationStatus::Cancelled : TranslationStatus::Failed, 'error_message' => $result->error]);
    }
}