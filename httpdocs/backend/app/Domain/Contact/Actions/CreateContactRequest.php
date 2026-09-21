<?php

declare(strict_types=1);

namespace App\Domain\Contact\Actions;

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest as ContactRequestModel;
use App\Models\Users\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateContactRequest
{
    /**
     * @param  array{first_name?: string, last_name?: string, email?: string, message: string, images?: array<UploadedFile>}  $data
     */
    public function handle(array $data, ?User $user): ContactRequestModel
    {
        $stored_paths = [];

        try {
            return DB::transaction(function () use ($data, $user, &$stored_paths): ContactRequestModel {
                $first_name = $user instanceof User
                    ? (string) $user->name
                    : string_value($data['first_name'] ?? null);
                $last_name = $user instanceof User
                    ? (string) $user->lastname
                    : string_value($data['last_name'] ?? null);
                $email = $user instanceof User
                    ? (string) $user->email
                    : string_value($data['email'] ?? null);

                $contact_request = ContactRequestModel::query()->create([
                    'user_id' => $user?->getKey(),
                    'sender_first_name' => $first_name,
                    'sender_last_name' => $last_name,
                    'sender_email' => $email,
                    'sender_is_blocked' => $user instanceof User && $user->is_blocked,
                    'message' => $data['message'],
                    'status' => ContactRequestStatus::Pending,
                ]);

                foreach (array_values($data['images'] ?? []) as $position => $image) {
                    $path = Storage::disk('local')->putFile(
                        'contact-requests/' . string_value($contact_request->getKey()),
                        $image,
                    );

                    if (! is_string($path)) {
                        throw new \RuntimeException('Contact attachment could not be stored.');
                    }

                    $stored_paths[] = $path;
                    $contact_request->attachments()->create([
                        'disk' => 'local',
                        'path' => $path,
                        'mime_type' => (string) $image->getMimeType(),
                        'size_bytes' => (int) $image->getSize(),
                        'position' => $position,
                    ]);
                }

                return $contact_request;
            });
        } catch (Throwable $exception) {
            foreach ($stored_paths as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }
    }
}
