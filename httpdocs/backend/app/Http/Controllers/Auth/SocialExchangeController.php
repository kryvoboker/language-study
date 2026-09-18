<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SocialExchangeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{ticket: string} $validated */
        $validated = $request->validate(['ticket' => ['required','string','size:64']]);
        $ticket = $validated['ticket'];
        $key = 'social-login:' . hash('sha256', $ticket);
        $user_id = Cache::pull($key);
        abort_unless(is_int($user_id) || is_string($user_id), 422, 'The social login ticket is invalid or expired.');
        /** @var User $user */
        $user = User::query()->findOrFail($user_id);
        abort_if($user->is_blocked, 403);
        return response()->json(['access_token' => $user->createToken('storefront')->accessToken, 'token_type' => 'Bearer']);
    }
}
