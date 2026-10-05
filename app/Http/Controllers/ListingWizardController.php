<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingStepOneRequest;
use App\Http\Requests\StoreListingStepTwoRequest;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingWizardController extends Controller
{
    /**
     * Show the listing wizard form.
     */
    public function create(): View
    {
        return view('listing-wizard.create');
    }

    /**
     * Handle Step 1: Save property info (draft).
     */
    public function storeStepOne(StoreListingStepOneRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->session()->put('listing_wizard.step_1', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Informasi properti berhasil disimpan.',
        ]);
    }

    /**
     * Handle Step 2: Upload photos & certificate.
     */
    public function storeStepTwo(StoreListingStepTwoRequest $request): JsonResponse
    {
        $sessionId = session()->getId();
        $photoPaths = [];

        // Store property photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store("properties/tmp/{$sessionId}/photos", 'local');

                $photoPaths[] = [
                    'path' => $path,
                    'order' => $index,
                ];
            }
        }

        // Store certificate file
        $certPath = null;
        if ($request->hasFile('certificate_file')) {
            $certPath = $request->file('certificate_file')
                ->store("properties/tmp/{$sessionId}/certificates", 'local');
        }

        $request->session()->put('listing_wizard.step_2', [
            'photos' => $photoPaths,
            'certificate' => $certPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto dan sertifikat berhasil diunggah.',
        ]);
    }

    /**
     * Handle Step 3: Submit listing for verification.
     */
    public function submit(Request $request): JsonResponse
    {
        $step1 = $request->session()->get('listing_wizard.step_1');
        $step2 = $request->session()->get('listing_wizard.step_2');

        if (! $step1 || ! $step2) {
            return response()->json(['success' => false, 'message' => 'Data wizard tidak lengkap atau sesi telah berakhir.'], 400);
        }

        $property = Property::create([
            'uuid' => Str::uuid()->toString(),
            'seller_id' => Auth::id(),
            'type' => $step1['type'],
            'status' => 'pending_verification',
            'title' => $step1['title'],
            'description' => $step1['description'],
            'price' => $step1['price'],
            'land_area_sqm' => $step1['land_area_sqm'],
            'building_area_sqm' => $step1['building_area_sqm'] ?? null,
            'certificate_type' => $step1['certificate_type'],
            'province' => $step1['province'],
            'city' => $step1['city'],
            'district' => $step1['district'],
            'address' => $step1['address'],
            'latitude' => $step1['latitude'] ?? null,
            'longitude' => $step1['longitude'] ?? null,
            'url_maps' => $step1['url_maps'] ?? null,
        ]);

        if (isset($step2['photos']) && is_array($step2['photos'])) {
            foreach ($step2['photos'] as $photo) {
                $newPath = "properties/{$property->id}/photos/".basename($photo['path']);
                Storage::disk('local')->move($photo['path'], $newPath);

                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'file_path' => $newPath,
                    'type' => 'photo',
                    'sort_order' => $photo['order'],
                ]);
            }
        }

        if (isset($step2['certificate']) && $step2['certificate']) {
            $newCertPath = "properties/{$property->id}/certificates/".basename($step2['certificate']);
            Storage::disk('local')->move($step2['certificate'], $newCertPath);

            PropertyPhoto::create([
                'property_id' => $property->id,
                'file_path' => $newCertPath,
                'type' => 'certificate',
                'sort_order' => 0,
            ]);
        }

        $request->session()->forget('listing_wizard');

        return response()->json([
            'success' => true,
            'message' => 'Listing berhasil disubmit untuk verifikasi.',
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * Get review data for step 3.
     */
    public function review(Request $request): JsonResponse
    {
        $step1 = $request->session()->get('listing_wizard.step_1');
        $step2 = $request->session()->get('listing_wizard.step_2');

        if (! $step1) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $photoUrls = [];
        if (isset($step2['photos'])) {
            foreach ($step2['photos'] as $photo) {
                $photoUrls[] = Storage::disk('local')->temporaryUrl($photo['path'], now()->addMinutes(30));
            }
        }

        $certificateUrl = null;
        if (isset($step2['certificate']) && $step2['certificate']) {
            $certificateUrl = Storage::disk('local')->temporaryUrl(
                $step2['certificate'],
                now()->addMinutes(10)
            );
        }

        return response()->json([
            'success' => true,
            'property' => $step1,
            'photo_urls' => $photoUrls,
            'certificate_url' => $certificateUrl,
        ]);
    }

    /**
     * Generate a signed temporary URL for a property photo.
     */
    public function signedPhotoUrl(PropertyPhoto $propertyPhoto): JsonResponse
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
