<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conclusion extends Model
{
    use HasFactory;

    protected $table = 'conclusions';

    protected $fillable = [
        'component_practice',
        'component_practice_edits',
        'actuality',
        'actuality_edits',
        'aplication',
        'aplication_edits',
        'state',
        'user_id',
        'tried_id'
    ];

    protected $casts = [
        'state' => 'string',
        'user_id' => 'integer',
        'component_practice_edits' => 'integer',
        'actuality_edits' => 'integer',
        'aplication_edits' => 'integer'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener conclusiones por usuario
     */
    public static function getByUser($userId)
    {
        return self::where('user_id', $userId)->first();
    }

    /**
     * Crear o actualizar conclusiones para un usuario
     */
    public static function createOrUpdate($data, $userId)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    /**
     * Verificar si todas las conclusiones están bloqueadas
     */
    public function isAllBlocked()
    {
        return $this->state === '1';
    }

    /**
     * Bloquear las conclusiones
     */
    public function block()
    {
        $this->state = '1';
        return $this->save();
    }

    /**
     * Desbloquear las conclusiones
     */
    public function unblock()
    {
        $this->state = '0';
        return $this->save();
    }

    /**
     * Verificar si los tres contadores de edición están en 3
     */
    public function areAllFieldsBlocked()
    {
        return $this->component_practice_edits >= 3 &&
               $this->actuality_edits >= 3 &&
               $this->aplication_edits >= 3;
    }
} 