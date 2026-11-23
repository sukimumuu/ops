<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisbursementAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize the account number before validation so pasted numbers with
     * spaces or dashes are accepted.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('bank_account_number'))) {
            $this->merge([
                'bank_account_number' => preg_replace('/[\s-]+/', '', $this->input('bank_account_number')),
            ]);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'regex:/^\d{6,34}$/'],
            'bank_account_holder_name' => ['required', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_name.max' => 'Nama bank tidak boleh lebih dari 100 karakter.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi.',
            'bank_account_number.regex' => 'Nomor rekening harus berupa 6-34 digit angka.',
            'bank_account_holder_name.required' => 'Nama pemilik rekening wajib diisi.',
            'bank_account_holder_name.max' => 'Nama pemilik rekening tidak boleh lebih dari 150 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'bank_name' => 'nama bank',
            'bank_account_number' => 'nomor rekening',
            'bank_account_holder_name' => 'nama pemilik rekening',
        ];
    }
}
