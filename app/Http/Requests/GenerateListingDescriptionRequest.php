<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateListingDescriptionRequest extends FormRequest
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
            'property_type' => ['nullable', 'string', 'in:bangunan,tanah'],
            'building_type' => ['nullable', 'string', 'in:rumah,apartemen,ruko,villa,gudang,kantor'],
            'land_type' => ['nullable', 'string', 'in:kavling,sawah,kebun,komersial,industri'],
            'transaction_type' => ['nullable', 'string', 'in:dijual,disewakan'],
            'rent_period' => ['nullable', 'string', 'in:bulan,tahun'],

            'title' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:1000000000000000'],
            'negotiable' => ['nullable', 'boolean'],

            'land_area_sqm' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'building_area_sqm' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'front_width' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'floors' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'condition' => ['nullable', 'string', 'in:baru,siap_huni,butuh_renovasi'],
            'certificate_type' => ['nullable', 'string', 'in:SHM,SHGB,GIRIK,AJB,OTHER'],
            'land_contour' => ['nullable', 'string', 'in:datar,miring,berbukit'],
            'road_access' => ['nullable', 'string', 'in:jalan_utama,jalan_lingkungan,gang'],
            'zone_type' => ['nullable', 'string', 'in:perumahan,komersial,industri,pertanian,campuran'],

            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.numeric' => 'Harga properti harus berupa angka.',
            'bedrooms.integer' => 'Jumlah kamar tidur harus berupa angka bulat.',
            'bathrooms.integer' => 'Jumlah kamar mandi harus berupa angka bulat.',
            'floors.integer' => 'Jumlah lantai harus berupa angka bulat.',
            'certificate_type.in' => 'Jenis sertifikat tidak dikenali.',
        ];
    }

    /**
     * Ensure the AI has at least one useful fact to work with.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasUsefulData = $this->filled('title')
                || $this->filled('price')
                || $this->filled('building_type')
                || $this->filled('land_type')
                || $this->filled('address');

            if (! $hasUsefulData) {
                $validator->errors()->add(
                    'form',
                    'Isi minimal judul, harga, jenis, atau lokasi properti sebelum membuat deskripsi.',
                );
            }
        });
    }
}
