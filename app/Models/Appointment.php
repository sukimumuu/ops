<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * All supported survey booking statuses.
     *
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_CONFIRMED,
        self::STATUS_REJECTED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Statuses that a reviewer may transition a pending request into.
     *
     * @var list<string>
     */
    public const REVIEW_STATUSES = [
        self::STATUS_CONFIRMED,
        self::STATUS_REJECTED,
    ];

    /**
     * Available time slots for a property visit.
     *
     * @var list<string>
     */
    public const TIME_SLOTS = ['09:00', '13:00', '15:30'];

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public static function labelFor(string $status): string
    {
        return match ($status) {
            self::STATUS_REQUESTED => 'Menunggu persetujuan',
            self::STATUS_CONFIRMED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    public function statusLabel(): string
    {
        return self::labelFor((string) $this->status);
    }

    public function isAwaitingReview(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
