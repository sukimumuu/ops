<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyReviewController extends Controller
{
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
