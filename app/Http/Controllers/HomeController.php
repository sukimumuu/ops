<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function home(Request $request): View
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
                    $photo ? $this->propertyPhotoUrl($photo) : null,
                );
            });
        } catch (QueryException $exception) {
            report($exception);

            $properties = collect();
            $propertiesUnavailable = true;
        }

        $savedPropertyIds = $this->savedPropertyIdsFor($request);

        return view('welcome', compact(
            'properties',
            'propertiesUnavailable',
            'savedPropertyIds',
        ));
    }

    public function property(Request $request): View
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
                    $photo ? $this->propertyPhotoUrl($photo) : null,
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

        $savedPropertyIds = $this->savedPropertyIdsFor($request);

        return view('properties', compact('properties', 'propertiesUnavailable', 'savedPropertyIds'));
    }

    public function propertyDetails(Request $request, string $uuid): View
    {
        $property = Property::query()
            ->where('uuid', $uuid)
            ->where('status', 'published')
            ->with(['photos', 'seller'])
            ->firstOrFail();

        $isSaved = $request->user() !== null
            && $request->user()->savedProperties()->whereKey($property->getKey())->exists();

        $activeAppointment = $request->user() === null
            ? null
            : Appointment::query()
                ->where('property_id', $property->getKey())
                ->where('buyer_id', $request->user()->getKey())
                ->whereIn('status', [Appointment::STATUS_REQUESTED, Appointment::STATUS_CONFIRMED])
                ->where('scheduled_at', '>=', now())
                ->latest('scheduled_at')
                ->first();

        $timeSlots = Appointment::TIME_SLOTS;

        $property->setAttribute(
            'photo_urls',
            $property->photos
                ->map(fn (PropertyPhoto $photo): string => $this->propertyPhotoUrl($photo))
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
                $photo ? $this->propertyPhotoUrl($photo) : null,
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

        return view('property-details', compact('property', 'propertyFacts', 'locationFacts', 'mapUrl', 'relatedProperties', 'specifications', 'isSaved', 'activeAppointment', 'timeSlots'));
    }

    /**
     * Return the saved property identifiers once so every card can render its
     * initial heart state without querying the database individually.
     *
     * @return array<int, int>
     */
    private function savedPropertyIdsFor(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [];
        }

        return $user->savedProperties()
            ->pluck('properties.id')
            ->all();
    }

    private function propertyPhotoUrl(PropertyPhoto $photo): string
    {
        $publicDisk = Storage::disk('public');

        return $publicDisk->exists($photo->file_path)
            ? $publicDisk->url($photo->file_path)
            : Storage::disk('local')->temporaryUrl($photo->file_path, now()->addHour());
    }

    public function taxCalculator()
    {
        return view('tax-calculator');
    }

    public function about()
    {
        return view('about');
    }

    public function scheduleVisit(Request $request, string $uuid): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk menjadwalkan kunjungan.');
        }

        $property = Property::query()
            ->where('uuid', $uuid)
            ->where('status', 'published')
            ->firstOrFail();

        if (auth()->id() === $property->seller_id) {
            return back()->with('error', 'Anda tidak dapat menjadwalkan kunjungan pada properti Anda sendiri.');
        }

        $hasActiveAppointment = Appointment::query()
            ->where('property_id', $property->id)
            ->where('buyer_id', auth()->id())
            ->whereIn('status', [Appointment::STATUS_REQUESTED, Appointment::STATUS_CONFIRMED])
            ->where('scheduled_at', '>=', now())
            ->exists();

        if ($hasActiveAppointment) {
            return back()->with('error', 'Anda sudah memiliki jadwal kunjungan aktif untuk properti ini.');
        }

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'string', Rule::in(Appointment::TIME_SLOTS)],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'scheduled_time.required' => 'Pilih waktu kunjungan.',
            'scheduled_time.in' => 'Waktu kunjungan yang dipilih tidak valid.',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_at'])->setTimeFromTimeString($validated['scheduled_time']);

        Appointment::query()->create([
            'property_id' => $property->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $property->seller_id,
            'scheduled_at' => $scheduledAt,
            'status' => 'requested',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Jadwal kunjungan berhasil diajukan! Pemilik properti akan segera mengonfirmasi jadwal Anda.');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function userDetails(Request $request, string $uuid): View
    {
        $user = User::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $sort = $request->query('sort', 'terbaru');
        $category = $request->query('category');
        $type = $request->query('type');

        $query = Property::query()
            ->where('seller_id', $user->id)
            ->where('status', 'published')
            ->with(['photos' => fn ($q) => $q->limit(1)]);

        if (filled($category) && in_array($category, ['sale', 'rent'])) {
            $query->where('category', $category);
        }

        if (filled($type)) {
            $query->where('type', $type);
        }

        match ($sort) {
            'termurah' => $query->orderBy('price', 'asc'),
            'termahal' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $properties = $query->paginate(6)->withQueryString();

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

            if ($area) {
                $specs[] = number_format((float) $area, 0, ',', '.').' m²';
            }

            $property->setAttribute('listing_specs', implode(' • ', $specs));
            $property->setAttribute(
                'image_url',
                $photo ? $this->propertyPhotoUrl($photo) : null,
            );
        });

        $totalProperties = Property::query()->where('seller_id', $user->id)->where('status', 'published')->count();
        $totalRent = Property::query()->where('seller_id', $user->id)->where('status', 'published')->where('category', 'rent')->count();
        $totalSale = Property::query()->where('seller_id', $user->id)->where('status', 'published')->where('category', 'sale')->count();
        $availableTypes = Property::query()->where('seller_id', $user->id)->where('status', 'published')->pluck('type')->unique()->filter()->values();
        $cities = Property::query()->where('seller_id', $user->id)->where('status', 'published')->pluck('city')->unique()->filter()->values();

        return view('user-details', compact('user', 'properties', 'sort', 'category', 'type', 'totalProperties', 'totalRent', 'totalSale', 'availableTypes', 'cities'));
    }
}
