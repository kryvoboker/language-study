<?php

namespace httpdocs\backend\app\Http\Controllers\Api\V1;

use httpdocs\backend\app\Domain\Translation\CancelTranslationAction;
use httpdocs\backend\app\Enums\TranslationStatus;
use httpdocs\backend\app\Http\Controllers\Controller;
use httpdocs\backend\app\Http\Requests\StoreTranslationRequest;
use httpdocs\backend\app\Jobs\StartTranslationJob;
use httpdocs\backend\app\Models\TranslationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function App\Http\Controllers\Api\V1\abort_unless;
use function App\Http\Controllers\Api\V1\data_get;
use function App\Http\Controllers\Api\V1\response;

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