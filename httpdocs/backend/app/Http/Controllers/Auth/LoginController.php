<?php
namespace httpdocs\backend\app\Http\Controllers\Auth;
use httpdocs\backend\app\Http\Controllers\Controller;
use httpdocs\backend\app\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function App\Http\Controllers\Auth\abort;
use function App\Http\Controllers\Auth\response;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        $user = User::query()->where('email', $data['email'])->first();
        if ($user === null || $user->is_blocked || ! Hash::check($data['password'], (string) $user->password)) abort(422, 'Invalid credentials.');
        if (! $user->hasVerifiedEmail()) abort(403, 'Email verification is required.');
        return response()->json(['access_token' => $user->createToken('storefront')->accessToken, 'token_type' => 'Bearer']);
    }
}