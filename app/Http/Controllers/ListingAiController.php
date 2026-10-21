<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtractListingDataRequest;
use App\Http\Requests\GenerateListingDescriptionRequest;
use App\Services\Ai\AiProviderException;
use App\Services\Listing\ListingAiService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ListingAiController extends Controller
{
    public function __construct(private ListingAiService $listingAiService) {}

    public function extract(ExtractListingDataRequest $request): JsonResponse
    {
        try {
            $result = $this->listingAiService->extract($request->string('text')->toString());
        } catch (AiProviderException) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan AI sedang tidak tersedia. Silakan coba lagi sebentar.',
                'code' => 'ai_unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if ($result['data'] === []) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data properti yang dapat dikenali. Coba sebutkan tipe, lokasi, luas, atau harga properti.',
                'code' => 'no_property_data',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data properti berhasil diekstrak. Periksa kembali sebelum melanjutkan.',
            'data' => $result['data'],
            'fields' => $result['fields'],
        ]);
    }

    public function description(GenerateListingDescriptionRequest $request): JsonResponse
    {
        try {
            $description = $this->listingAiService->generateDescription($request->validated());
        } catch (AiProviderException) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan AI sedang tidak tersedia. Silakan coba lagi sebentar.',
                'code' => 'ai_unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if ($description === '') {
            return response()->json([
                'success' => false,
                'message' => 'AI belum dapat membuat deskripsi. Lengkapi data properti lalu coba lagi.',
                'code' => 'empty_description',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'description' => $description,
        ]);
    }
}
