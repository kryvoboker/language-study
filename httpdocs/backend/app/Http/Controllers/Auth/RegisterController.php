<?php

namespace httpdocs\backend\app\Http\Controllers\Auth;

use httpdocs\backend\app\Http\Controllers\Controller;
use httpdocs\backend\app\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function App\Http\Controllers\Auth\event;
use function App\Http\Controllers\Auth\response;

class RegisterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = User::query()->create([...$validated, 'password' => Hash::make($validated['password'])]);
        $user->assignRole('user');
        event(new Registered($user));

        return response()->json(['message' => 'Account created. Please verify your email.'], 201);
    }
}