<?php
namespace httpdocs\backend\app\Http\Controllers\Auth;
use httpdocs\backend\app\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use function App\Http\Controllers\Auth\redirect;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse { $request->fulfill(); return redirect(rtrim((string) config('app.frontend_url'), '/').'/login?verified=1'); }
}