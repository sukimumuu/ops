<?php

namespace App\Models;

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
