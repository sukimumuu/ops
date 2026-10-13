<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyReviewController extends Controller
{
    public function ppats(Request $request, Property $property): JsonResponse
    {
        Gate::authorize('review', $property);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim($filters['search'] ?? '');
        $page = (int) ($filters['page'] ?? 1);
        $query = User::role('PPAT')
            ->select('id', 'name', 'email', 'phone', 'province', 'city', 'district')
            ->when($property->assigned_ppat_id, fn (Builder $users, int $assignedPpatId): Builder => $users->whereKeyNot($assignedPpatId))
            ->when($search !== '', function (Builder $users) use ($search): void {
                $users->where(function (Builder $users) use ($search): void {
                    $users->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%");
                });
            });

        $total = (clone $query)->count();

        if (filled($property->district)) {
            $query->orderByRaw('CASE WHEN LOWER(TRIM(COALESCE(district, ?))) = ? THEN 0 ELSE 1 END', [
                '',
                mb_strtolower(trim($property->district)),
            ]);
        }

        $users = $query
            ->orderBy('name')
            ->offset(($page - 1) * 6)
            ->limit(6)
            ->get();
        $hasMore = $page * 6 < $total;

        return response()->json([
            'data' => $users->map(fn (User $user): array => $user->only([
                'id', 'name', 'email', 'phone', 'province', 'city', 'district',
            ]))->values(),
            'current_page' => $page,
            'has_more' => $hasMore,
        ]);
    }

    public function assignPpat(Request $request, Property $property): JsonResponse
    {
        Gate::authorize('review', $property);

        $validated = $request->validate([
            'ppat_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $ppat = User::role('PPAT')->find($validated['ppat_id']);

        if (! $ppat) {
            return response()->json([
                'message' => 'Pengguna yang dipilih bukan PPAT.',
                'errors' => ['ppat_id' => ['Pengguna yang dipilih bukan PPAT.']],
            ], 422);
        }

        if ($property->assigned_ppat_id === $ppat->id) {
            return response()->json([
                'message' => 'PPAT ini sudah ditugaskan ke properti.',
                'assigned' => $ppat->only(['id', 'name', 'email', 'phone', 'province', 'city', 'district']),
                'unchanged' => true,
            ]);
        }

        $property->assignedPpat()->associate($ppat);
        $property->save();

        return response()->json([
            'message' => 'PPAT berhasil ditugaskan ke properti.',
            'assigned' => $ppat->only(['id', 'name', 'email', 'phone', 'province', 'city', 'district']),
            'unchanged' => false,
        ]);
    }

    public function updateStatus(Request $request, Property $property): RedirectResponse
    {
        Gate::authorize('review', $property);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['published', 'rejected'])],
        ]);

        $updated = Property::query()
            ->whereKey($property->getKey())
            ->where('status', 'pending_verification')
            ->update(['status' => $validated['status']]);

        if ($updated === 0) {
            return back()->withErrors([
                'status' => 'Properti ini sudah diproses dan tidak dapat diubah kembali.',
            ]);
        }

        $message = $validated['status'] === 'published'
            ? 'Properti berhasil disetujui.'
            : 'Properti berhasil ditolak.';

        return back()->with('status', $message);
    }

    public function showFile(PropertyPhoto $propertyPhoto): StreamedResponse
    {
        Gate::authorize('review', $propertyPhoto->property);

        $disk = Storage::disk('local');
        $path = $propertyPhoto->file_path;

        abort_unless(filled($path) && $disk->exists($path), 404);

        return $disk->response($path, basename($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
