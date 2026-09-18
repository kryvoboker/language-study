<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Translation\CancelTranslationAction;
use App\Enums\TranslationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Jobs\StartTranslationJob;
use App\Models\TranslationRequest;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationRequestController extends Controller
{
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $source_text = $request->string('source_text')->toString();
        $source_language = $request->string('source_language')->toString();
        $target_language = $request->string('target_language')->toString();
		$current_locale = $request->string('locale')->toString();
        $request_hash = TranslationRequest::generateRequestHash($source_text, $source_language, $target_language, $current_locale);
        $cached_request = TranslationRequest::query()
            ->where('request_hash', $request_hash)
            ->where('status', TranslationStatus::Completed)
            ->whereNotNull('result')
            ->latest('completed_at')
            ->first();

        if ($cached_request !== null) {
            $translation_request = TranslationRequest::query()->create([
                'user_id' => $user->id,
                'source_text' => $source_text,
                'source_language' => $source_language,
                'target_language' => $target_language,
                'request_hash' => $request_hash,
                'locale' => $current_locale,
                'status' => TranslationStatus::Completed,
                'provider_key' => $cached_request->provider_key,
                'result' => $cached_request->result,
                'completed_at' => now(),
            ]);

            return response()->json($this->payload($translation_request), Response::HTTP_OK);
        }

        $translation_request = TranslationRequest::query()->create([
            'user_id' => $user->id,
            'source_text' => $source_text,
            'source_language' => $source_language,
            'target_language' => $target_language,
            'request_hash' => $request_hash,
            'locale' => $current_locale,
            'status' => TranslationStatus::Queued,
        ]);

        StartTranslationJob::dispatch($translation_request->id);

        return response()->json($this->payload($translation_request), Response::HTTP_ACCEPTED);
    }

    public function show(Request $request, TranslationRequest $translation_request): JsonResponse
    {
        $user = $request->user();
        abort_if(!($user instanceof User) || $translation_request->user_id !== $user->id, 404);
        $fresh_request = $translation_request->fresh();
		abort_if(!($fresh_request instanceof TranslationRequest), 404);

        return response()->json($this->payload($fresh_request));
    }

    public function destroy(Request $request, TranslationRequest $translation_request, CancelTranslationAction $action): Response
    {
        $user = $request->user();
        abort_if(!($user instanceof User) || $translation_request->user_id !== $user->id, 404);
        $action->execute($translation_request);
        return response()->noContent();
    }

    /** @return array<string, mixed> */
    private function payload(TranslationRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status->value,
            'translation' => data_get($request->result, 'translation'),
            'source_corrected' => data_get($request->result, 'source_corrected'),
            'natural_version' => data_get($request->result, 'natural_version'),
            'issues' => data_get($request->result, 'issues', []),
            'error' => $request->status === TranslationStatus::Failed ? 'Translation could not be completed.' : null,
        ];
    }
}