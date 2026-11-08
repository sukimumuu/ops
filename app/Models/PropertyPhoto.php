<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPhoto extends Model
{
    public const TYPE_PHOTO = 'photo';

    public const TYPE_CERTIFICATE = 'certificate';

    public const TYPE_IMB = 'imb';

    public const TYPE_PBB = 'pbb';

    /**
     * Document types that participate in the BPN legal check.
     *
     * @var list<string>
     */
    public const DOCUMENT_TYPES = [
        self::TYPE_CERTIFICATE,
        self::TYPE_IMB,
        self::TYPE_PBB,
    ];

    public const VALIDATION_PENDING = 'pending';

    public const VALIDATION_APPROVED = 'approved';

    public const VALIDATION_REJECTED = 'rejected';

    /**
     * All supported document validation statuses.
     *
     * @var list<string>
     */
    public const VALIDATION_STATUSES = [
        self::VALIDATION_PENDING,
        self::VALIDATION_APPROVED,
        self::VALIDATION_REJECTED,
    ];

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_CERTIFICATE => 'Sertifikat',
            self::TYPE_IMB => 'IMB / PBG',
            self::TYPE_PBB => 'SPPT / STTS PBB',
            self::TYPE_PHOTO => 'Foto properti',
            default => ucfirst((string) $this->type),
        };
    }

    public function validationStatusLabel(): string
    {
        return match ($this->validation_status) {
            self::VALIDATION_APPROVED => 'Disetujui',
            self::VALIDATION_REJECTED => 'Ditolak',
            default => 'Menunggu validasi',
        };
    }

    public function isApproved(): bool
    {
        return $this->validation_status === self::VALIDATION_APPROVED;
    }
}
