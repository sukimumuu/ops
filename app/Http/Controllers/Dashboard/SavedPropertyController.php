<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavedPropertyController extends Controller
{
    /**
     * Toggle the authenticated user's saved state for a property.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
        ]);

        $user = $request->user();
        $property = Property::query()->findOrFail($validated['property_id']);

        $saved = DB::transaction(function () use ($user, $property): bool {
            // Serialize toggles for the same user so two concurrent requests
            // cannot both observe the property as unsaved and insert it twice.
            $user->newQuery()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            $isSaved = $user->savedProperties()
                ->whereKey($property->getKey())
                ->exists();

            if ($isSaved) {
                $user->savedProperties()->detach($property->getKey());

                return false;
            }

            if ($property->status !== 'published') {
                abort(422, 'Properti ini belum tersedia untuk disimpan.');
            }

            $user->savedProperties()->syncWithoutDetaching([$property->getKey()]);

            return true;
        });

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'property_id' => $property->getKey(),
            'message' => $saved
                ? 'Properti berhasil disimpan.'
                : 'Properti dihapus dari daftar tersimpan.',
        ]);
    }
}
