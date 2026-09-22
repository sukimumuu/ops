<?php

namespace App\Models;

use App\Models\Property;
use App\Models\TransactionLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = ['id'];

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

    public function logs()
    {
        return $this->hasMany(TransactionLogs::class);
    }
}
