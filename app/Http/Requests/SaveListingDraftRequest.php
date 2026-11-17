<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveListingDraftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('properties.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draft_uuid' => ['nullable', 'uuid'],
            'current_step' => ['required', 'integer', 'between:1,3'],
            'data' => [
                'required',
                'array:property_type,building_type,land_type,transaction_type,rent_period,title,description,price,negotiable,land_area_sqm,building_area_sqm,bedrooms,bathrooms,floors,condition,province,province_code,city,city_code,district,district_code,postal_code,address,latitude,longitude,front_width,land_contour,road_access,zone_type,url_maps',
            ],
            'data.property_type' => ['nullable', 'string', Rule::in(['bangunan', 'tanah'])],
            'data.building_type' => ['nullable', 'string', 'max:100'],
            'data.land_type' => ['nullable', 'string', 'max:100'],
            'data.transaction_type' => ['nullable', 'string', Rule::in(['dijual', 'disewakan'])],
            'data.rent_period' => ['nullable', 'string', Rule::in(['bulan', 'tahun'])],
            'data.title' => ['nullable', 'string', 'max:255'],
            'data.description' => ['nullable', 'string', 'max:2000'],
            'data.price' => ['nullable', 'numeric', 'min:0', 'max:999999999999999'],
            'data.negotiable' => ['nullable', 'boolean'],
            'data.land_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'data.building_area_sqm' => ['nullable', 'numeric', 'min:0'],
            'data.bedrooms' => ['nullable', 'numeric', 'min:0'],
            'data.bathrooms' => ['nullable', 'numeric', 'min:0'],
            'data.floors' => ['nullable', 'numeric', 'min:0'],
            'data.condition' => ['nullable', 'string', 'max:100'],
            'data.province' => ['nullable', 'string', 'max:100'],
            'data.province_code' => ['nullable', 'string', 'max:16'],
            'data.city' => ['nullable', 'string', 'max:100'],
            'data.city_code' => ['nullable', 'string', 'max:16'],
            'data.district' => ['nullable', 'string', 'max:100'],
            'data.district_code' => ['nullable', 'string', 'max:16'],
            'data.postal_code' => ['nullable', 'string', 'max:20'],
            'data.address' => ['nullable', 'string', 'max:500'],
            'data.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'data.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'data.front_width' => ['nullable', 'numeric', 'min:0'],
            'data.land_contour' => ['nullable', 'string', 'max:100'],
            'data.road_access' => ['nullable', 'string', 'max:100'],
            'data.zone_type' => ['nullable', 'string', 'max:100'],
            'data.url_maps' => ['nullable', 'string', 'max:500'],
        ];
    }
}
