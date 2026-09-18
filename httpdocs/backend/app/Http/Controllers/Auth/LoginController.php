<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{email: string, password: string} $data */
        $data = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        $user = User::query()->where('email', $data['email'])->first();
        if ($user === null || $user->is_blocked || ! Hash::check($data['password'], (string) $user->password)) {
            abort(422, 'Invalid credentials.');
        }
        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Email verification is required.');
        }
        return response()->json(['access_token' => $user->createToken('storefront')->accessToken, 'token_type' => 'Bearer']);
    }
}
