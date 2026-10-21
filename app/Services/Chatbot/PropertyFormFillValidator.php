<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Filters, normalizes, and validates the arguments of the
 * `fill_property_form` AI tool against the listing wizard form.
 *
 * Only whitelisted fields that exist in `resources/views/listing-wizard/create.blade.php`
 * can be returned. Invalid or unknown values are dropped instead of being applied.
 */
class PropertyFormFillValidator
{
    /**
     * Accepted values for every categorical listing wizard field.
     *
     * @var array<string, list<string>>
     */
    private const ENUMS = [
        'property_type' => ['bangunan', 'tanah'],
        'building_type' => ['rumah', 'apartemen', 'ruko', 'villa', 'gudang', 'kantor'],
        'land_type' => ['kavling', 'sawah', 'kebun', 'komersial', 'industri'],
        'transaction_type' => ['dijual', 'disewakan'],
        'rent_period' => ['bulan', 'tahun'],
        'condition' => ['baru', 'siap_huni', 'butuh_renovasi'],
        'certificate_type' => ['SHM', 'SHGB', 'GIRIK', 'AJB', 'OTHER'],
        'land_contour' => ['datar', 'miring', 'berbukit'],
        'road_access' => ['jalan_utama', 'jalan_lingkungan', 'gang'],
        'zone_type' => ['perumahan', 'komersial', 'industri', 'pertanian', 'campuran'],
    ];

    /**
     * Safe aliases the model may produce instead of the exact option value.
     *
     * @var array<string, array<string, string>>
     */
    private const ENUM_ALIASES = [
        'property_type' => [
            'building' => 'bangunan',
            'rumah' => 'bangunan',
            'apartemen' => 'bangunan',
            'ruko' => 'bangunan',
            'villa' => 'bangunan',
            'gudang' => 'bangunan',
            'kantor' => 'bangunan',
            'land' => 'tanah',
            'kavling' => 'tanah',
            'sawah' => 'tanah',
            'kebun' => 'tanah',
        ],
        'building_type' => [
            'house' => 'rumah',
            'apartment' => 'apartemen',
            'shophouse' => 'ruko',
            'warehouse' => 'gudang',
            'office' => 'kantor',
        ],
        'transaction_type' => [
            'jual' => 'dijual',
            'sale' => 'dijual',
            'for sale' => 'dijual',
            'sewa' => 'disewakan',
            'rent' => 'disewakan',
            'for rent' => 'disewakan',
        ],
        'rent_period' => [
            'monthly' => 'bulan',
            'yearly' => 'tahun',
        ],
        'condition' => [
            'new' => 'baru',
            'ready' => 'siap_huni',
            'renovation' => 'butuh_renovasi',
            'siap huni' => 'siap_huni',
            'butuh renovasi' => 'butuh_renovasi',
        ],
        'certificate_type' => [
            'HGB' => 'SHGB',
            'HAK MILIK' => 'SHM',
            'HAK GUNA BANGUNAN' => 'SHGB',
            'LAINNYA' => 'OTHER',
        ],
        'road_access' => [
            'jalan utama' => 'jalan_utama',
            'jalan lingkungan' => 'jalan_lingkungan',
        ],
    ];

    /**
     * @var list<string>
     */
    private const INTEGER_FIELDS = ['price', 'bedrooms', 'bathrooms', 'floors'];

    /**
     * @var list<string>
     */
    private const NUMERIC_FIELDS = [
        'price',
        'land_area_sqm',
        'building_area_sqm',
        'bedrooms',
        'bathrooms',
        'floors',
        'front_width',
    ];

