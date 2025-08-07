<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traceability extends Model
{
    protected $table = 'traceability';

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

    public static function getOrCreateForUser($userId)
    {
        
        $traceability = static::where('user_id', $userId)
            ->orderBy('tried', 'desc')
            ->first();
        
        if ($traceability) {
            return $traceability;
        }

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

    public static function getCurrentRouteForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('tried', 'desc')
            ->first();
    }

    public static function getAllRoutesForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('tried', 'asc')
            ->get();
    }

    public static function getRouteByTried($userId, $tried)
    {
        return static::where('user_id', $userId)
            ->where('tried', $tried)
            ->first();
    }

    public function canAccessSection($section)
    {
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

            $this->$field = '1';

            $this->enableNextSection($section);
            
            $this->save();
            
            \Log::info('Campo actualizado. Nuevo estado: ' . json_encode($this->toArray()));
        } else {
            \Log::error('Sección no encontrada en el mapeo: ' . $section);
        }
    }

    public static function findNextAvailableId(): int
    {
        
        $maxId = static::max('id');
        return $maxId ? $maxId + 1 : 1;
    }

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
                
                $this->$nextField = '1';
                \Log::info('Siguiente sección habilitada: ' . $nextSection . ' (' . $nextField . ') en ruta tried=' . $this->tried);
            }
        }
    }

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

                if ($this->tried == '2' && $key === 'results') {
                    \Log::info('Segunda ruta: No se bloquea results');
                    continue;
                }

                if ($key !== 'variables') {
                    $field = $sectionMap[$key] ?? null;
                    if ($field && isset($this->$field)) {
                        $this->$field = '0';
                        \Log::info('Sección bloqueada: ' . $key . ' (' . $field . ') en ruta tried=' . $this->tried);
                    }
                }
            }
            $this->save();
        } else {
            \Log::error('Sección no encontrada en el mapeo: ' . $section);
        }
    }

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