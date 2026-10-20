<?php

namespace App\Services\Chatbot;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PropertySearchService
{
    /**
     * @return Collection<int, Property>
     */
    public function search(string $conversationText): Collection
    {
        $criteria = mb_strtolower($conversationText);
        $query = Property::query()
            ->where('status', 'published')
            ->select([
                'id',
                'uuid',
                'type',
                'category',
                'title',
                'price',
                'city',
                'district',
                'province',
                'rooms',
                'building_area_sqm',
                'land_area_sqm',
            ]);

        $location = $this->location($conversationText);

        if ($location !== null) {
            $query->where(function (Builder $query) use ($location): void {
                $query->where('city', 'like', "%{$location}%")
                    ->orWhere('district', 'like', "%{$location}%")
                    ->orWhere('province', 'like', "%{$location}%");
            });
        }

        $maxPrice = $this->maximumPrice($conversationText);

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $type = $this->propertyType($criteria);

        if ($type !== null) {
            $query->where('type', 'like', "%{$type}%");
        }

        $category = $this->category($criteria);

        if ($category !== null) {
            $query->where('category', $category);
        }

        $properties = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();
        $minimumBedrooms = $this->minimumBedrooms($criteria);

        if ($minimumBedrooms !== null) {
            $properties = $properties->filter(
                fn (Property $property): bool => $this->hasMinimumBedrooms($property->rooms, $minimumBedrooms),
            );
        }

        return $properties->take(5)->values();
    }

    private function location(string $conversationText): ?string
    {
        if (! preg_match('/(?:\blokasi(?:\s+di)?|\bdi)\s+([a-z][a-z\s\'-]{1,60})/iu', $conversationText, $matches)) {
            return null;
        }

        $location = preg_split(
            '/\s+(?:di\s+bawah|kurang\s+dari|maksimal|budget|harga|dengan|untuk|yang|tipe|jenis|kamar)\b/iu',
            trim($matches[1]),
        )[0] ?? '';
        $location = trim($location, " \t\n\r\0\x0B,.");

        return mb_strlen($location) >= 2 ? $location : null;
    }

    private function maximumPrice(string $conversationText): ?float
    {
        if (! preg_match(
            '/(?:di\s+bawah|kurang\s+dari|maksimal|budget|harga\s+di\s+bawah)\s*(?:rp\.?\s*)?([\d.,]+)\s*(miliar|milyar|juta|jt|m)?\b/iu',
            $conversationText,
            $matches,
        )) {
            return null;
        }

        $amount = $this->parseAmount($matches[1]);
        $unit = mb_strtolower($matches[2] ?? '');

        return $amount * match ($unit) {
            'miliar', 'milyar', 'm' => 1_000_000_000,
            'juta', 'jt' => 1_000_000,
            default => 1,
        };
    }

    private function parseAmount(string $amount): float
    {
        if (str_contains($amount, '.') && str_contains($amount, ',')) {
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
        } elseif (preg_match('/[.,]\d{3}(?:[.,]\d{3})*$/', $amount)) {
            $amount = str_replace([',', '.'], '', $amount);
        } else {
            $amount = str_replace(',', '.', $amount);
        }

        return (float) $amount;
    }

    private function propertyType(string $criteria): ?string
    {
        foreach (['apartemen', 'rumah', 'villa', 'ruko', 'tanah', 'kavling', 'kos'] as $type) {
            if (Str::contains($criteria, $type)) {
                return $type;
            }
        }

        return null;
    }

    private function hasMinimumBedrooms(?string $rooms, int $minimumBedrooms): bool
    {
        if (! preg_match('/^\s*(\d+)/', $rooms ?? '', $matches)) {
            return false;
        }

        return (int) $matches[1] >= $minimumBedrooms;
    }

    private function category(string $criteria): ?string
    {
        if (preg_match('/\b(sewa|disewa|kontrak|menyewa)\b/u', $criteria)) {
            return 'rent';
        }

        if (preg_match('/\b(beli|dijual|membeli)\b/u', $criteria)) {
            return 'sale';
        }

        return null;
    }

    private function minimumBedrooms(string $criteria): ?int
    {
        $numbers = [
            'satu' => 1,
            'dua' => 2,
            'tiga' => 3,
            'empat' => 4,
            'lima' => 5,
        ];

        if (! preg_match('/\b(\d+|satu|dua|tiga|empat|lima)\s*(?:kamar\s+tidur|kt)\b/u', $criteria, $matches)) {
            return null;
        }

        return is_numeric($matches[1]) ? (int) $matches[1] : $numbers[$matches[1]];
    }
}
