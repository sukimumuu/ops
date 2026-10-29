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
    public function create(Request $request): View
    {
        return view('listing-wizard.create', [
            'initialForm' => $request->session()->get('listing_wizard.step_1', []),
        ]);
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
        if ($request->hasFile('certificate_file')) {
            abort_unless($request->user()?->can('documents.upload_transactional'), 403);
        }

        $sessionId = session()->getId();
        $previousStepTwo = $request->session()->get('listing_wizard.step_2', []);
        $photoPaths = [];
        $stagedFiles = [];

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

        try {
            $certificates = [];

            $certificateFiles = [
                [
                    'type' => $request->string('jenis_sertifikat')->toString(),
                    'file' => $request->file('certificate_document'),
                ],
            ];

            foreach ($request->input('additional_certificates', []) as $index => $additionalCertificate) {
                $certificateFiles[] = [
                    'type' => (string) ($additionalCertificate['jenis_sertifikat'] ?? ''),
                    'file' => $request->file("additional_certificates.{$index}.certificate_document"),
                ];
            }

            foreach ($certificateFiles as $index => $certificateFile) {
                if (! $certificateFile['file']) {
                    continue;
                }

                $path = $certificateFile['file']->store("properties/tmp/{$sessionId}/certificates", 'local');
                $stagedFiles[] = $path;

                $certificates[] = [
                    'path' => $path,
                    'type' => $certificateFile['type'],
                    'order' => $index,
                ];
            }

            $optionalDocuments = [
                'imb' => filled($previousStepTwo['imb'] ?? null) ? $previousStepTwo['imb'] : null,
                'pbb' => filled($previousStepTwo['pbb'] ?? null) ? $previousStepTwo['pbb'] : null,
            ];

            foreach (['imb' => 'imb_document', 'pbb' => 'pbb_document'] as $key => $input) {
                if (! $request->hasFile($input)) {
                    continue;
                }

                $path = $request->file($input)->store("properties/tmp/{$sessionId}/documents", 'local');
                $stagedFiles[] = $path;
                $optionalDocuments[$key] = $path;
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($stagedFiles);
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Dokumen gagal diunggah. Silakan coba lagi.',
            ], 500);
        }

        $currentCertificatePaths = array_column($certificates, 'path');

        $replacedFiles = array_filter([
            ...array_column($previousStepTwo['certificates'] ?? [], 'path'),
            $previousStepTwo['certificate'] ?? null,
            $previousStepTwo['imb'] ?? null,
            $previousStepTwo['pbb'] ?? null,
        ], fn ($path): bool => is_string($path)
            && ! in_array($path, $currentCertificatePaths, true)
            && ! in_array($path, $optionalDocuments, true));

        Storage::disk('local')->delete($replacedFiles);

        $request->session()->put('listing_wizard.step_2', [
            'photos' => $photoPaths,
            'certificates' => $certificates,
            'imb' => $optionalDocuments['imb'],
            'pbb' => $optionalDocuments['pbb'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto dan dokumen berhasil diunggah.',
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

                $certificates = $step2['certificates'] ?? [];

                if ($certificates === [] && filled($step2['certificate'] ?? null)) {
                    $certificates = [[
                        'path' => $step2['certificate'],
                        'type' => $step1['certificate_type'] ?? null,
                        'order' => 0,
                    ]];
                }

                $property = Property::create([
                    'uuid' => Str::uuid()->toString(),
                    'seller_id' => Auth::id(),
                    'property_type' => $propertyType,
                    'type' => $type,
                    'category' => $category,
                    'rent_period' => $step1['rent_period'] ?? null,
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
                    'certificate_type' => $certificates[0]['type'] ?? $step1['certificate_type'] ?? null,
                    'province' => $step1['province'],
                    'province_code' => $step1['province_code'] ?? null,
                    'city' => $step1['city'],
                    'city_code' => $step1['city_code'] ?? null,
                    'district' => $step1['district'],
                    'district_code' => $step1['district_code'] ?? null,
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

                foreach ($certificates as $certificate) {
                    $stagedPath = $certificate['path'] ?? null;

                    if (blank($stagedPath)) {
                        continue;
                    }

                    $newCertPath = "properties/{$property->id}/certificates/".basename($stagedPath);
                    $disk = Storage::disk('local');

                    if (! $disk->move($stagedPath, $newCertPath)) {
                        throw new RuntimeException('The property certificate could not be moved into permanent storage.');
                    }

                    $movedFiles[] = ['disk' => 'local', 'source' => $stagedPath, 'destination' => $newCertPath];

                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'file_path' => $newCertPath,
                        'type' => 'certificate',
                        'certificate_type' => $certificate['type'] ?? null,
                        'sort_order' => $certificate['order'] ?? 0,
                    ]);
                }

                foreach (['imb', 'pbb'] as $documentType) {
                    $stagedPath = $step2[$documentType] ?? null;

                    if (blank($stagedPath)) {
                        continue;
                    }

                    $newDocumentPath = "properties/{$property->id}/documents/".basename($stagedPath);
                    $disk = Storage::disk('local');

                    if (! $disk->move($stagedPath, $newDocumentPath)) {
                        throw new RuntimeException('A property document could not be moved into permanent storage.');
                    }

                    $movedFiles[] = ['disk' => 'local', 'source' => $stagedPath, 'destination' => $newDocumentPath];

                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'file_path' => $newDocumentPath,
                        'type' => $documentType,
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
            'redirect' => route('seller.listing'),
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

        $certificateUrls = [];

        foreach ($step2['certificates'] ?? [] as $certificate) {
            if (filled($certificate['path'] ?? null)) {
                $certificateUrls[] = Storage::disk('local')->temporaryUrl(
                    $certificate['path'],
                    now()->addMinutes(10)
                );
            }
        }

        if ($certificateUrls === [] && filled($step2['certificate'] ?? null)) {
            $certificateUrls[] = Storage::disk('local')->temporaryUrl(
                $step2['certificate'],
                now()->addMinutes(10)
            );
        }

        $documentUrls = [];

        foreach (['imb', 'pbb'] as $documentType) {
            if (filled($step2[$documentType] ?? null)) {
                $documentUrls[$documentType] = Storage::disk('local')->temporaryUrl(
                    $step2[$documentType],
                    now()->addMinutes(10)
                );
            }
        }

        return response()->json([
            'success' => true,
            'property' => $step1,
            'photo_urls' => $photoUrls,
            'certificate_url' => $certificateUrls[0] ?? null,
            'certificate_urls' => $certificateUrls,
            'document_urls' => $documentUrls,
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
