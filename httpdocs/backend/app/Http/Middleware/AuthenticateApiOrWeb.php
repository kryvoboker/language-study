<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateApiOrWeb
{
    /**
     * Authenticate with Passport first, then the Filament/web session.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach (['api', 'web'] as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user !== null) {
                Auth::shouldUse($guard);
                $request->setUserResolver(static fn () => $user);

                return $next($request);
            }
        }

        return response()->json([
            'code' => 'login_required',
            'message' => 'Sign in is required.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
