<?php

namespace httpdocs\backend\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'source_text' => ['required', 'string', 'max:12000'],
            'source_language' => ['required', 'string', 'max:12'],
            'target_language' => ['required', 'string', 'max:12', 'different:source_language'],
        ];
    }
}