<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Variable que representa una variable en la base de datos
 * 
 * Este modelo gestiona las variables del análisis prospectivo, donde cada variable
 * representa un factor o elemento a evaluar en el estudio. El modelo maneja:
 * - Identificación única de variables (V1, V2, etc.)
 * - Nombres y descripciones de variables
 * - Puntuaciones basadas en análisis de texto
 * - Estados de evaluación
 * 
 * @property int $id Identificador único autoincremental
 * @property string $id_variable Código visible de la variable (V1, V2, etc.)
 * @property string $name_variable Nombre descriptivo de la variable
 * @property string $description Descripción detallada que se analiza para el score
 * @property int $score Puntuación calculada basada en el conteo de palabras
 * @property int $user_id ID del usuario que creó la variable
 * @property string $state Estado actual de la variable (siempre '0' por defecto)
 */
class Variable extends Model
{
    use HasFactory;  // Trait para crear factories para pruebas

    /**
     * Los atributos que son asignables masivamente.
     * 
     * @var array
     * 
     * Estos campos pueden ser llenados a través de asignación masiva,
     * lo cual es útil para la creación y actualización de variables
     * a través de formularios o API.
     */
    protected $fillable = [
        'id',
        'id_variable',
        'name_variable',
        'description',
        'score',
        'user_id',
        'state',
        'is_edited'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * 
     * @var array
     * 
     * Esta conversión asegura que:
     * - score: Se maneje como entero para cálculos matemáticos
     * - user_id: Se maneje como entero para relaciones con la tabla users
     */
    protected $casts = [
        'score' => 'integer',
        'user_id' => 'integer',
        'is_edited' => 'boolean',
        'state' => 'string'
    ];

    /**
     * Valores por defecto para los atributos
     * 
     * @var array
     */
    protected $attributes = [
        'is_edited' => false,
        'state' => '0'
    ];

    /**
     * Relación con el modelo User
     * 
     * Define una relación belongsTo con el modelo User, lo que permite:
     * - Acceder al usuario que creó la variable
     * - Mantener integridad referencial en la base de datos
     * - Facilitar consultas relacionadas
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 