    /**
     * Validate AI arguments and return only usable form values.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function validate(array $arguments): array
    {
        $validated = [];

        foreach ($this->rules() as $field => $rules) {
            if (! array_key_exists($field, $arguments)) {
                continue;
            }

            $value = $this->normalize($field, $arguments[$field]);

            if ($value === null) {
                continue;
            }

            $validator = Validator::make([$field => $value], [$field => $rules]);

            if ($validator->fails()) {
                continue;
            }

            $validated[$field] = $validator->validated()[$field];
        }

        return $this->pruneIrrelevantFields($validated);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'property_type' => ['string', Rule::in(self::ENUMS['property_type'])],
            'building_type' => ['string', Rule::in(self::ENUMS['building_type'])],
            'land_type' => ['string', Rule::in(self::ENUMS['land_type'])],
            'transaction_type' => ['string', Rule::in(self::ENUMS['transaction_type'])],
            'rent_period' => ['string', Rule::in(self::ENUMS['rent_period'])],
            'title' => ['string', 'max:255'],
            'description' => ['string', 'max:2000'],
            'price' => ['numeric', 'min:0'],
            'negotiable' => ['boolean'],
            'land_area_sqm' => ['numeric', 'min:0'],
            'building_area_sqm' => ['numeric', 'min:0'],
            'bedrooms' => ['integer', 'min:0'],
            'bathrooms' => ['integer', 'min:0'],
            'floors' => ['integer', 'min:1'],
            'condition' => ['string', Rule::in(self::ENUMS['condition'])],
            'certificate_type' => ['string', Rule::in(self::ENUMS['certificate_type'])],
            'address' => ['string', 'max:500'],
            'front_width' => ['numeric', 'min:0'],
            'land_contour' => ['string', Rule::in(self::ENUMS['land_contour'])],
            'road_access' => ['string', Rule::in(self::ENUMS['road_access'])],
            'zone_type' => ['string', Rule::in(self::ENUMS['zone_type'])],
        ];
    }

    private function normalize(string $field, mixed $value): mixed
    {
        if (in_array($field, self::NUMERIC_FIELDS, true)) {
            return $this->normalizeNumber($field, $value);
        }

        if ($field === 'negotiable') {
            return $this->normalizeBoolean($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (isset(self::ENUMS[$field])) {
            return $this->normalizeEnum($field, $value);
        }

        return $value;
    }

    private function normalizeEnum(string $field, string $value): ?string
    {
        $candidate = $field === 'certificate_type'
            ? mb_strtoupper($value)
            : mb_strtolower($value);

        if (in_array($candidate, self::ENUMS[$field], true)) {
            return $candidate;
        }

        $alias = self::ENUM_ALIASES[$field][$candidate] ?? null;

        return $alias !== null && in_array($alias, self::ENUMS[$field], true) ? $alias : null;
    }

    private function normalizeNumber(string $field, mixed $value): int|float|null
    {
        if (is_int($value) || is_float($value)) {
            $number = (float) $value;
        } elseif (is_string($value)) {
            $number = $field === 'price'
                ? $this->parseRupiah($value)
                : $this->parseDecimal($value);
        } else {
            return null;
        }

        if ($number === null || ! is_finite($number) || $number < 0) {
            return null;
        }

        return in_array($field, self::INTEGER_FIELDS, true)
            ? (int) round($number)
            : round($number, 2);
    }

    /**
     * Normalize prices such as "1.800.000.000", "1,8 miliar", or "750 juta".
     */
    private function parseRupiah(string $value): ?float
    {
        $value = mb_strtolower(trim($value));

        if (! preg_match('/(\d[\d.,]*)\s*(miliar|milyar|juta|jt|ribu|rb|m|j)?/u', $value, $matches)) {
            return null;
        }

        $number = $this->parseDecimal($matches[1]);

        if ($number === null) {
            return null;
        }

        $multiplier = match ($matches[2] ?? '') {
            'miliar', 'milyar', 'm' => 1_000_000_000,
            'juta', 'jt' => 1_000_000,
            'ribu', 'rb', 'j' => 1_000,
            default => 1,
        };

        return $number * $multiplier;
    }

    private function parseDecimal(string $value): ?float
    {
        $value = trim($value);

        if (! preg_match('/\d[\d.,]*/', $value, $matches)) {
            return null;
        }

        $value = $matches[0];

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '.') && str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/[.,]\d{1,2}$/', $value) && ! preg_match('/[.,]\d{3}/', $value)) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace([',', '.'], '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        return is_string($value)
            ? filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
    }

    /**
     * Remove fields that do not apply to the selected property type or transaction.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function pruneIrrelevantFields(array $validated): array
    {
        if (! isset($validated['property_type'])) {
            if (isset($validated['building_type'])) {
                $validated['property_type'] = 'bangunan';
            } elseif (isset($validated['land_type'])) {
                $validated['property_type'] = 'tanah';
            }
        }

        if (! isset($validated['transaction_type']) && isset($validated['rent_period'])) {
            $validated['transaction_type'] = 'disewakan';
        }

        $buildingOnly = ['building_type', 'building_area_sqm', 'bedrooms', 'bathrooms', 'floors', 'condition'];
        $landOnly = ['land_type', 'front_width', 'land_contour', 'road_access', 'zone_type'];

        if (($validated['property_type'] ?? null) === 'bangunan') {
            foreach ($landOnly as $field) {
                unset($validated[$field]);
            }
        }

        if (($validated['property_type'] ?? null) === 'tanah') {
            foreach ($buildingOnly as $field) {
                unset($validated[$field]);
            }
        }

        if (($validated['transaction_type'] ?? null) !== 'disewakan') {
            unset($validated['rent_period']);
        }

        return $validated;
    }
}
