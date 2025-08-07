<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'title'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getByUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getLatestByUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}