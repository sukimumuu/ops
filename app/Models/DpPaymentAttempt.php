<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DpPaymentAttempt extends Model
{
    public const STATUS_CREATING = 'creating';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELED = 'canceled';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'provider_response' => 'array',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(TransactionLedgerEntry::class);
    }
}
