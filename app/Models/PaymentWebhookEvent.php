<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhookEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function dpPaymentAttempt(): BelongsTo
    {
        return $this->belongsTo(DpPaymentAttempt::class);
    }
}
