<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Ai\AiProviderManager;
use App\Domain\Ai\AiProviderResolver;
use App\Domain\Translation\Data\TranslationPromptData;
use App\Enums\TranslationStatus;
use App\Models\TranslationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class StartTranslationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $translation_request_id)
    {
        $this->onQueue('translations');
    }

	/**
	 * @param AiProviderResolver $resolver
	 * @param AiProviderManager  $manager
	 *
	 * @return void
	 * @throws Throwable
	 */
    public function handle(AiProviderResolver $resolver, AiProviderManager $manager): void
    {
        /** @var TranslationRequest $request */
        $request = TranslationRequest::query()->with('user')->findOrFail($this->translation_request_id);
		$user = $request->user;
        if ($request->status === TranslationStatus::Cancelled) {
            return;
        }

        $setting = $resolver->resolveFor($user);
        $request->update(['status' => TranslationStatus::Processing, 'provider_key' => $setting->key]);

        try {
            $operation = $manager->driver($setting->key)->start($setting, new TranslationPromptData(
                $request->source_text,
                $request->source_language,
                $request->target_language,
                $request->locale,
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