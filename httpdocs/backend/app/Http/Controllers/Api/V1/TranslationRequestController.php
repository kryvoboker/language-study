<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Translation\CancelTranslationAction;
use App\Enums\TranslationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranslationRequest;
use App\Jobs\StartTranslationJob;
use App\Models\TranslationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationRequestController extends Controller
{
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation_request = TranslationRequest::query()->create([
            'user_id' => $request->user()->id,
            'source_text' => $request->string('source_text')->toString(),
            'source_language' => $request->string('source_language')->toString(),
            'target_language' => $request->string('target_language')->toString(),
            'status' => TranslationStatus::Queued,
        ]);

        StartTranslationJob::dispatch($translation_request->id);

        return response()->json($this->payload($translation_request), Response::HTTP_ACCEPTED);
    }

    public function show(Request $request, TranslationRequest $translation_request): JsonResponse
    {
        abort_unless($translation_request->user_id === $request->user()->id, 404);
        return response()->json($this->payload($translation_request->fresh()));
    }

    public function destroy(Request $request, TranslationRequest $translation_request, CancelTranslationAction $action): Response
    {
        abort_unless($translation_request->user_id === $request->user()->id, 404);
        $action->execute($translation_request);
        return response()->noContent();
    }

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
