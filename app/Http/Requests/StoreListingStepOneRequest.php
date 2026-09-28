<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingStepOneRequest extends FormRequest
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
            'type' => ['required', 'in:residential,land'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'land_area_sqm' => ['required', 'numeric', 'min:1'],
            'building_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'certificate_type' => ['required', 'in:SHM,SHGB,GIRIK,OTHER'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'type.required' => 'Tipe properti wajib dipilih.',
            'title.required' => 'Judul properti wajib diisi.',
            'description.required' => 'Deskripsi properti wajib diisi.',
            'price.required' => 'Harga properti wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'land_area_sqm.required' => 'Luas tanah wajib diisi.',
            'land_area_sqm.numeric' => 'Luas tanah harus berupa angka.',
            'certificate_type.required' => 'Tipe sertifikat wajib dipilih.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota/Kabupaten wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'address.required' => 'Alamat lengkap wajib diisi.',
        ];
    }
}
