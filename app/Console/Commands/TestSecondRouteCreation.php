<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Traceability;
use App\Models\User;

class TestSecondRouteCreation extends Command
{
    
    protected $signature = 'test:second-route-creation {user_id?}';

    protected $description = 'Test the creation of a second route to verify correct values';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            $this->error('Debe proporcionar un user_id');
            return 1;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("Probando creación de segunda ruta para usuario: {$user->user} (ID: {$user->id})");

        $existingRoute2 = Traceability::where('user_id', $user->id)
            ->where('tried', '2')
            ->first();

        if ($existingRoute2) {
            $this->warn("Ya existe una segunda ruta para este usuario. Eliminándola para la prueba...");
            $existingRoute2->delete();
        }

        $currentTraceability = Traceability::where('user_id', $user->id)
            ->where('results', '1')
            ->first();

        if (!$currentTraceability) {
            $this->error("El usuario no tiene una primera ruta completada (results = '1')");
            $this->info("Creando una primera ruta completada para la prueba...");
            
            $nextId = Traceability::findNextAvailableId();
            $currentTraceability = Traceability::create([
                'id' => $nextId,
                'user_id' => $user->id,
                'tried' => '1',
                'variables' => '1',
                'matriz' => '1',
                'maps' => '1',
                'hypothesis' => '1',
                'shwartz' => '1',
                'conditions' => '1',
                'scenarios' => '1',
                'conclusions' => '1',
                'results' => '1',
                'state' => '0'
            ]);
            
            $this->info("Primera ruta creada con ID: {$currentTraceability->id}");
        }

        $this->info("Creando segunda ruta...");
        
        $nextId = Traceability::findNextAvailableId();
        $newTraceability = Traceability::create([
            'id' => $nextId,
            'user_id' => $user->id,
            'tried' => '2',
            'variables' => '1',
            'matriz' => '0',
            'maps' => '0',
            'hypothesis' => '0',
            'shwartz' => '0',
            'conditions' => '0',
            'scenarios' => '0',
                            'conclusions' => '0',
                'results' => '1', // Habilitado para que el usuario pueda ingresar
                'state' => '0'
        ]);

        $this->info("Segunda ruta creada exitosamente con ID: {$newTraceability->id}");

        $this->info("\nValores de la segunda ruta creada:");
        $this->table(
            ['Campo', 'Valor', 'Estado'],
            [
                ['tried', $newTraceability->tried, 'Segunda ruta'],
                ['variables', $newTraceability->variables, $newTraceability->variables == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['matriz', $newTraceability->matriz, $newTraceability->matriz == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['maps', $newTraceability->maps, $newTraceability->maps == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['hypothesis', $newTraceability->hypothesis, $newTraceability->hypothesis == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['shwartz', $newTraceability->shwartz, $newTraceability->shwartz == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['conditions', $newTraceability->conditions, $newTraceability->conditions == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['scenarios', $newTraceability->scenarios, $newTraceability->scenarios == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['conclusions', $newTraceability->conclusions, $newTraceability->conclusions == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['results', $newTraceability->results, $newTraceability->results == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['state', $newTraceability->state, 'Estado actual'],
            ]
        );

        $expectedValues = [
            'variables' => '1',
            'results' => '1', // Habilitado para que el usuario pueda ingresar
            'matriz' => '0',
            'maps' => '0',
            'hypothesis' => '0',
            'shwartz' => '0',
            'conditions' => '0',
            'scenarios' => '0',
            'conclusions' => '0'
        ];

        $allCorrect = true;
        foreach ($expectedValues as $field => $expectedValue) {
            if ($newTraceability->$field !== $expectedValue) {
                $this->error("❌ Campo {$field}: esperado '{$expectedValue}', obtenido '{$newTraceability->$field}'");
                $allCorrect = false;
            } else {
                $this->info("✅ Campo {$field}: correcto ('{$newTraceability->$field}')");
            }
        }

        if ($allCorrect) {
            $this->info("\n🎉 ¡TODOS LOS VALORES SON CORRECTOS!");
            $this->info("La segunda ruta se creó correctamente con variables y results en '1' y el resto en '0'");
        } else {
            $this->error("\n❌ HAY VALORES INCORRECTOS");
            $this->error("La segunda ruta no se creó con los valores esperados");
        }

        return 0;
    }
}


