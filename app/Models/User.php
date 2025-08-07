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
        'activation_token',
        'activation_token_expires_at',
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
            'activation_token_expires_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            \Log::info('Usuario creado, disparando evento UserRegistered', [
                'user_id' => $user->id,
                'user_email' => $user->user,
                'registration_type' => $user->registration_type,
                'timestamp' => now()->toDateTimeString(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ]);
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