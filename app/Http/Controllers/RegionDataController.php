<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class RegionDataController extends Controller
{
    public function provinces(): JsonResponse
    {
        return $this->regions('wilayah.provinces.v1', 'provinces');
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        return $this->regions("wilayah.regencies.{$provinceCode}.v1", "regencies/{$provinceCode}");
    }

    public function districts(string $regencyCode): JsonResponse
    {
        return $this->regions("wilayah.districts.{$regencyCode}.v1", "districts/{$regencyCode}");
    }

    private function regions(string $cacheKey, string $endpoint): JsonResponse
    {
        try {
            $regions = Cache::remember($cacheKey, now()->addDays(7), function () use ($endpoint): array {
                $response = Http::acceptJson()
                    ->connectTimeout(3)
                    ->timeout(8)
                    ->get("https://wilayah.id/api/{$endpoint}.json");

                if (! $response->successful()) {
                    throw new RuntimeException('Wilayah.id returned an unsuccessful response.');
                }

                $data = $response->json('data');

                if (! is_array($data) || ! array_is_list($data)) {
                    throw new RuntimeException('Wilayah.id returned an invalid data structure.');
                }

                foreach ($data as $region) {
                    if (! is_array($region) || ! is_string($region['code'] ?? null) || ! is_string($region['name'] ?? null)) {
                        throw new RuntimeException('Wilayah.id returned an invalid region item.');
                    }
                }

                return $data;
            });

            return response()->json(['data' => $regions]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Data wilayah sedang tidak tersedia. Silakan coba lagi.',
            ], 503);
        }
    }
}
