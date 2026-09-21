<?php

declare(strict_types=1);

namespace App\Http\Requests\Contact;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $is_guest = $this->user() === null;

        return [
            'first_name' => [$is_guest ? 'required' : 'prohibited', 'string', 'max:120'],
            'last_name' => [$is_guest ? 'required' : 'prohibited', 'string', 'max:120'],
            'email' => [$is_guest ? 'required' : 'prohibited', 'string', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['file', File::image()->types(['jpg', 'jpeg', 'png'])->max(5120)],
        ];
    }
}
