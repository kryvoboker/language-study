<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Ai\AiProviderResolver;
use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use RuntimeException;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
		$max_input_characters = (int)config('app.max_characters_for_input_translate', 500);

        if ($this->user() instanceof User) {
            try {
                $max_input_characters = app(AiProviderResolver::class)
                    ->resolveFor($this->user())
                    ->maxInputCharacters();
            } catch (RuntimeException) {
                // Provider availability is handled by the translation job.
            }
        }

        return [
            'source_text' => ['required', 'string', 'max:' . $max_input_characters],
            'source_language' => ['required', 'string', 'max:12'],
            'target_language' => ['required', 'string', 'max:12', 'different:source_language'],
        ];
    }
}