<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
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
        'completed',
    ];

    protected $guarded = ['id'];

    public function hasCompletedDownPayment(): bool
    {
        return in_array((string) $this->state, self::DP_PAID_STATES, true);
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
}
