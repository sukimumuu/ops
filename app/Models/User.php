<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLogs;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if(empty($model->username)) {
                $model->username = strtolower(str_replace(' ', '', $model->name));
            }
        });
    }
    
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                $cleaned = preg_replace('/\D/', '', $value);
                if (str_starts_with($cleaned, '0')) {
                    $cleaned = substr($cleaned, 1);
                }
                if (str_starts_with($cleaned, '62')) {
                    $cleaned = substr($cleaned, 2);
                }
                return '62' . $cleaned;
            },
        );
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function appointmentsAsBuyer(): HasMany
    {
        return $this->hasMany(Appointment::class, 'buyer_id');
    }

    public function appointmentsAsSeller(): HasMany
    {
        return $this->hasMany(Appointment::class, 'seller_id');
    }

    public function transactionsAsBuyer(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function transactionsAsSeller(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function transactionLogs(): HasMany
    {
        return $this->hasMany(TransactionLogs::class, 'actor_id');
    }
}
