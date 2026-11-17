<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveListingDraftRequest;
use App\Http\Requests\StoreListingStepOneRequest;
use App\Http\Requests\StoreListingStepTwoRequest;
use App\Models\AuditLog;
use App\Models\Property;
use App\Models\PropertyDraft;
use App\Models\PropertyPhoto;
use App\Services\Audit\AuditLogger;
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
        if ($request->boolean('new')) {
            $request->session()->forget('listing_wizard');
        } elseif (filled($draftUuid = $request->session()->get('listing_wizard.draft_uuid'))) {
            $draft = PropertyDraft::query()
                ->whereBelongsTo($request->user(), 'seller')
                ->where('uuid', $draftUuid)
                ->first();

            if ($draft) {
                return $this->editDraft($request, $draft);
            }
        }

        return view('listing-wizard.create', [
            'initialForm' => $request->session()->get('listing_wizard.step_1', []),
            'initialDraftUuid' => $request->session()->get('listing_wizard.draft_uuid'),
            'initialDraftStep' => $request->session()->get('listing_wizard.current_step', 1),
            'initialUploads' => [],
        ]);
    }

    public function editDraft(Request $request, PropertyDraft $draft): View
    {
        abort_unless($draft->seller_id === $request->user()->id, 404);

        $uploads = $draft->uploads ?? [];
        $initialUploads = $this->formatDraftUploads($uploads);

        $request->session()->put('listing_wizard.draft_uuid', $draft->uuid);
        $request->session()->put('listing_wizard.step_1', $draft->payload);
        $request->session()->put('listing_wizard.step_2', $uploads);
        $request->session()->put('listing_wizard.current_step', $draft->current_step);

        return view('listing-wizard.create', [
            'initialForm' => $draft->payload,
            'initialDraftUuid' => $draft->uuid,
            'initialDraftStep' => $draft->current_step,
            'initialUploads' => $initialUploads,
        ]);
    }

    /**
     * Format stored draft uploads for display in the listing wizard.
     *
     * @param array{
     *     photos?: list<array{path: string, name?: string}>,
     *     certificates?: list<array{path: string, type?: string, name?: string}>,
     *     imb?: array{path?: string, name?: string}|string|null,
     *     pbb?: array{path?: string, name?: string}|string|null
     * } $uploads
     * @return array{
     *     photos: list<array{url: string, name: string}>,
     *     certificates: list<array{type: string, name: string}>,
     *     imb: array{name: string}|null,
     *     pbb: array{name: string}|null
     * }
     */
    private function formatDraftUploads(array $uploads): array
    {
        return [
            'photos' => collect($uploads['photos'] ?? [])
                ->map(fn (array $photo): array => [
                    'url' => Storage::disk('public')->url($photo['path']),
                    'name' => $photo['name'] ?? basename($photo['path']),
                ])
                ->all(),
            'certificates' => collect($uploads['certificates'] ?? [])
                ->map(fn (array $certificate): array => [
                    'type' => $certificate['type'] ?? '',
                    'name' => $certificate['name'] ?? basename($certificate['path'] ?? ''),
                ])
                ->all(),
            'imb' => $this->draftDocumentName($uploads['imb'] ?? null),
            'pbb' => $this->draftDocumentName($uploads['pbb'] ?? null),
        ];
    }

    /**
     * Return a safe display name for an optional document stored with a draft.
     *
     * @param  array{path?: string, name?: string}|string|null  $document
     * @return array{name: string}|null
     */
    private function draftDocumentName(array|string|null $document): ?array
    {
        if (blank($document)) {
            return null;
        }

        if (is_string($document)) {
            return ['name' => basename($document)];
        }

        return ['name' => $document['name'] ?? basename($document['path'] ?? '')];
    }

    public function saveDraft(SaveListingDraftRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $validated['data'];
        $hasDraftData = collect($data)
            ->except('negotiable')
            ->contains(fn (mixed $value): bool => is_string($value) ? trim($value) !== '' : $value !== null);

        if (! $hasDraftData && blank($validated['draft_uuid'] ?? null)) {
            return response()->json([
                'success' => true,
                'draft_uuid' => null,
                'message' => 'Belum ada data untuk disimpan.',
            ]);
        }

        $draft = filled($validated['draft_uuid'] ?? null)
            ? PropertyDraft::query()
                ->whereBelongsTo($request->user(), 'seller')
                ->where('uuid', $validated['draft_uuid'])
                ->firstOrFail()
            : new PropertyDraft([
                'uuid' => Str::uuid()->toString(),
                'seller_id' => $request->user()->id,
            ]);

        $draft->fill([
            'payload' => $data,
            'current_step' => $validated['current_step'],
            'last_saved_at' => now(),
        ])->save();
        $request->session()->put('listing_wizard.draft_uuid', $draft->uuid);
        $request->session()->put('listing_wizard.step_1', $data);
        $request->session()->put('listing_wizard.current_step', $draft->current_step);

        return response()->json([
            'success' => true,
            'draft_uuid' => $draft->uuid,
            'message' => 'Draf berhasil disimpan.',
        ]);
    }

    /**
     * Handle Step 1: Save property info (draft).
     */
    public function storeStepOne(StoreListingStepOneRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $draftUuid = $validated['draft_uuid'] ?? null;
        unset($validated['draft_uuid']);

        $request->session()->put('listing_wizard.step_1', $validated);

        $draft = filled($draftUuid)
            ? PropertyDraft::query()
                ->whereBelongsTo($request->user(), 'seller')
                ->where('uuid', $draftUuid)
                ->firstOrFail()
            : new PropertyDraft([
                'uuid' => Str::uuid()->toString(),
                'seller_id' => $request->user()->id,
            ]);

        $draft->fill([
            'payload' => $validated,
            'current_step' => 2,
            'last_saved_at' => now(),
        ])->save();
        $request->session()->put('listing_wizard.draft_uuid', $draft->uuid);
        $request->session()->put('listing_wizard.current_step', 2);

        return response()->json([
            'success' => true,
            'message' => 'Informasi properti berhasil disimpan.',
            'draft_uuid' => $draft->uuid,
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

        $draftUuid = $request->input('draft_uuid') ?? $request->session()->get('listing_wizard.draft_uuid');
        $draft = filled($draftUuid)
            ? PropertyDraft::query()
                ->whereBelongsTo($request->user(), 'seller')
                ->where('uuid', $draftUuid)
                ->firstOrFail()
            : null;
        $sessionId = session()->getId();
        $previousStepTwo = $draft?->uploads ?? $request->session()->get('listing_wizard.step_2', []);
        $uploadDirectory = $draft
            ? "property-drafts/{$draft->uuid}"
            : "properties/tmp/{$sessionId}";
        $photoPaths = $previousStepTwo['photos'] ?? [];
        $newPhotoPaths = [];
        $stagedFiles = [];
        $replacedFiles = [];

        try {
            foreach ($request->file('photos', []) as $photo) {
                $path = $imageWatermarkService->process($photo, "{$uploadDirectory}/photos");

                $newPhotoPaths[] = [
                    'path' => $path,
                    'order' => count($photoPaths) + count($newPhotoPaths),
                    'name' => $photo->getClientOriginalName(),
                ];
            }
            $photoPaths = [...$photoPaths, ...$newPhotoPaths];
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_column($newPhotoPaths, 'path'));
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Foto gagal diproses. Silakan coba unggah kembali.',
            ], 500);
        }

        try {
            $certificates = $previousStepTwo['certificates'] ?? [];
            $certificateFiles = [];

            if ($request->hasFile('certificate_document')) {
                $certificateFiles[] = [
                    'type' => $request->string('jenis_sertifikat')->toString(),
                    'file' => $request->file('certificate_document'),
                ];
            }

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

                $path = $certificateFile['file']->store("{$uploadDirectory}/certificates", 'local');
                $stagedFiles[] = $path;

                $certificates[] = [
                    'path' => $path,
                    'type' => $certificateFile['type'],
                    'order' => count($certificates),
                    'name' => $certificateFile['file']->getClientOriginalName(),
                ];
            }

            $optionalDocuments = [
                'imb' => $previousStepTwo['imb'] ?? null,
                'pbb' => $previousStepTwo['pbb'] ?? null,
            ];

            foreach (['imb' => 'imb_document', 'pbb' => 'pbb_document'] as $key => $input) {
                if (! $request->hasFile($input)) {
                    continue;
                }

                $existingPath = is_array($optionalDocuments[$key])
                    ? ($optionalDocuments[$key]['path'] ?? null)
                    : $optionalDocuments[$key];
                if (filled($existingPath)) {
                    $replacedFiles[] = $existingPath;
                }

                $path = $request->file($input)->store("{$uploadDirectory}/documents", 'local');
                $stagedFiles[] = $path;
                $optionalDocuments[$key] = $draft
                    ? ['path' => $path, 'name' => $request->file($input)->getClientOriginalName()]
                    : $path;
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($stagedFiles);
            Storage::disk('public')->delete(array_column($newPhotoPaths, 'path'));
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Dokumen gagal diunggah. Silakan coba lagi.',
            ], 500);
        }

        Storage::disk('local')->delete($replacedFiles);

        $stepTwo = [
            'photos' => $photoPaths,
            'certificates' => $certificates,
            'imb' => $optionalDocuments['imb'],
            'pbb' => $optionalDocuments['pbb'],
        ];
        $request->session()->put('listing_wizard.step_2', $stepTwo);
        if ($draft) {
            $draft->fill([
                'uploads' => $stepTwo,
                'current_step' => 3,
                'last_saved_at' => now(),
            ])->save();
            $request->session()->put('listing_wizard.draft_uuid', $draft->uuid);
            $request->session()->put('listing_wizard.current_step', 3);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto dan dokumen berhasil diunggah.',
            'uploads' => $draft ? $this->formatDraftUploads($stepTwo) : null,
        ]);
    }

    /**
     * Handle Step 3: Submit listing for verification.
     */
    public function submit(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $draftUuid = $request->input('draft_uuid') ?? $request->session()->get('listing_wizard.draft_uuid');
        $draft = filled($draftUuid)
            ? PropertyDraft::query()
                ->whereBelongsTo($request->user(), 'seller')
                ->where('uuid', $draftUuid)
                ->firstOrFail()
            : null;
        $step1 = $request->session()->get('listing_wizard.step_1') ?? $draft?->payload;
        $step2 = $request->session()->get('listing_wizard.step_2') ?? $draft?->uploads ?? [];

        if (! $step1 || (! $step2 && ! $draft)) {
            return response()->json(['success' => false, 'message' => 'Data wizard tidak lengkap atau sesi telah berakhir.'], 400);
        }

        $submissionErrors = [];

        if ($draft && empty($step2['photos'])) {
            $submissionErrors['photos'] = ['Minimal 1 foto properti wajib diunggah.'];
        }

        if ($draft && empty($step2['certificates']) && blank($step2['certificate'] ?? null)) {
            $submissionErrors['certificate_document'] = ['Sertifikat properti wajib diunggah.'];
        }

        if ($submissionErrors !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Unggah minimal satu foto properti dan sertifikat sebelum mengirim listing.',
                'errors' => $submissionErrors,
            ], 422);
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
                    $document = $step2[$documentType] ?? null;
                    $stagedPath = is_array($document) ? ($document['path'] ?? null) : $document;

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

        $draft?->delete();
        $request->session()->forget('listing_wizard');

        $auditLogger->record(
            AuditLog::EVENT_PROPERTY_SUBMITTED,
            'Properti "'.$property->title.'" diajukan untuk verifikasi.',
            $property,
            [
                'status' => ['old' => 'draft', 'new' => 'pending_verification'],
                'category' => $property->category,
                'property_type' => $property->property_type,
                'price' => (string) $property->price,
            ],
            $request->user(),
        );

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
            $document = $step2[$documentType] ?? null;
            $documentPath = is_array($document) ? ($document['path'] ?? null) : $document;

            if (filled($documentPath)) {
                $documentUrls[$documentType] = Storage::disk('local')->temporaryUrl(
                    $documentPath,
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
