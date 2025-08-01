<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traceability extends Model
{
    protected $table = 'traceability';
    
    // Deshabilitar el auto-increment para permitir asignación manual de IDs
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'tried',
        'variables',
        'matriz',
        'maps',
        'hypothesis',
        'shwartz',
        'conditions',
        'scenarios',
        'conclusions',
        'results',
        'state',
        'user_id'
    ];

    protected $casts = [
        'variables' => 'string',
        'matriz' => 'string',
        'maps' => 'string',
        'hypothesis' => 'string',
        'shwartz' => 'string',
        'conditions' => 'string',
        'scenarios' => 'string',
        'conclusions' => 'string',
        'results' => 'string',
        'state' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene o crea un registro de traceability para un usuario
     */
    public static function getOrCreateForUser($userId)
    {
        // Obtener la ruta actual (la que tiene el tried más alto)
        $traceability = static::where('user_id', $userId)
            ->orderBy('tried', 'desc')
            ->first();
        
        if ($traceability) {
            return $traceability;
        }
        
        // Si no existe, crear uno nuevo con el siguiente ID disponible
        $nextId = static::findNextAvailableId();
        
        return static::create([
            'id' => $nextId,
            'user_id' => $userId,
            'tried' => '1',
            'variables' => '1',
            'matriz' => '0',
            'maps' => '0',
            'hypothesis' => '0',
            'shwartz' => '0',
            'conditions' => '0',
            'scenarios' => '0',
            'conclusions' => '0',
            'results' => '0',
            'state' => '0'
        ]);
    }

    /**
     * Obtiene la ruta actual del usuario (la que tiene el tried más alto)
     */
    public static function getCurrentRouteForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('tried', 'desc')
            ->first();
    }

    /**
     * Obtiene todas las rutas del usuario
     */
    public static function getAllRoutesForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('tried', 'asc')
            ->get();
    }

    /**
     * Obtiene una ruta específica por tried
     */
    public static function getRouteByTried($userId, $tried)
    {
        return static::where('user_id', $userId)
            ->where('tried', $tried)
            ->first();
    }

    /**
     * Verifica si un usuario puede acceder a una sección específica
     */
    public function canAccessSection($section)
    {
        $sectionMap = [
            'variables' => 'variables',
            'matrix' => 'matriz',
            'graphics' => 'matriz', // La gráfica depende de la matriz
            'analysis' => 'maps',
            'hypothesis' => 'hypothesis',
            'schwartz' => 'shwartz',
            'initialconditions' => 'conditions',
            'scenarios' => 'scenarios',
            'conclusions' => 'conclusions',
            'results' => 'results'
        ];

        if (!isset($sectionMap[$section])) {
            \Log::info('Traceability - Sección no encontrada: ' . json_encode(['section' => $section]));
            return false;
        }

        $field = $sectionMap[$section];
        $value = $this->$field;
        $canAccess = $value == '1';
        
        \Log::info('Traceability - Verificando acceso: ' . json_encode([
            'section' => $section,
            'field' => $field,
            'value' => $value,
            'canAccess' => $canAccess
        ]));
        
        return $canAccess;
    }

    /**
     * Marca una sección como completada y habilita la siguiente
     */
    public function markSectionCompleted($section)
    {
        \Log::info('=== MODELO: MARCANDO SECCIÓN COMO COMPLETADA ===');
        \Log::info('Sección: ' . $section);
        \Log::info('Estado actual: ' . json_encode($this->toArray()));
        
        $sectionMap = [
            'variables' => 'variables',
            'matrix' => 'matriz',
            'graphics' => 'matriz',
            'analysis' => 'maps',
            'hypothesis' => 'hypothesis',
            'schwartz' => 'shwartz',
            'initialconditions' => 'conditions',
            'scenarios' => 'scenarios',
            'conclusions' => 'conclusions',
            'results' => 'results'
        ];

        if (isset($sectionMap[$section])) {
            $field = $sectionMap[$section];
            \Log::info('Campo a actualizar: ' . $field);
            
            // Marcar la sección actual como completada
            $this->$field = '1';
            
            // Habilitar la siguiente sección secuencialmente
            $this->enableNextSection($section);
            
            $this->save();
            
            \Log::info('Campo actualizado. Nuevo estado: ' . json_encode($this->toArray()));
        } else {
            \Log::error('Sección no encontrada en el mapeo: ' . $section);
        }
    }

    /**
     * Encuentra el primer ID disponible en la tabla
     */
    public static function findNextAvailableId(): int
    {
        // Obtener todos los IDs existentes ordenados
        $existingIds = static::orderBy('id')->pluck('id')->toArray();
        
        if (empty($existingIds)) {
            return 1; // Si no hay registros, empezar con 1
        }
        
        // Buscar el primer hueco en la secuencia
        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                // Encontramos un hueco, usar este ID
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }
        
        // Si no hay huecos, usar el siguiente ID después del último
        return $expectedId;
    }

    /**
     * Habilita la siguiente sección en la secuencia
     */
    private function enableNextSection($completedSection)
    {
        $sectionOrder = [
            'variables',
            'matrix',
            'analysis',
            'hypothesis',
            'schwartz',
            'initialconditions',
            'scenarios',
            'conclusions',
            'results'
        ];

        $sectionMap = [
            'variables' => 'variables',
            'matrix' => 'matriz',
            'graphics' => 'matriz',
            'analysis' => 'maps',
            'hypothesis' => 'hypothesis',
            'schwartz' => 'shwartz',
            'initialconditions' => 'conditions',
            'scenarios' => 'scenarios',
            'conclusions' => 'conclusions',
            'results' => 'results'
        ];

        $completedIndex = array_search($completedSection, $sectionOrder);
        
        if ($completedIndex !== false && $completedIndex < count($sectionOrder) - 1) {
            $nextSection = $sectionOrder[$completedIndex + 1];
            $nextField = $sectionMap[$nextSection] ?? null;
            
            if ($nextField && isset($this->$nextField)) {
                $this->$nextField = '1'; // Ahora bloquea la siguiente sección
                \Log::info('Siguiente sección bloqueada: ' . $nextSection . ' (' . $nextField . ')');
            }
        }
    }

    /**
     * Marca una sección como NO completada y bloquea la siguiente
     */
    public function reverseSectionCompleted($section)
    {
        \Log::info('=== MODELO: REVERSANDO SECCIÓN Y TODAS LAS POSTERIORES ===');
        $sectionOrder = [
            'variables',
            'matrix',
            'analysis',
            'hypothesis',
            'schwartz',
            'initialconditions',
            'scenarios',
            'conclusions',
            'results'
        ];
        $sectionMap = [
            'variables' => 'variables',
            'matrix' => 'matriz',
            'graphics' => 'matriz',
            'analysis' => 'maps',
            'hypothesis' => 'hypothesis',
            'schwartz' => 'shwartz',
            'initialconditions' => 'conditions',
            'scenarios' => 'scenarios',
            'conclusions' => 'conclusions',
            'results' => 'results'
        ];

        $startIndex = array_search($section, $sectionOrder);
        if ($startIndex !== false) {
            for ($i = $startIndex + 1; $i < count($sectionOrder); $i++) {
                $key = $sectionOrder[$i];
                // Solo saltar variables, bloquear todos los demás (incluyendo matrix)
                if ($key !== 'variables') {
                    $field = $sectionMap[$key] ?? null;
                    if ($field && isset($this->$field)) {
                        $this->$field = '0';
                        \Log::info('Sección bloqueada: ' . $key . ' (' . $field . ')');
                    }
                }
            }
            $this->save();
        } else {
            \Log::error('Sección no encontrada en el mapeo: ' . $section);
        }
    }

    /**
     * Bloquea la siguiente sección en la secuencia
     */
    private function blockNextSection($completedSection)
    {
        $sectionOrder = [
            'variables',
            'matrix',
            'analysis',
            'hypothesis',
            'schwartz',
            'initialconditions',
            'scenarios',
            'conclusions',
            'results'
        ];

        $sectionMap = [
            'variables' => 'variables',
            'matrix' => 'matriz',
            'graphics' => 'matriz',
            'analysis' => 'maps',
            'hypothesis' => 'hypothesis',
            'schwartz' => 'shwartz',
            'initialconditions' => 'conditions',
            'scenarios' => 'scenarios',
            'conclusions' => 'conclusions',
            'results' => 'results'
        ];

        $completedIndex = array_search($completedSection, $sectionOrder);
        
        if ($completedIndex !== false && $completedIndex < count($sectionOrder) - 1) {
            $nextSection = $sectionOrder[$completedIndex + 1];
            $nextField = $sectionMap[$nextSection] ?? null;
            
            if ($nextField && isset($this->$nextField)) {
                $this->$nextField = '0';
                \Log::info('Siguiente sección bloqueada: ' . $nextSection . ' (' . $nextField . ')');
            }
        }
    }
} 