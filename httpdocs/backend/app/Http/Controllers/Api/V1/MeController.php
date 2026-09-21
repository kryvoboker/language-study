<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Ai\AiProviderResolver;
use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\Request;
use RuntimeException;

class MeController extends Controller
{
    /** @return array{id: int|string, name: string, email: string, is_blocked: bool, is_active: bool, max_input_characters: int} */
    public function __invoke(Request $request): array
    {
        $max_input_characters = integer_value(config('app.max_characters_for_input_translate', 12000));
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        try {
            $max_input_characters = app(AiProviderResolver::class)
                ->resolveFor($user)
                ->maxInputCharacters();
        } catch (RuntimeException) {
            // Keep the public contract usable while no provider is configured.
        }

        return [
            'id' => $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'is_blocked' => (bool) $user->is_blocked,
            'is_active' => (bool) $user->is_active,
            'max_input_characters' => $max_input_characters,
        ];
    }
}
