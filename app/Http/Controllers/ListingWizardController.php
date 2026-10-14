<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingStepOneRequest;
use App\Http\Requests\StoreListingStepTwoRequest;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Services\ImageWatermarkService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

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
    public function storeStepTwo(StoreListingStepTwoRequest $request, ImageWatermarkService $imageWatermarkService): JsonResponse
    {
        $sessionId = session()->getId();
        $photoPaths = [];

        try {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $imageWatermarkService->process($photo, "properties/tmp/{$sessionId}/photos");

                $photoPaths[] = [
                    'path' => $path,
                    'order' => $index,
                ];
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_column($photoPaths, 'path'));
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Foto gagal diproses. Silakan coba unggah kembali.',
            ], 500);
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

        $movedFiles = [];

        try {
            $property = DB::transaction(function () use ($step1, $step2, &$movedFiles): Property {
                $propertyType = $step1['property_type'];
                $type = $propertyType === 'bangunan' ? $step1['building_type'] : $step1['land_type'];
                $category = $step1['transaction_type'] === 'dijual' ? 'sale' : 'rent';

                $property = Property::create([
                    'uuid' => Str::uuid()->toString(),
                    'seller_id' => Auth::id(),
                    'property_type' => $propertyType,
                    'type' => $type,
                    'category' => $category,
                    'status' => 'pending_verification',
                    'title' => $step1['title'],
                    'description' => $step1['description'],
                    'price' => $step1['price'],
                    'negotiable' => $step1['negotiable'] ?? false,
                    'land_area_sqm' => $step1['land_area_sqm'],
                    'building_area_sqm' => $step1['building_area_sqm'] ?? null,
                    'rooms' => $step1['bedrooms'] ?? null,
                    'bathrooms' => $step1['bathrooms'] ?? null,
                    'floors' => $step1['floors'] ?? null,
                    'condition' => $step1['condition'] ?? null,
                    'front_width' => $step1['front_width'] ?? null,
                    'land_contour' => $step1['land_contour'] ?? null,
                    'road_access' => $step1['road_access'] ?? null,
                    'zone_type' => $step1['zone_type'] ?? null,
                    'certificate_type' => $step1['certificate_type'],
                    'province' => $step1['province'],
                    'city' => $step1['city'],
                    'district' => $step1['district'],
                    'postal_code' => $step1['postal_code'] ?? null,
                    'address' => $step1['address'],
                    'latitude' => $step1['latitude'] ?? null,
                    'longitude' => $step1['longitude'] ?? null,
                    'url_maps' => $step1['url_maps'] ?? null,
                ]);

                foreach ($step2['photos'] ?? [] as $photo) {
                    $newPath = "properties/{$property->id}/photos/".basename($photo['path']);
                    $disk = Storage::disk('public');

                    if (! $disk->move($photo['path'], $newPath)) {
                        throw new RuntimeException('A property photo could not be moved into permanent storage.');
                    }

                    $movedFiles[] = ['disk' => 'public', 'source' => $photo['path'], 'destination' => $newPath];

                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'file_path' => $newPath,
                        'type' => 'photo',
                        'sort_order' => $photo['order'],
                    ]);
                }

                if (filled($step2['certificate'] ?? null)) {
                    $newCertPath = "properties/{$property->id}/certificates/".basename($step2['certificate']);
                    $disk = Storage::disk('local');

                    if (! $disk->move($step2['certificate'], $newCertPath)) {
                        throw new RuntimeException('The property certificate could not be moved into permanent storage.');
                    }

                    $movedFiles[] = ['disk' => 'local', 'source' => $step2['certificate'], 'destination' => $newCertPath];

                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'file_path' => $newCertPath,
                        'type' => 'certificate',
                        'sort_order' => 0,
                    ]);
                }

                return $property;
            });
        } catch (Throwable $exception) {
            foreach (array_reverse($movedFiles) as $movedFile) {
                $disk = Storage::disk($movedFile['disk']);

                try {
                    if ($disk->exists($movedFile['destination']) && ! $disk->move($movedFile['destination'], $movedFile['source'])) {
                        $disk->delete($movedFile['destination']);
                        $request->session()->forget('listing_wizard.step_2');
                    }
                } catch (Throwable $rollbackException) {
                    report($rollbackException);
                    $disk->delete($movedFile['destination']);
                    $request->session()->forget('listing_wizard.step_2');
                }
            }

            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Listing gagal disimpan. Silakan coba lagi.',
            ], 500);
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
                $photoUrls[] = Storage::disk('public')->url($photo['path']);
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

        $url = Storage::disk('public')->exists($propertyPhoto->file_path)
            ? Storage::disk('public')->url($propertyPhoto->file_path)
            : Storage::disk('local')->temporaryUrl($propertyPhoto->file_path, now()->addMinutes(30));

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
