<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasUuids;
    protected $primaryKey = 'uuid';
}
