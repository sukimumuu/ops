<?php

namespace App\Models;

use App\Models\Appointment;
use App\Models\PropertyPhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->where('type', 'photo')->orderBy('sort_order');
    }

    public function certificate(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->where('type', 'certificate');
    }
}
