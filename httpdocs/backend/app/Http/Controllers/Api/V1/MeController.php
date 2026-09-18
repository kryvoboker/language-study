<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Ai\AiProviderResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RuntimeException;

class MeController extends Controller
{
    public function __invoke(Request $request): array
    {
		$max_input_characters = (int)config('app.max_characters_for_input_translate', 500);

        try {
            $max_input_characters = app(AiProviderResolver::class)
                ->resolveFor($request->user())
                ->maxInputCharacters();
        } catch (RuntimeException) {
            // Keep the public contract usable while no provider is configured.
        }

        return [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'max_input_characters' => $max_input_characters,
        ];
    }
}