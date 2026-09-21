<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Contact\Actions\CreateContactRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Jobs\SendContactRequestToTelegram;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactRequestController extends Controller
{
    public function __invoke(StoreContactRequest $request, CreateContactRequest $create_contact_request): JsonResponse
    {
        try {
            $user = $request->user();
            /** @var array{first_name?: string, last_name?: string, email?: string, message: string, images?: array<\Illuminate\Http\UploadedFile>} $validated_data */
            $validated_data = $request->validated();
            $contact_request = $create_contact_request->handle(
                $validated_data,
                $user instanceof User ? $user : null,
            );
        } catch (Throwable $exception) {
            Log::channel('stack')->error('Contact request could not be persisted.', [
                'exception_class' => $exception::class,
            ]);

            return response()->json([
                'code' => 'contact_submission_failed',
                'message' => 'We could not accept your request. Please try again.',
            ], 500);
        }

        try {
            SendContactRequestToTelegram::dispatch(string_value($contact_request->getKey()))
                ->onQueue('translations')
                ->afterCommit();
        } catch (Throwable $exception) {
            Log::channel('stack')->error('Contact request Telegram delivery could not be queued.', [
                'contact_request_id' => $contact_request->getKey(),
                'exception_class' => $exception::class,
            ]);
        }

        return response()->json([
            'data' => ['id' => $contact_request->getKey()],
            'message' => 'Your message has been accepted.',
        ], 202);
    }
}
