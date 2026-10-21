<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtractListingDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Tempel atau tuliskan informasi properti terlebih dahulu.',
            'text.string' => 'Informasi properti harus berupa teks.',
            'text.min' => 'Tuliskan informasi properti minimal 10 karakter.',
            'text.max' => 'Informasi properti maksimal 5000 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('text'))) {
            $this->merge(['text' => trim($this->input('text'))]);
        }
    }
}
