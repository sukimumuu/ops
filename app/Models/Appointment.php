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

    public const BUYER_INTENT_DEAL = 'deal';

    public const BUYER_INTENT_NO_DEAL = 'no_deal';

    /**
     * All supported buyer decisions after a survey.
     *
     * @var list<string>
     */
    public const BUYER_INTENTS = [
        self::BUYER_INTENT_DEAL,
        self::BUYER_INTENT_NO_DEAL,
    ];

    /**
     * Available time slots for a property visit.
     *
     * @var list<string>
     */
    public const TIME_SLOTS = ['09:00', '13:00', '15:30'];

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            if ($appointment->property_id === null) {
                return;
            }

            $ownerId = Property::query()
                ->whereKey($appointment->property_id)
                ->value('seller_id');

            if ($ownerId !== null) {
                $appointment->seller_id = $ownerId;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'reschedule_suggested_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function buyerIntentLabel(): ?string
    {
        return match ($this->buyer_intent) {
            self::BUYER_INTENT_DEAL => 'Berminat membeli',
            self::BUYER_INTENT_NO_DEAL => 'Tidak melanjutkan pembelian',
            default => null,
        };
    }

    /**
     * Describe the survey progress represented by this appointment.
     *
     * @return array{label: string, description: string, percentage: int, tone: string, completed_steps: int, stopped: bool}
     */
    public function progressState(): array
    {
        if ($this->hasPendingReschedule()) {
            return [
                'label' => 'Menunggu jadwal pengganti',
                'description' => 'Jadwal pengganti sudah dikirim dan menunggu konfirmasi calon pembeli.',
                'percentage' => 33,
                'tone' => 'amber',
                'completed_steps' => 1,
                'stopped' => false,
            ];
        }

        return match ($this->status) {
            self::STATUS_REQUESTED => [
                'label' => 'Menunggu konfirmasi survei',
                'description' => 'Permintaan survei sudah masuk dan menunggu persetujuan Anda.',
                'percentage' => 33,
                'tone' => 'amber',
                'completed_steps' => 1,
                'stopped' => false,
            ],
            self::STATUS_CONFIRMED => [
                'label' => 'Survei terjadwal',
                'description' => 'Jadwal survei sudah dikonfirmasi. Catat hasilnya setelah survei dilaksanakan.',
                'percentage' => 66,
                'tone' => 'sky',
                'completed_steps' => 2,
                'stopped' => false,
            ],
            self::STATUS_COMPLETED => [
                'label' => 'Survei selesai',
                'description' => 'Hasil survei sudah dicatat dan tidak mengubah status tayang properti.',
                'percentage' => 100,
                'tone' => 'emerald',
                'completed_steps' => 3,
                'stopped' => false,
            ],
            self::STATUS_REJECTED => [
                'label' => 'Survei tidak dilanjutkan',
                'description' => 'Permintaan survei ditolak dan tidak ada jadwal pengganti yang aktif.',
                'percentage' => 33,
                'tone' => 'rose',
                'completed_steps' => 1,
                'stopped' => true,
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Survei dibatalkan',
                'description' => 'Survei ini dibatalkan sebelum hasilnya dicatat.',
                'percentage' => 33,
                'tone' => 'slate',
                'completed_steps' => 1,
                'stopped' => true,
            ],
            default => [
                'label' => 'Status survei tidak diketahui',
                'description' => 'Progres survei belum dapat ditampilkan.',
                'percentage' => 0,
                'tone' => 'slate',
                'completed_steps' => 0,
                'stopped' => true,
            ],
        };
    }

    public function isAwaitingReview(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function hasPendingReschedule(): bool
    {
        return $this->status === self::STATUS_REJECTED && $this->reschedule_suggested_at !== null;
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
