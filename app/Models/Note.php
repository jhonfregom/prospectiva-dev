<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'traceability_id',
        'content',
        'title'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la trazabilidad (ruta)
     */
    public function traceability()
    {
        return $this->belongsTo(Traceability::class);
    }

    /**
     * Obtener notas por usuario y ruta
     */
    public static function getByUserAndRoute($userId, $traceabilityId = null)
    {
        $query = static::where('user_id', $userId);
        
        if ($traceabilityId) {
            $query->where('traceability_id', $traceabilityId);
        } else {
            $query->whereNull('traceability_id');
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Obtener la nota más reciente del usuario
     */
    public static function getLatestByUser($userId, $traceabilityId = null)
    {
        $query = static::where('user_id', $userId);
        
        if ($traceabilityId) {
            $query->where('traceability_id', $traceabilityId);
        } else {
            $query->whereNull('traceability_id');
        }
        
        return $query->orderBy('created_at', 'desc')->first();
    }
} 