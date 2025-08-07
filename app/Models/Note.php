<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'title'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getByUser($userId)
    {
        $cacheKey = "notes_user_{$userId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            return static::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public static function getLatestByUser($userId)
    {
        $cacheKey = "notes_latest_user_{$userId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($userId) {
            return static::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();
        });
    }

    public static function getByUserPaginated($userId, $perPage = 10)
    {
        $cacheKey = "notes_paginated_user_{$userId}_page_" . request()->get('page', 1);
        
        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($userId, $perPage) {
            return static::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    public static function searchByUser($userId, $query)
    {
        return static::where('user_id', $userId)
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getStatsByUser($userId)
    {
        $cacheKey = "notes_stats_user_{$userId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            return [
                'total' => static::where('user_id', $userId)->count(),
                'this_month' => static::where('user_id', $userId)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'this_week' => static::where('user_id', $userId)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'today' => static::where('user_id', $userId)
                    ->whereDate('created_at', today())
                    ->count()
            ];
        });
    }

    protected static function booted()
    {
        static::created(function ($note) {
            static::clearUserCache($note->user_id);
        });

        static::updated(function ($note) {
            static::clearUserCache($note->user_id);
        });

        static::deleted(function ($note) {
            static::clearUserCache($note->user_id);
        });
    }

    private static function clearUserCache($userId)
    {
        Cache::forget("notes_user_{$userId}");
        Cache::forget("notes_latest_user_{$userId}");
        Cache::forget("notes_stats_user_{$userId}");
        
        // Limpiar cache de paginación (primeras 5 páginas)
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("notes_paginated_user_{$userId}_page_{$i}");
        }
    }

    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeWithContent(Builder $query, $search)
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }
}