<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => [
                'required',
                'image',
                'mimes:jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=6000,max_height=6000',
            ],
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
            'photos.*.required' => 'Setiap foto wajib berupa gambar.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Format yang didukung: JPEG, PNG, WebP.',
            'photos.*.extensions' => 'Ekstensi foto yang didukung: JPG, JPEG, PNG, WebP.',
            'photos.*.max' => 'Ukuran foto maksimal 2MB.',
            'photos.*.dimensions' => 'Dimensi foto maksimal 6000 × 6000 piksel.',
            'certificate_file.required' => 'File sertifikat wajib diunggah.',
            'certificate_file.mimes' => 'Format sertifikat yang didukung: PDF, JPEG, PNG.',
            'certificate_file.max' => 'Ukuran sertifikat maksimal 10MB.',
        ];
    }
}
