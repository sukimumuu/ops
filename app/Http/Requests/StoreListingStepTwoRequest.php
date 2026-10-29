<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingStepTwoRequest extends FormRequest
{
    /**
     * Certificate types supported by the listing wizard.
     *
     * @var list<string>
     */
    public const CERTIFICATE_TYPES = ['SHM', 'SHGB', 'GIRIK', 'AJB', 'OTHER'];

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
        $documentRules = ['file', 'mimes:pdf,jpeg,jpg,png', 'extensions:pdf,jpg,jpeg,png', 'max:10240'];

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

            'jenis_sertifikat' => ['required', 'string', Rule::in(self::CERTIFICATE_TYPES)],
            'certificate_document' => ['required', ...$documentRules],

            'additional_certificates' => ['nullable', 'array', 'max:4'],
            'additional_certificates.*.jenis_sertifikat' => ['required', 'string', Rule::in(self::CERTIFICATE_TYPES)],
            'additional_certificates.*.certificate_document' => ['required', ...$documentRules],

            'imb_document' => ['nullable', ...$documentRules],
            'pbb_document' => ['nullable', ...$documentRules],
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

            'jenis_sertifikat.required' => 'Jenis surat wajib dipilih.',
            'jenis_sertifikat.in' => 'Jenis surat tidak dikenali.',
            'certificate_document.required' => 'File sertifikat wajib diunggah.',
            'certificate_document.mimes' => 'Format sertifikat yang didukung: PDF, JPG, JPEG, PNG.',
            'certificate_document.extensions' => 'Ekstensi sertifikat yang didukung: PDF, JPG, JPEG, PNG.',
            'certificate_document.max' => 'Ukuran sertifikat maksimal 10MB.',

            'additional_certificates.array' => 'Data sertifikat tambahan tidak valid.',
            'additional_certificates.max' => 'Maksimal 4 sertifikat tambahan.',
            'additional_certificates.*.jenis_sertifikat.required' => 'Jenis surat sertifikat tambahan wajib dipilih.',
            'additional_certificates.*.jenis_sertifikat.in' => 'Jenis surat sertifikat tambahan tidak dikenali.',
            'additional_certificates.*.certificate_document.required' => 'File sertifikat tambahan wajib diunggah.',
            'additional_certificates.*.certificate_document.mimes' => 'Format sertifikat tambahan yang didukung: PDF, JPG, JPEG, PNG.',
            'additional_certificates.*.certificate_document.extensions' => 'Ekstensi sertifikat tambahan yang didukung: PDF, JPG, JPEG, PNG.',
            'additional_certificates.*.certificate_document.max' => 'Ukuran sertifikat tambahan maksimal 10MB.',

            'imb_document.mimes' => 'Format IMB / PBG yang didukung: PDF, JPG, JPEG, PNG.',
            'imb_document.extensions' => 'Ekstensi IMB / PBG yang didukung: PDF, JPG, JPEG, PNG.',
            'imb_document.max' => 'Ukuran IMB / PBG maksimal 10MB.',
            'pbb_document.mimes' => 'Format SPPT dan STTS PBB yang didukung: PDF, JPG, JPEG, PNG.',
            'pbb_document.extensions' => 'Ekstensi SPPT dan STTS PBB yang didukung: PDF, JPG, JPEG, PNG.',
            'pbb_document.max' => 'Ukuran SPPT dan STTS PBB maksimal 10MB.',
        ];
    }
}
