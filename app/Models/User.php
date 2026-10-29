<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
            if (empty($model->username)) {
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

                return '62'.$cleaned;
            },
        );
    }

    /**
     * Returns the publicly accessible avatar URL.
     * Falls back to a generated initial-based avatar when no custom photo is set.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->avatar_path && Storage::disk('public')->exists($this->avatar_path)) {
                    return Storage::disk('public')->url($this->avatar_path);
                }

                return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=ea580c&color=fff&size=256&bold=true';
            },
        );
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'seller_id');
    }

    /**
     * Properties bookmarked by the user, ordered by when they saved them.
     */
    public function savedProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'saved_properties')
            ->withTimestamps();
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

    public function webPushSubscriptions(): HasMany
    {
        return $this->hasMany(WebPushSubscription::class);
    }

    /**
     * @return Collection<int, WebPushSubscription>
     */
    public function routeNotificationForWebPush(): Collection
    {
        return $this->webPushSubscriptions()->get();
    }
}
