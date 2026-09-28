<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingStepOneRequest;
use App\Http\Requests\StoreListingStepTwoRequest;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingWizardController extends Controller
{
    /**
     * Show the listing wizard form.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('listing-wizard.create');
    }

    /**
     * Handle Step 1: Save property info (draft).
     */
    public function storeStepOne(StoreListingStepOneRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        $property = Property::create([
            'uuid' => Str::uuid()->toString(),
            'seller_id' => Auth::id(),
            'type' => $validated['type'],
            'status' => 'draft',
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'land_area_sqm' => $validated['land_area_sqm'],
            'building_area_sqm' => $validated['building_area_sqm'] ?? null,
            'certificate_type' => $validated['certificate_type'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'district' => $validated['district'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi properti berhasil disimpan.',
            'property_id' => $property->id,
        ]);
    }

    /**
     * Handle Step 2: Upload photos & certificate.
     */
    public function storeStepTwo(StoreListingStepTwoRequest $request, Property $property): \Illuminate\Http\JsonResponse
    {
        $this->authorizeOwnership($property);

        // Store property photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store("properties/{$property->id}/photos", 'local');

                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'file_path' => $path,
                    'type' => 'photo',
                    'sort_order' => $index,
                ]);
            }
        }

        // Store certificate file (private disk for signed URL)
        if ($request->hasFile('certificate_file')) {
            $certPath = $request->file('certificate_file')
                ->store("properties/{$property->id}/certificates", 'local');

            PropertyPhoto::create([
                'property_id' => $property->id,
                'file_path' => $certPath,
                'type' => 'certificate',
                'sort_order' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto dan sertifikat berhasil diunggah.',
        ]);
    }

    /**
     * Handle Step 3: Submit listing for verification.
     */
    public function submit(Property $property): \Illuminate\Http\JsonResponse
    {
        $this->authorizeOwnership($property);

        $property->update(['status' => 'pending_verification']);

        return response()->json([
            'success' => true,
            'message' => 'Listing berhasil disubmit untuk verifikasi.',
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * Get review data for step 3.
     */
    public function review(Property $property): \Illuminate\Http\JsonResponse
    {
        $this->authorizeOwnership($property);

        $property->load(['photos', 'certificate']);

        $photoUrls = $property->photos->map(function (PropertyPhoto $photo) {
            return Storage::disk('local')->temporaryUrl($photo->file_path, now()->addMinutes(30));
        });

        $certificateUrl = null;
        $certificateFile = $property->certificate->first();
        if ($certificateFile) {
            $certificateUrl = Storage::disk('local')->temporaryUrl(
                $certificateFile->file_path,
                now()->addMinutes(10)
            );
        }

        return response()->json([
            'success' => true,
            'property' => $property->only([
                'id', 'uuid', 'type', 'title', 'description', 'price',
                'land_area_sqm', 'building_area_sqm', 'certificate_type',
                'province', 'city', 'district', 'address',
                'latitude', 'longitude',
            ]),
            'photo_urls' => $photoUrls,
            'certificate_url' => $certificateUrl,
        ]);
    }

    /**
     * Generate a signed temporary URL for a property photo.
     */
    public function signedPhotoUrl(PropertyPhoto $propertyPhoto): \Illuminate\Http\JsonResponse
    {
        $property = $propertyPhoto->property;
        $this->authorizeOwnership($property);

        $url = Storage::disk('local')->temporaryUrl(
            $propertyPhoto->file_path,
            now()->addMinutes(30)
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Ensure the authenticated user owns the property.
     */
    private function authorizeOwnership(Property $property): void
    {
        if ($property->seller_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke properti ini.');
        }
    }
}
