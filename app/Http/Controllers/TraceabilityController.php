<?php

namespace App\Http\Controllers;

use App\Models\Traceability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TraceabilityController extends Controller
{
    /**
     * Obtiene el estado de traceability del usuario
     */
    public function getUserTraceability(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = Traceability::getOrCreateForUser($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $traceability
        ]);
    }

    /**
     * Verifica si un usuario puede acceder a una sección específica
     */
    public function canAccessSection(Request $request): JsonResponse
    {
        $user = auth()->user();
        $section = $request->input('section');
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Los administradores (rol 1) pueden acceder a todas las secciones
        if ($user->role === 1) {
            return response()->json([
                'success' => true,
                'canAccess' => true,
                'reason' => 'admin'
            ]);
        }

        $traceability = Traceability::getOrCreateForUser($user->id);
        
        // Los usuarios normales solo pueden acceder a variables inicialmente
        if ($section === 'variables') {
            return response()->json([
                'success' => true,
                'canAccess' => true,
                'reason' => 'variables_always_accessible'
            ]);
        }

        // Para otras secciones, verificar si la sección anterior está completada
        $canAccess = $traceability->canAccessSection($section);
        
        return response()->json([
            'success' => true,
            'canAccess' => $canAccess,
            'reason' => $canAccess ? 'section_completed' : 'section_not_completed'
        ]);
    }

    /**
     * Marca una sección como completada
     */
    public function markSectionCompleted(Request $request): JsonResponse
    {
        \Log::info('=== MARCANDO SECCIÓN COMO COMPLETADA ===');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        $user = auth()->user();
        $section = $request->input('section');
        
        \Log::info('Usuario: ' . json_encode(['id' => $user->id, 'role' => $user->role, 'email' => $user->user]));
        \Log::info('Sección a marcar: ' . $section);
        
        if (!$user) {
            \Log::error('Usuario no autenticado');
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = Traceability::getOrCreateForUser($user->id);
        \Log::info('Traceability antes de marcar: ' . json_encode($traceability->toArray()));
        
        $traceability->markSectionCompleted($section);
        
        \Log::info('Traceability después de marcar: ' . json_encode($traceability->toArray()));
        
        return response()->json([
            'success' => true,
            'message' => 'Sección marcada como completada'
        ]);
    }

    /**
     * Marca una sección como NO completada y bloquea la siguiente
     */
    public function reverseSectionCompleted(Request $request): JsonResponse
    {
        \Log::info('=== REVERSANDO SECCIÓN COMO COMPLETADA DESDE EL CONTROLADOR ===');
        $user = auth()->user();
        $section = $request->input('section');

        if (!$user) {
            \Log::error('Usuario no autenticado');
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = \App\Models\Traceability::getOrCreateForUser($user->id);
        $traceability->reverseSectionCompleted($section);

        return response()->json([
            'success' => true,
            'message' => 'Sección revertida y bloqueada correctamente'
        ]);
    }

    /**
     * Obtiene las secciones disponibles para un usuario
     */
    public function getAvailableSections(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = Traceability::getOrCreateForUser($user->id);
        
        \Log::info('Traceability - Usuario: ' . json_encode(['id' => $user->id, 'role' => $user->role, 'email' => $user->user]));
        \Log::info('Traceability - Estado actual: ' . json_encode($traceability->toArray()));
        
        // Los administradores tienen acceso a todas las secciones
        if ($user->role === 1) {
            \Log::info('Traceability - Usuario es admin, acceso completo');
            return response()->json([
                'success' => true,
                'sections' => [
                    'variables' => true,
                    'matrix' => true,
                    'graphics' => true,
                    'analysis' => true,
                    'hypothesis' => true,
                    'schwartz' => true,
                    'initialconditions' => true,
                    'scenarios' => true,
                    'conclusions' => true,
                    'results' => true
                ]
            ]);
        }

        // Para usuarios normales, verificar cada sección
        $sections = [
            'variables' => true, // Siempre accesible
            'matrix' => $traceability->canAccessSection('matrix'),
            'graphics' => $traceability->canAccessSection('graphics'),
            'analysis' => $traceability->canAccessSection('analysis'),
            'hypothesis' => $traceability->canAccessSection('hypothesis'),
            'schwartz' => $traceability->canAccessSection('schwartz'),
            'initialconditions' => $traceability->canAccessSection('initialconditions'),
            'scenarios' => $traceability->canAccessSection('scenarios'),
            'conclusions' => $traceability->canAccessSection('conclusions'),
            'results' => $traceability->canAccessSection('results')
        ];
        
        \Log::info('Traceability - Secciones disponibles para usuario normal: ' . json_encode($sections));
        
        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Resetea los campos de edición (bloqueo) de la sección dada y todas las posteriores para el usuario actual
     */
    public function resetEditLocksFromSection(Request $request): JsonResponse
    {
        $user = auth()->user();
        $section = $request->input('section');

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Orden de los módulos
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

        $startIndex = array_search($section, $sectionOrder);
        if ($startIndex === false) {
            return response()->json(['error' => 'Sección no válida'], 400);
        }

        // Lógica para cada módulo (ejemplo: variables y matriz)
        for ($i = $startIndex; $i < count($sectionOrder); $i++) {
            $mod = $sectionOrder[$i];
            switch ($mod) {
                case 'variables':
                    \App\Models\Variable::where('user_id', $user->id)->update(['edits_variable' => 0, 'state' => '0']);
                    break;
                case 'matrix':
                    if (class_exists('App\\Models\\Matriz')) {
                        \App\Models\Matriz::where('user_id', $user->id)->update(['state' => '0']);
                    }
                    break;
                case 'analysis':
                    if (class_exists('App\\Models\\VariableMapAnalisys')) {
                        \App\Models\VariableMapAnalisys::where('user_id', $user->id)->update(['state' => '0']);
                    }
                    break;
                case 'hypothesis':
                    if (class_exists('App\\Models\\Hypothesis')) {
                        \App\Models\Hypothesis::where('user_id', $user->id)->update(['state' => '0']);
                    }
                    break;
                case 'initialconditions':
                    \App\Models\Variable::where('user_id', $user->id)->update(['edits_now_condition' => 0, 'state' => '0']);
                    break;
                case 'scenarios':
                    if (class_exists('App\\Models\\Scenarios')) {
                        \App\Models\Scenarios::where('user_id', $user->id)->update([
                            'edits' => 0,
                            'state' => '0',
                            'edits_year1' => 0,
                            'edits_year2' => 0,
                            'edits_year3' => 0
                        ]);
                    }
                    break;
                case 'conclusions':
                    if (class_exists('App\\Models\\Conclusion')) {
                        \App\Models\Conclusion::where('user_id', $user->id)->update([
                            'component_practice_edits' => 0,
                            'actuality_edits' => 0,
                            'aplication_edits' => 0,
                            'state' => '0'
                        ]);
                    }
                    break;
                // Puedes agregar más módulos aquí si es necesario
            }
        }

        return response()->json(['success' => true, 'message' => 'Campos de edición reseteados']);
    }

    /**
     * Actualiza el campo tried de traceability para el usuario autenticado
     */
    public function updateTried(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = \App\Models\Traceability::getOrCreateForUser($user->id);
        $traceability->tried = $request->input('tried', 2);
        $traceability->save();

        return response()->json([
            'success' => true,
            'tried' => $traceability->tried
        ]);
    }

    /**
     * Obtiene el valor actual del campo tried para el usuario autenticado
     */
    public function getTried(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $traceability = \App\Models\Traceability::getOrCreateForUser($user->id);
        
        return response()->json([
            'success' => true,
            'tried' => $traceability->tried
        ]);
    }
} 