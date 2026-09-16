<?php
namespace httpdocs\backend\app\Http\Controllers\Auth;
use httpdocs\backend\app\Http\Controllers\Controller;
use httpdocs\backend\app\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use function App\Http\Controllers\Auth\abort_if;
use function App\Http\Controllers\Auth\abort_unless;
use function App\Http\Controllers\Auth\now;
use function App\Http\Controllers\Auth\redirect;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'github', 'facebook'];
    public function redirect(string $provider): RedirectResponse { abort_unless(in_array($provider, self::PROVIDERS, true), 404); return Socialite::driver($provider)->redirect(); }
    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $social_user = Socialite::driver($provider)->user();
        $email = $social_user->getEmail(); abort_if($email === null, 422, 'The social provider did not return an email address.');
        $user = User::query()->firstOrCreate(['email' => $email], ['name' => $social_user->getName() ?: $social_user->getNickname() ?: 'User', 'email_verified_at' => now()]);
        if (! $user->hasVerifiedEmail()) { $user->forceFill(['email_verified_at' => now()])->save(); }
        if (! $user->hasAnyRole()) { $user->assignRole('user'); }
        abort_if($user->is_blocked, 403);
        $ticket = Str::random(64); Cache::put('social-login:'.hash('sha256', $ticket), $user->id, now()->addMinute());
        return redirect(rtrim((string) config('app.frontend_url'), '/').'/auth/social/callback?ticket='.urlencode($ticket));
    }
}