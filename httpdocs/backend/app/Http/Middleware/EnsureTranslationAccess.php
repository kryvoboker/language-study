<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Users\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTranslationAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return new JsonResponse([
                'code' => 'login_required',
                'message' => 'Sign in is required to use translation services.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->is_blocked) {
            return new JsonResponse([
                'code' => 'account_blocked',
                'message' => 'This account is blocked from using translation services.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $user->is_active) {
            return new JsonResponse([
                'code' => 'login_required',
                'message' => 'Sign in is required to use translation services.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
