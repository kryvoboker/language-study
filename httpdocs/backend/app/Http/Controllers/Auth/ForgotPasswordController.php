<?php
namespace httpdocs\backend\app\Http\Controllers\Auth;
use httpdocs\backend\app\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use function App\Http\Controllers\Auth\response;

class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($request->only('email'));
        return response()->json(['message' => 'If the account exists, a reset link has been sent.'], 202);
    }
}