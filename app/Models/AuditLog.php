<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const CATEGORY_AUTHENTICATION = 'authentication';

    public const CATEGORY_TRANSACTION = 'transaction';

    public const CATEGORY_SCHEDULING = 'scheduling';

    public const CATEGORY_MODERATION = 'moderation';

    public const CATEGORY_ADMINISTRATION = 'administration';

    public const CATEGORY_SYSTEM = 'system';

    /**
     * Human readable labels for every audit category.
     *
     * @var array<string, string>
     */
    public const CATEGORY_LABELS = [
        self::CATEGORY_AUTHENTICATION => 'Autentikasi',
        self::CATEGORY_TRANSACTION => 'Transaksi',
        self::CATEGORY_SCHEDULING => 'Penjadwalan',
        self::CATEGORY_MODERATION => 'Moderasi Properti',
        self::CATEGORY_ADMINISTRATION => 'Administrasi',
        self::CATEGORY_SYSTEM => 'Sistem',
    ];

    public const EVENT_AUTH_LOGIN = 'auth.login';

    public const EVENT_AUTH_LOGOUT = 'auth.logout';

    public const EVENT_AUTH_FAILED = 'auth.failed';

    public const EVENT_TRANSACTION_CREATED = 'transaction.created';

    public const EVENT_TRANSACTION_DP_INITIATED = 'transaction.dp_initiated';

    public const EVENT_TRANSACTION_DP_PAID = 'transaction.dp_paid';

    public const EVENT_TRANSACTION_ESCROW_INITIATED = 'transaction.escrow_initiated';

    public const EVENT_TRANSACTION_ESCROW_PAID = 'transaction.escrow_paid';

    public const EVENT_TRANSACTION_CERTIFICATE_TRANSFERRED = 'transaction.certificate_transferred';

    public const EVENT_PAYOUT_INITIATED = 'payout.initiated';

    public const EVENT_PAYOUT_CONFIRMED = 'payout.confirmed';

    public const EVENT_APPOINTMENT_CREATED = 'appointment.created';

    public const EVENT_APPOINTMENT_REVIEWED = 'appointment.reviewed';

    public const EVENT_APPOINTMENT_RESULT_RECORDED = 'appointment.result_recorded';

    public const EVENT_APPOINTMENT_RESCHEDULED = 'appointment.rescheduled';

    public const EVENT_APPOINTMENT_PPAT_MEETING_REQUESTED = 'appointment.ppat_meeting_requested';

    public const EVENT_APPOINTMENT_PPAT_MEETING_RESPONDED = 'appointment.ppat_meeting_responded';

    public const EVENT_APPOINTMENT_AJB_MEETING_REQUESTED = 'appointment.ajb_meeting_requested';

    public const EVENT_APPOINTMENT_AJB_MEETING_RESPONDED = 'appointment.ajb_meeting_responded';

    public const EVENT_APPOINTMENT_AJB_RESCHEDULE_RESPONDED = 'appointment.ajb_reschedule_responded';

    public const EVENT_PROPERTY_SUBMITTED = 'property.submitted';

    public const EVENT_PROPERTY_APPROVED = 'property.approved';

    public const EVENT_PROPERTY_REJECTED = 'property.rejected';

    public const EVENT_PROPERTY_PPAT_ASSIGNED = 'property.ppat_assigned';

    public const EVENT_USER_CREATED = 'user.created';

    public const EVENT_ROLE_PERMISSIONS_UPDATED = 'role.permissions_updated';

    /**
     * Human readable labels for every recorded event.
     *
     * @var array<string, string>
     */
    public const EVENT_LABELS = [
        self::EVENT_AUTH_LOGIN => 'Masuk platform',
        self::EVENT_AUTH_LOGOUT => 'Keluar platform',
        self::EVENT_AUTH_FAILED => 'Percobaan masuk gagal',
        self::EVENT_TRANSACTION_CREATED => 'Transaksi dibuat',
        self::EVENT_TRANSACTION_DP_INITIATED => 'Pembayaran DP dimulai',
        self::EVENT_TRANSACTION_DP_PAID => 'Pembayaran DP terkonfirmasi',
        self::EVENT_TRANSACTION_ESCROW_INITIATED => 'Pembayaran pelunasan dimulai',
        self::EVENT_TRANSACTION_ESCROW_PAID => 'Pelunasan escrow terkonfirmasi',
        self::EVENT_TRANSACTION_CERTIFICATE_TRANSFERRED => 'Tanda terima BPN diunggah',
        self::EVENT_PAYOUT_INITIATED => 'Pencairan escrow diajukan',
        self::EVENT_PAYOUT_CONFIRMED => 'Pencairan escrow dikonfirmasi',
        self::EVENT_APPOINTMENT_CREATED => 'Jadwal survei diajukan',
        self::EVENT_APPOINTMENT_REVIEWED => 'Jadwal survei ditinjau',
        self::EVENT_APPOINTMENT_RESULT_RECORDED => 'Hasil survei dicatat',
        self::EVENT_APPOINTMENT_RESCHEDULED => 'Jadwal survei diubah',
        self::EVENT_APPOINTMENT_PPAT_MEETING_REQUESTED => 'Jadwal temu PPAT diajukan',
        self::EVENT_APPOINTMENT_PPAT_MEETING_RESPONDED => 'Jadwal temu PPAT ditanggapi',
        self::EVENT_APPOINTMENT_AJB_MEETING_REQUESTED => 'Jadwal AJB diajukan',
        self::EVENT_APPOINTMENT_AJB_MEETING_RESPONDED => 'Jadwal AJB ditanggapi',
        self::EVENT_APPOINTMENT_AJB_RESCHEDULE_RESPONDED => 'Jadwal pengganti AJB ditanggapi',
        self::EVENT_PROPERTY_SUBMITTED => 'Properti diajukan verifikasi',
        self::EVENT_PROPERTY_APPROVED => 'Properti disetujui',
        self::EVENT_PROPERTY_REJECTED => 'Properti ditolak',
        self::EVENT_PROPERTY_PPAT_ASSIGNED => 'PPAT ditugaskan',
        self::EVENT_USER_CREATED => 'Akun pengguna dibuat',
        self::EVENT_ROLE_PERMISSIONS_UPDATED => 'Hak akses role diperbarui',
    ];

    /**
     * Human readable labels for the audited entity types.
     *
     * @var array<string, string>
     */
    public const SUBJECT_LABELS = [
        'Property' => 'Properti',
        'Appointment' => 'Jadwal',
        'Transaction' => 'Transaksi',
        'EscrowDisbursement' => 'Pencairan Escrow',
        'User' => 'Pengguna',
        'Role' => 'Role',
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
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when(
            filled($category),
            fn (Builder $query): Builder => $query->where('category', $category),
        );
    }

    public function scopeEvent(Builder $query, ?string $event): Builder
    {
        return $query->when(
            filled($event),
            fn (Builder $query): Builder => $query->where('event', $event),
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', function (Builder $query) use ($term): void {
            $query->where(function (Builder $query) use ($term): void {
                $query->where('description', 'like', "%{$term}%")
                    ->orWhere('actor_name', 'like', "%{$term}%")
                    ->orWhere('actor_email', 'like', "%{$term}%")
                    ->orWhere('subject_label', 'like', "%{$term}%")
                    ->orWhere('event', 'like', "%{$term}%");
            });
        });
    }

    public static function categoryLabel(?string $category): string
    {
        return self::CATEGORY_LABELS[$category] ?? Str::headline((string) $category);
    }

    public static function eventLabelFor(?string $event): string
    {
        return self::EVENT_LABELS[$event] ?? Str::headline((string) $event);
    }

    public function eventLabel(): string
    {
        return self::eventLabelFor($this->event);
    }

    public function subjectTypeLabel(): ?string
    {
        if ($this->subject_type === null) {
            return null;
        }

        $basename = class_basename($this->subject_type);

        return self::SUBJECT_LABELS[$basename] ?? Str::headline($basename);
    }

    /**
     * Indicates whether the entry is restricted to superadmin visibility.
     */
    public function isRestricted(): bool
    {
        return $this->category === self::CATEGORY_SYSTEM;
    }
}
