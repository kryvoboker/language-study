<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'github', 'facebook'];
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        return Socialite::driver($provider)->redirect();
    }
    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $social_user = Socialite::driver($provider)->user();
        $email = $social_user->getEmail();
        abort_if($email === null, 422, 'The social provider did not return an email address.');
        $user = User::query()->firstOrCreate(['email' => $email], ['name' => $social_user->getName() ?: $social_user->getNickname() ?: 'User', 'email_verified_at' => now()]);
        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        if (! $user->hasAnyRole()) {
            $user->assignRole('user');
        }
        abort_if($user->is_blocked, 403);
        $ticket = Str::random(64);
        Cache::put('social-login:' . hash('sha256', $ticket), $user->id, now()->addMinute());
        return redirect(Str::rtrim((string) config('app.frontend_url'), '/') . '/auth/social/callback?ticket=' . urlencode($ticket));
    }
}