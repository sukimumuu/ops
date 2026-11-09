<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    public const STATE_CERTIFICATE_TRANSFER_PENDING = 'certificate_transfer_pending';

    public const STATE_COMPLETED = 'completed';

    /**
     * States where the down payment has been confirmed and Stage 3 is unlocked.
     *
     * @var list<string>
     */
    public const DP_PAID_STATES = [
        'dp_paid',
        'held_in_escrow',
        'bpn_checking',
        'bpn_cleared',
        'ajb_scheduled',
        self::STATE_CERTIFICATE_TRANSFER_PENDING,
        self::STATE_COMPLETED,
    ];

    /**
     * States where the escrow final payment has been confirmed and Stage 5 is ready.
     *
     * @var list<string>
     */
    public const ESCROW_PAID_STATES = [
        'held_in_escrow',
        'ajb_scheduled',
        self::STATE_CERTIFICATE_TRANSFER_PENDING,
        self::STATE_COMPLETED,
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
            'paid_at' => 'datetime',
            'settled_at' => 'datetime',
            'ajb_signed_at' => 'datetime',
            'certificate_transfer_submitted_at' => 'datetime',
        ];
    }

    public function hasCompletedDownPayment(): bool
    {
        return in_array((string) $this->state, self::DP_PAID_STATES, true);
    }

    public function hasCompletedEscrowPayment(): bool
    {
        return in_array((string) $this->state, self::ESCROW_PAID_STATES, true);
    }

    public function hasCompletedCertificateTransfer(): bool
    {
        return $this->state === self::STATE_COMPLETED
            && filled($this->certificate_transfer_receipt_path);
    }

    public function isStageFourUnlocked(): bool
    {
        return in_array((string) $this->state, [
            'bpn_cleared',
            'held_in_escrow',
            'ajb_scheduled',
            self::STATE_CERTIFICATE_TRANSFER_PENDING,
            self::STATE_COMPLETED,
        ], true);
    }

    public function isStageFiveUnlocked(): bool
    {
        return $this->hasCompletedEscrowPayment();
    }

    public function ajbDocumentUrl(): ?string
    {
        if (empty($this->ajb_document_path)) {
            return null;
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($this->ajb_document_path)) {
            return $publicDisk->url($this->ajb_document_path);
        }

        $localDisk = Storage::disk('local');
        if ($localDisk->exists($this->ajb_document_path)) {
            return $localDisk->temporaryUrl($this->ajb_document_path, now()->addHour());
        }

        return $publicDisk->url($this->ajb_document_path);
    }

    /**
     * Get the confirmed down payment amount from confirmed payment attempts or ledger entries.
     */
    public function confirmedDpAmount(): float
    {
        $ledgerAmount = (float) $this->ledgerEntries()
            ->whereHas('dpPaymentAttempt', fn ($query) => $query->where(function ($q): void {
                $q->where('payment_type', DpPaymentAttempt::TYPE_DP)
                    ->orWhereNull('payment_type');
            }))
            ->sum('amount');

        if ($ledgerAmount > 0) {
            return $ledgerAmount;
        }

        return (float) $this->dpPaymentAttempts()
            ->where(function ($q): void {
                $q->where('payment_type', DpPaymentAttempt::TYPE_DP)
                    ->orWhereNull('payment_type');
            })
            ->where('status', DpPaymentAttempt::STATUS_PAID)
            ->sum('amount');
    }

    /**
     * Calculate outstanding balance automatically based on total property price minus confirmed DP.
     */
    public function remainingEscrowAmount(): int
    {
        $total = BigDecimal::of((string) $this->total_property_amount);
        $confirmedDp = BigDecimal::of((string) $this->confirmedDpAmount());
        $remaining = $total->minus($confirmedDp);

        return max(0, (int) (string) $remaining);
    }

    public function latestDpPaymentAttempt(): ?DpPaymentAttempt
    {
        return $this->dpPaymentAttempts()
            ->where(function ($query): void {
                $query->where('payment_type', DpPaymentAttempt::TYPE_DP)
                    ->orWhereNull('payment_type');
            })
            ->latest('id')
            ->first();
    }

    public function latestEscrowPaymentAttempt(): ?DpPaymentAttempt
    {
        return $this->dpPaymentAttempts()
            ->where('payment_type', DpPaymentAttempt::TYPE_FINAL)
            ->latest('id')
            ->first();
    }

    public function escrowPaymentAttempts(): HasMany
    {
        return $this->hasMany(DpPaymentAttempt::class)->where('payment_type', DpPaymentAttempt::TYPE_FINAL);
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

    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLogs::class);
    }

    public function dpPaymentAttempts(): HasMany
    {
        return $this->hasMany(DpPaymentAttempt::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TransactionLedgerEntry::class);
    }

    public function escrowDisbursements(): HasMany
    {
        return $this->hasMany(EscrowDisbursement::class);
    }

    public function latestEscrowDisbursement(): ?EscrowDisbursement
    {
        return $this->escrowDisbursements()->latest('id')->first();
    }

    public function certificateTransferSubmittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certificate_transfer_submitted_by');
    }
}
