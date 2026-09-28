<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingStepTwoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'certificate_file' => ['required', 'file', 'mimes:pdf,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photos.required' => 'Minimal 1 foto properti wajib diunggah.',
            'photos.min' => 'Minimal 1 foto properti wajib diunggah.',
            'photos.max' => 'Maksimal 10 foto yang dapat diunggah.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Format yang didukung: JPEG, PNG, WebP.',
            'photos.*.max' => 'Ukuran foto maksimal 5MB.',
            'certificate_file.required' => 'File sertifikat wajib diunggah.',
            'certificate_file.mimes' => 'Format sertifikat yang didukung: PDF, JPEG, PNG.',
            'certificate_file.max' => 'Ukuran sertifikat maksimal 10MB.',
        ];
    }
}
