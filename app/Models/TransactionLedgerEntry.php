<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLedgerEntry extends Model
{
    public const TYPE_DP = 'dp';

    public const TYPE_FINAL = 'final';

    public const STATUS_RESERVED = 'reserved';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function dpPaymentAttempt(): BelongsTo
    {
        return $this->belongsTo(DpPaymentAttempt::class);
    }
}
