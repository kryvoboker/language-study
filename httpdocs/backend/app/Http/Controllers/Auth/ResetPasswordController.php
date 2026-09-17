<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
	public function __invoke(Request $request): JsonResponse
	{
		$data   = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', 'min:10']]);
		$status = Password::reset($data, function (User $user, string $password): void {
			$user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
			event(new PasswordReset($user));
		});
		abort_if($status !== Password::PasswordReset, 422, __($status));
		return response()->json(['message' => __($status)]);
	}
}