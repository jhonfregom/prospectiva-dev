<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StateUser extends Model
{
    use HasFactory;
    protected $table = "status_users";

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_SUSPENDED = 3;
    const STATUS_LOCKED = 4;
    const STATUS_DELETED = 5;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'status_users_id');
    }
}