<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Users\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveOptionalApiOrWebUser
{
    /** @param Closure(Request): (Response) $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user() ?? Auth::guard('web')->user();

        if ($user instanceof User) {
            Auth::shouldUse(Auth::guard('api')->user() instanceof User ? 'api' : 'web');
        }

        $request->setUserResolver(static fn (): ?User => $user instanceof User ? $user : null);

        return $next($request);
    }
}
