<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\Users\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RegisterController extends Controller
{
    public function __invoke(RegisterUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $avatar = $request->file('avatar');
        $avatar_path = null;

        if ($avatar instanceof UploadedFile) {
            $configured_user_directory = resolve_upload_path_placeholders(string_value(config('app.user_dir')));
            $normalized_user_directory = Str::of($configured_user_directory)
                ->replace('\\', '/')
                ->trim('/')
                ->toString();
            $public_disk_prefix = 'storage/app/public/';

            if (! Str::startsWith($normalized_user_directory, $public_disk_prefix)) {
                throw new RuntimeException('The configured user directory must be inside the public storage directory.');
            }

            $directory = Str::of(Str::after($normalized_user_directory, $public_disk_prefix))
                ->append('/images/avatar')
                ->toString();
            $avatar_path = Storage::disk('public')->putFile($directory, $avatar);

            if (! is_string($avatar_path)) {
                throw new RuntimeException('The user avatar could not be stored.');
            }
        }

        try {
            $user = DB::transaction(function () use ($validated, $avatar_path): User {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'avatar' => $avatar_path,
                ]);
                $user->assignRole('user');

                return $user;
            });
        } catch (Throwable $exception) {
            if (is_string($avatar_path)) {
                Storage::disk('public')->delete($avatar_path);
            }

            throw $exception;
        }

        event(new Registered($user));

        return response()->json(['message' => 'Account created. You can now sign in.'], 201);
    }
}
