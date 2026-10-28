<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebPushSubscription extends Model
{
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'endpoint' => 'encrypted',
            'public_key' => 'encrypted',
            'auth_token' => 'encrypted',
        ];
    }
}
