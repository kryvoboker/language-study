<?php

namespace httpdocs\backend\app\Jobs;

use httpdocs\backend\app\Domain\Ai\AiProviderManager;
use httpdocs\backend\app\Domain\Ai\AiProviderResolver;
use httpdocs\backend\app\Domain\Translation\Data\TranslationPromptData;
use httpdocs\backend\app\Enums\TranslationStatus;
use httpdocs\backend\app\Jobs\PollTranslationJob;
use httpdocs\backend\app\Models\TranslationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;
use function App\Jobs\now;

class StartTranslationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $translation_request_id)
    {
        $this->onQueue('translations');
    }

    public function handle(AiProviderResolver $resolver, AiProviderManager $manager): void
    {
        $request = TranslationRequest::query()->with('user')->findOrFail($this->translation_request_id);
        if ($request->status === TranslationStatus::Cancelled) {
            return;
        }

        $setting = $resolver->resolveFor($request->user);
        $request->update(['status' => TranslationStatus::Processing, 'provider_key' => $setting->key]);

        try {
            $operation = $manager->driver($setting->key)->start($setting, new TranslationPromptData(
                $request->source_text,
                $request->source_language,
                $request->target_language,
            ));

            $request->refresh();
            $request->update(['provider_operation_id' => $operation->id]);

            if ($request->status === TranslationStatus::Cancelled) {
                $manager->driver($setting->key)->cancel($setting, $operation->id);
                return;
            }

            PollTranslationJob::dispatch($request->id)->delay(now()->addMilliseconds(350));
        } catch (Throwable $exception) {
            $request->update(['status' => TranslationStatus::Failed, 'error_message' => $exception->getMessage()]);
            throw $exception;
        }
    }
}