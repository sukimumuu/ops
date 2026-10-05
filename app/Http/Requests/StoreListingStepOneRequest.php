<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_type' => ['required', 'in:bangunan,tanah'],
            'building_type' => ['required_if:property_type,bangunan', 'string', 'nullable'],
            'land_type' => ['required_if:property_type,tanah', 'string', 'nullable'],
            'transaction_type' => ['required', 'in:dijual,disewakan'],

            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'negotiable' => ['boolean', 'nullable'],

            'land_area_sqm' => ['required', 'numeric', 'min:1'],
            'building_area_sqm' => ['required_if:property_type,bangunan', 'numeric', 'min:0', 'nullable'],

            'bedrooms' => ['required_if:property_type,bangunan', 'numeric', 'min:0', 'nullable'],
            'bathrooms' => ['required_if:property_type,bangunan', 'numeric', 'min:0', 'nullable'],
            'floors' => ['required_if:property_type,bangunan', 'numeric', 'min:1', 'nullable'],
            'condition' => ['required_if:property_type,bangunan', 'string', 'nullable'],

            'front_width' => ['nullable', 'numeric', 'min:0'],
            'land_contour' => ['required_if:property_type,tanah', 'string', 'nullable'],
            'road_access' => ['required_if:property_type,tanah', 'string', 'nullable'],
            'zone_type' => ['nullable', 'string'],

            'certificate_type' => ['required', 'string'],

            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'url_maps' => ['nullable', 'url', 'max:500'],
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
            'property_type.required' => 'Tipe properti wajib dipilih.',
            'building_type.required_if' => 'Jenis bangunan wajib dipilih.',
            'land_type.required_if' => 'Jenis tanah wajib dipilih.',
            'transaction_type.required' => 'Tujuan transaksi wajib dipilih.',
            'title.required' => 'Judul properti wajib diisi.',
            'description.required' => 'Deskripsi properti wajib diisi.',
            'price.required' => 'Harga properti wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'land_area_sqm.required' => 'Luas tanah wajib diisi.',
            'building_area_sqm.required_if' => 'Luas bangunan wajib diisi.',
            'bedrooms.required_if' => 'Jumlah kamar tidur wajib diisi.',
            'bathrooms.required_if' => 'Jumlah kamar mandi wajib diisi.',
            'floors.required_if' => 'Jumlah lantai wajib diisi.',
            'condition.required_if' => 'Kondisi bangunan wajib dipilih.',
            'land_contour.required_if' => 'Kontur tanah wajib dipilih.',
            'road_access.required_if' => 'Akses jalan wajib dipilih.',
            'certificate_type.required' => 'Jenis sertifikat wajib dipilih.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota/Kabupaten wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'address.required' => 'Alamat lengkap wajib diisi.',
        ];
    }
}
