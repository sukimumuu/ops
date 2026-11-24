<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $guarded = ['id'];

    /**
     * Predefined payout banks keyed by the value stored in `users.bank_name`.
     *
     * @var array<string, array{label: string, logo: string}>
     */
    public const BANK_OPTIONS = [
        'ANZ' => ['label' => 'ANZ', 'logo' => 'assets/bank-logos/ANZ.svg'],
        'Aladin' => ['label' => 'Aladin', 'logo' => 'assets/bank-logos/Aladin.svg'],
        'Allo' => ['label' => 'Allo', 'logo' => 'assets/bank-logos/Allo.svg'],
        'Amar Bank' => ['label' => 'Amar Bank', 'logo' => 'assets/bank-logos/Amar%20Bank.svg'],
        'BCA Digital' => ['label' => 'BCA Digital', 'logo' => 'assets/bank-logos/BCA%20Digital.svg'],
        'BCA Syariah' => ['label' => 'BCA Syariah', 'logo' => 'assets/bank-logos/BCA%20Syariah.svg'],
        'BCA' => ['label' => 'BCA', 'logo' => 'assets/bank-logos/BCA.svg'],
        'BNC' => ['label' => 'BNC', 'logo' => 'assets/bank-logos/BNC.svg'],
        'BNI' => ['label' => 'BNI', 'logo' => 'assets/bank-logos/BNI.svg'],
        'BRI' => ['label' => 'BRI', 'logo' => 'assets/bank-logos/BRI.svg'],
        'BSI' => ['label' => 'BSI', 'logo' => 'assets/bank-logos/BSI.svg'],
        'BSN' => ['label' => 'BSN', 'logo' => 'assets/bank-logos/BSN%20(Alt).svg'],
        'BTN' => ['label' => 'BTN', 'logo' => 'assets/bank-logos/BTN%20(New).svg'],
        'BTN Syariah' => ['label' => 'BTN Syariah', 'logo' => 'assets/bank-logos/BTN%20Syariah%20(New).svg'],
        'BTPN Syariah' => ['label' => 'BTPN Syariah', 'logo' => 'assets/bank-logos/BTPN%20Syariah.svg'],
        'BTPN' => ['label' => 'BTPN', 'logo' => 'assets/bank-logos/BTPN.svg'],
        'Bank Artha Graha Internasional' => ['label' => 'Bank Artha Graha Internasional', 'logo' => 'assets/bank-logos/Bank%20Artha%20Graha%20Internasional.svg'],
        'Bank Bumi Artha' => ['label' => 'Bank Bumi Artha', 'logo' => 'assets/bank-logos/Bank%20Bumi%20Artha.svg'],
        'Bank Capital' => ['label' => 'Bank Capital', 'logo' => 'assets/bank-logos/Bank%20Capital.svg'],
        'Bank Ganesha' => ['label' => 'Bank Ganesha', 'logo' => 'assets/bank-logos/Bank%20Ganesha.svg'],
        'Bank INA' => ['label' => 'Bank INA', 'logo' => 'assets/bank-logos/Bank%20INA.svg'],
        'Bank Index Selindo' => ['label' => 'Bank Index Selindo', 'logo' => 'assets/bank-logos/Bank%20Index%20Selindo.svg'],
        'Bank Jasa Jakarta' => ['label' => 'Bank Jasa Jakarta', 'logo' => 'assets/bank-logos/Bank%20Jasa%20Jakarta.svg'],
        'Bank Mas' => ['label' => 'Bank Mas', 'logo' => 'assets/bank-logos/Bank%20Mas.svg'],
        'Bank Maspion' => ['label' => 'Bank Maspion', 'logo' => 'assets/bank-logos/Bank%20Maspion.svg'],
        'Bank Mayapada' => ['label' => 'Bank Mayapada', 'logo' => 'assets/bank-logos/Bank%20Mayapada.svg'],
        'Bank Mayora' => ['label' => 'Bank Mayora', 'logo' => 'assets/bank-logos/Bank%20Mayora.svg'],
        'Bank Mestika Dharma' => ['label' => 'Bank Mestika Dharma', 'logo' => 'assets/bank-logos/Bank%20Mestika%20Dharma.svg'],
        'Bank Raya' => ['label' => 'Bank Raya', 'logo' => 'assets/bank-logos/Bank%20Raya.svg'],
        'Bank Resona Perdania' => ['label' => 'Bank Resona Perdania', 'logo' => 'assets/bank-logos/Bank%20Resona%20Perdania.svg'],
        'Bank SMBC Indonesia' => ['label' => 'Bank SMBC Indonesia', 'logo' => 'assets/bank-logos/Bank%20SMBC%20Indonesia.svg'],
        'Bank Sahabat Sampoerna' => ['label' => 'Bank Sahabat Sampoerna', 'logo' => 'assets/bank-logos/Bank%20Sahabat%20Sampoerna.svg'],
        'Bank Saqu' => ['label' => 'Bank Saqu', 'logo' => 'assets/bank-logos/Bank%20Saqu.svg'],
        'Bank Victoria Syariah' => ['label' => 'Bank Victoria Syariah', 'logo' => 'assets/bank-logos/Bank%20Victoria%20Syariah.svg'],
        'Bank Victoria' => ['label' => 'Bank Victoria', 'logo' => 'assets/bank-logos/Bank%20Victoria.svg'],
        'Bank Woori Saudara' => ['label' => 'Bank Woori Saudara', 'logo' => 'assets/bank-logos/Bank%20Woori%20Saudara.svg'],
        'Blu BCA' => ['label' => 'Blu BCA', 'logo' => 'assets/bank-logos/Blu%20BCA.svg'],
        'CCB Indonesia' => ['label' => 'CCB Indonesia', 'logo' => 'assets/bank-logos/CCB%20Indonesia.svg'],
        'CIMB Niaga' => ['label' => 'CIMB Niaga', 'logo' => 'assets/bank-logos/CIMB%20Niaga.svg'],
        'CTBC Bank' => ['label' => 'CTBC Bank', 'logo' => 'assets/bank-logos/CTBC%20Bank.svg'],
        'DBS' => ['label' => 'DBS', 'logo' => 'assets/bank-logos/DBS.svg'],
        'Danamon Syariah' => ['label' => 'Danamon Syariah', 'logo' => 'assets/bank-logos/Danamon%20Syariah.svg'],
        'Danamon' => ['label' => 'Danamon', 'logo' => 'assets/bank-logos/Danamon.svg'],
        'HSBC' => ['label' => 'HSBC', 'logo' => 'assets/bank-logos/HSBC.svg'],
        'Jago' => ['label' => 'Jago', 'logo' => 'assets/bank-logos/Jago.svg'],
        'Jenius' => ['label' => 'Jenius', 'logo' => 'assets/bank-logos/Jenius.svg'],
        'KB Bukopin' => ['label' => 'KB Bukopin', 'logo' => 'assets/bank-logos/KB%20Bukopin.svg'],
        'Krom' => ['label' => 'Krom', 'logo' => 'assets/bank-logos/Krom.svg'],
        'LINE Bank' => ['label' => 'LINE Bank', 'logo' => 'assets/bank-logos/LINE%20Bank.svg'],
        'MNC' => ['label' => 'MNC', 'logo' => 'assets/bank-logos/MNC.svg'],
        'Mandiri' => ['label' => 'Mandiri', 'logo' => 'assets/bank-logos/Mandiri.svg'],
        'Mandiri Taspen' => ['label' => 'Mandiri Taspen', 'logo' => 'assets/bank-logos/Mandiri%20Taspen.svg'],
        'Maybank' => ['label' => 'Maybank', 'logo' => 'assets/bank-logos/Maybank.svg'],
        'Mega' => ['label' => 'Mega', 'logo' => 'assets/bank-logos/Mega.svg'],
        'Mualamat' => ['label' => 'Mualamat', 'logo' => 'assets/bank-logos/Mualamat.svg'],
        'NOBU' => ['label' => 'NOBU', 'logo' => 'assets/bank-logos/NOBU.svg'],
        'PaninBank' => ['label' => 'PaninBank', 'logo' => 'assets/bank-logos/PaninBank.svg'],
        'Permata Bank' => ['label' => 'Permata Bank', 'logo' => 'assets/bank-logos/Permata%20Bank%20(New).svg'],
        'Permata' => ['label' => 'Permata', 'logo' => 'assets/bank-logos/Permata.svg'],
        'SeaBank' => ['label' => 'SeaBank', 'logo' => 'assets/bank-logos/SeaBank.svg'],
        'Sinarmas Syariah' => ['label' => 'Sinarmas Syariah', 'logo' => 'assets/bank-logos/Sinarmas%20Syariah.svg'],
        'Sinarmas' => ['label' => 'Sinarmas', 'logo' => 'assets/bank-logos/Sinarmas.svg'],
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'bank_account_updated_at' => 'datetime',
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

    /**
     * Determine whether the user has supplied a complete bank account for escrow payouts.
     */
    public function hasDisbursementAccount(): bool
    {
        return filled($this->bank_name)
            && filled($this->bank_account_number)
            && filled($this->bank_account_holder_name);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'seller_id');
    }

    public function propertyDrafts(): HasMany
    {
        return $this->hasMany(PropertyDraft::class, 'seller_id');
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
