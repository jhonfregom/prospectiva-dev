<?php

namespace App\Models;

use App\Events\UserRegistered;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'user_type',
        'company_name',
        'nit',
        'city_region',
        'economic_sector',
        'data_authorization',
        'document_id',
        'city',
        'registration_type',
        'first_name',
        'last_name',
        'user',
        'password',
        'password_reset_token',
        'password_reset_expires_at',
        'role',
        'status_users_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            event(new UserRegistered($user));
        });
    }

    public function stateUser(): BelongsTo
    {
        return $this->belongsTo(StateUser::class, 'status_users_id');
    }

    public function economicSector(): BelongsTo
    {
        return $this->belongsTo(EconomicSector::class, 'economic_sector');
    }

    public $incrementing = false;
}