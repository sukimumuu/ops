<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function home(): View
    {
        $propertiesUnavailable = false;

        try {
            $properties = Property::query()
                ->where('status', 'published')
                ->with(['photos' => fn ($query) => $query->limit(1)])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(4)
                ->get();

            $properties->each(function (Property $property): void {
                $photo = $property->photos->first();

                $property->setAttribute(
                    'image_url',
                    $photo ? Storage::disk('local')->temporaryUrl($photo->file_path, now()->addHour()) : null,
                );
            });
        } catch (QueryException $exception) {
            report($exception);

            $properties = collect();
            $propertiesUnavailable = true;
        }

        return view('welcome', compact('properties', 'propertiesUnavailable'));
    }

    public function property(): View
    {
        $propertiesUnavailable = false;

        try {
            $properties = Property::query()
                ->where('status', 'published')
                ->with(['photos' => fn ($query) => $query->limit(1)])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(6)
                ->withQueryString();

            $properties->getCollection()->each(function (Property $property): void {
                $photo = $property->photos->first();
                $area = $property->building_area_sqm ?? $property->land_area_sqm;
                $specs = [];

                if ($property->rooms) {
                    $specs[] = "{$property->rooms} KT";
                }

                if ($property->bathrooms) {
                    $specs[] = "{$property->bathrooms} KM";
                }

                $specs[] = number_format((float) $area, 0, ',', '.').' m²';

                $property->setAttribute('listing_specs', implode(' • ', $specs));
                $property->setAttribute(
                    'image_url',
                    $photo ? Storage::disk('local')->temporaryUrl($photo->file_path, now()->addHour()) : null,
                );
            });
        } catch (QueryException $exception) {
            report($exception);

            $properties = new LengthAwarePaginator(
                [],
                0,
                6,
                1,
                [
                    'path' => route('properties'),
                    'query' => request()->query(),
                ],
            );
            $propertiesUnavailable = true;
        }

        return view('properties', compact('properties', 'propertiesUnavailable'));
    }

    public function propertyDetails(string $uuid): View
    {
        $property = Property::query()
            ->where('uuid', $uuid)
            ->where('status', 'published')
            ->with(['photos', 'seller'])
            ->firstOrFail();

        $property->setAttribute(
            'photo_urls',
            $property->photos
                ->map(fn ($photo): string => Storage::disk('local')->temporaryUrl($photo->file_path, now()->addHour()))
                ->all(),
        );

        $specifications = array_filter([
            'Tipe' => ucfirst($property->type),
            'Luas Tanah' => number_format((float) $property->land_area_sqm, 0, ',', '.').' m²',
            'Luas Bangunan' => $property->building_area_sqm
                ? number_format((float) $property->building_area_sqm, 0, ',', '.').' m²'
                : null,
            'Sertifikat' => $property->certificate_type,
            'Kamar Tidur' => $property->rooms,
            'Kamar Mandi' => $property->bathrooms,
            'Lantai' => $property->floors,
        ], fn (?string $value): bool => filled($value));

        $relatedProperties = Property::query()
            ->where('status', 'published')
            ->whereKeyNot($property->getKey())
            ->with(['photos' => fn ($query) => $query->limit(1)])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $relatedProperties->each(function (Property $relatedProperty): void {
            $photo = $relatedProperty->photos->first();
            $specs = [];

            if ($relatedProperty->rooms) {
                $specs[] = "{$relatedProperty->rooms} KT";
            }

            if ($relatedProperty->bathrooms) {
                $specs[] = "{$relatedProperty->bathrooms} KM";
            }

            $area = $relatedProperty->building_area_sqm ?? $relatedProperty->land_area_sqm;
            $specs[] = number_format((float) $area, 0, ',', '.').' m²';

            $relatedProperty->setAttribute('listing_specs', implode(' • ', $specs));
            $relatedProperty->setAttribute(
                'image_url',
                $photo ? Storage::disk('local')->temporaryUrl($photo->file_path, now()->addHour()) : null,
            );
        });

        $propertyFacts = array_filter([
            'Kondisi' => $property->condition,
            'Lantai' => $property->floors,
            'Lebar depan' => $property->front_width ? number_format((float) $property->front_width, 0, ',', '.').' m' : null,
            'Kontur tanah' => $property->land_contour,
            'Akses jalan' => $property->road_access,
            'Zona' => $property->zone_type,
        ], fn (?string $value): bool => filled($value));

        $locationFacts = array_filter([
            'Alamat' => $property->address,
            'Kecamatan' => $property->district,
            'Kota / Kabupaten' => $property->city,
            'Provinsi' => $property->province,
            'Kode Pos' => $property->postal_code,
        ], fn (?string $value): bool => filled($value));

        $mapLocation = filled($property->latitude) && filled($property->longitude)
            ? "{$property->latitude},{$property->longitude}"
            : "{$property->address}, {$property->city}, {$property->province}";
        $mapUrl = 'https://maps.google.com/maps?q='.urlencode($mapLocation).'&output=embed';

        return view('property-details', compact('property', 'propertyFacts', 'locationFacts', 'mapUrl', 'relatedProperties', 'specifications'));
    }

    public function taxCalculator()
    {
        return view('tax-calculator');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
}
