<?php

namespace App\Models;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TransactionLogs extends Model
{
    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
