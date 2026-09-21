<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Auth;

final class VerifySessionCsrfToken extends PreventRequestForgery
{
    /**
     * Passport bearer requests are protected by the bearer token.
     * Session-authenticated requests must provide a valid CSRF token.
     *
     * @param  Closure  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('api')->user() !== null || Auth::guard('web')->user() === null) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
