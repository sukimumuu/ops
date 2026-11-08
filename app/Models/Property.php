<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    protected $guarded = ['id'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function assignedPpat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_ppat_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->where('type', 'photo')->orderBy('sort_order');
    }

    /**
     * Resolve the public URL of the property's primary photo.
     *
     * Photos live on the public disk, but legacy uploads may still live on the
     * local disk, so both locations are checked before falling back to null.
     */
    public function primaryPhotoUrl(): ?string
    {
        $photo = $this->relationLoaded('photos')
            ? $this->photos->first()
            : $this->photos()->first();

        if (! $photo) {
            return null;
        }

        $publicDisk = Storage::disk('public');

        if ($publicDisk->exists($photo->file_path)) {
            return $publicDisk->url($photo->file_path);
        }

        $localDisk = Storage::disk('local');

        return $localDisk->exists($photo->file_path)
            ? $localDisk->temporaryUrl($photo->file_path, now()->addHour())
            : null;
    }

    public function certificate(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)
            ->where('type', 'certificate')
            ->orderBy('sort_order');
    }

    /**
     * Supporting legal documents (IMB/PBG and SPPT/STTS PBB).
     */
    public function supportingDocuments(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)
            ->whereIn('type', ['imb', 'pbb'])
            ->orderBy('sort_order');
    }

    /**
     * All legal documents reviewed during the BPN legal check.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)
            ->whereIn('type', PropertyPhoto::DOCUMENT_TYPES)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Summarize the PPAT document verification progress for this property.
     *
     * @return array{
     *     documents: Collection<int, PropertyPhoto>,
     *     total: int,
     *     pending: int,
     *     approved: int,
     *     rejected: int,
     *     percentage: int,
     *     status: string,
     *     label: string,
     *     description: string,
     *     tone: string,
     *     latest_validated_at: ?Carbon
     * }
     */
    public function bpnVerificationProgress(): array
    {
        $documents = $this->relationLoaded('documents')
            ? $this->documents
            : $this->documents()->with('validatedBy:id,name')->get();

        $total = $documents->count();
        $approved = $documents->filter(
            fn (PropertyPhoto $document): bool => $document->validation_status === PropertyPhoto::VALIDATION_APPROVED,
        )->count();
        $rejected = $documents->filter(
            fn (PropertyPhoto $document): bool => $document->validation_status === PropertyPhoto::VALIDATION_REJECTED,
        )->count();
        $pending = max(0, $total - $approved - $rejected);
        $percentage = $total > 0 ? (int) round(($approved / $total) * 100) : 0;

        $latestValidatedDocument = $documents
            ->filter(fn (PropertyPhoto $document): bool => $document->validated_at !== null)
            ->sortByDesc('validated_at')
            ->first();

        [$status, $label, $description, $tone] = match (true) {
            $total === 0 => [
                'empty',
                'Belum ada dokumen',
                'Dokumen legal belum diunggah oleh penjual.',
                'slate',
            ],
            $rejected > 0 => [
                'needs_revision',
                'Perlu perbaikan',
                'PPAT menolak sebagian dokumen. Periksa catatan validasi dan unggah perbaikan.',
                'rose',
            ],
            $approved === $total => [
                'completed',
                'Pengecekan selesai',
                'Seluruh dokumen legal telah disetujui PPAT.',
                'emerald',
            ],
            $approved > 0 => [
                'in_progress',
                'Sedang diverifikasi PPAT',
                'Sebagian dokumen telah disetujui dan sisanya masih menunggu pengecekan.',
                'amber',
            ],
            default => [
                'pending',
                'Menunggu validasi PPAT',
                'Dokumen telah diunggah dan menunggu pengecekan oleh PPAT.',
                'amber',
            ],
        };

        return [
            'documents' => $documents,
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'percentage' => $percentage,
            'status' => $status,
            'label' => $label,
            'description' => $description,
            'tone' => $tone,
            'latest_validated_at' => $latestValidatedDocument?->validated_at,
        ];
    }

    /**
     * Users who bookmarked this property.
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_properties')
            ->withTimestamps();
    }
}
