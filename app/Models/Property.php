<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    protected $guarded = ['id'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
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
     * Users who bookmarked this property.
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_properties')
            ->withTimestamps();
    }
}
