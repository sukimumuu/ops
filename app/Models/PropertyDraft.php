<?php

namespace App\Models;

use Database\Factories\PropertyDraftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDraft extends Model
{
    /** @use HasFactory<PropertyDraftFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'seller_id',
        'payload',
        'uploads',
        'current_step',
        'last_saved_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'uploads' => 'array',
            'current_step' => 'integer',
            'last_saved_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->withTrashed();
    }
}
