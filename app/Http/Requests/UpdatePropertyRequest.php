<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Normalize the wizard's field names to property attributes.
     */
    protected function prepareForValidation(): void
    {
        $type = match ($this->input('property_type')) {
            'bangunan' => $this->input('building_type', $this->input('type')),
            'tanah' => $this->input('land_type', $this->input('type')),
            default => $this->input('type'),
        };

        $category = match ($this->input('transaction_type')) {
            'dijual' => 'sale',
            'disewakan' => 'rent',
            default => $this->input('category'),
        };

        $this->merge([
            'type' => $type,
            'category' => $category,
            'rooms' => $this->input('bedrooms', $this->input('rooms')),
            'price' => str_replace('.', '', (string) $this->input('price')),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            && ($this->user()?->can('update', $property) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $provinceCode = preg_quote((string) $this->input('province_code'), '/');
        $cityCode = preg_quote((string) $this->input('city_code'), '/');

        return [
            'property_type' => ['required', Rule::in(['bangunan', 'tanah'])],
            'type' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(['sale', 'rent'])],
            'rent_period' => ['nullable', 'required_if:category,rent', Rule::in(['bulan', 'tahun', 'minggu', 'hari'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'negotiable' => ['required', 'boolean'],
            'land_area_sqm' => ['required', 'numeric', 'min:1'],
            'building_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'rooms' => ['nullable', 'string', 'max:30'],
            'bathrooms' => ['nullable', 'string', 'max:30'],
            'floors' => ['nullable', 'string', 'max:30'],
            'condition' => ['nullable', 'string', 'max:100'],
            'front_width' => ['nullable', 'numeric', 'min:0'],
            'land_contour' => ['nullable', 'string', 'max:100'],
            'road_access' => ['nullable', 'string', 'max:100'],
            'zone_type' => ['nullable', 'string', 'max:100'],
            'certificate_type' => ['required', Rule::in(StoreListingStepTwoRequest::CERTIFICATE_TYPES)],
            'province' => ['required', 'string', 'max:100'],
            'province_code' => ['nullable', 'string', 'regex:/^\\d{2}$/'],
            'city' => ['required', 'string', 'max:100'],
            'city_code' => ['nullable', 'string', 'regex:/^'.$provinceCode.'\\.\\d{2}$/'],
            'district' => ['required', 'string', 'max:100'],
            'district_code' => ['nullable', 'string', 'regex:/^'.$cityCode.'\\.\\d{2}$/'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'url_maps' => ['nullable', 'url', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rent_period.required_if' => 'Periode sewa wajib dipilih untuk properti yang disewakan.',
            'title.required' => 'Judul properti wajib diisi.',
            'description.required' => 'Deskripsi properti wajib diisi.',
            'price.required' => 'Harga properti wajib diisi.',
            'land_area_sqm.required' => 'Luas tanah wajib diisi.',
            'certificate_type.required' => 'Jenis sertifikat wajib dipilih.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota/kabupaten wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'address.required' => 'Alamat lengkap wajib diisi.',
        ];
    }
}
