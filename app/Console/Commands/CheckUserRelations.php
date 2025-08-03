<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EconomicSector;

class CheckUserRelations extends Command
{
    protected $signature = 'user:check-relations';
    protected $description = 'Verificar que las relaciones de User funcionan correctamente';

    public function handle()
    {
        $this->info('🔍 Verificando relaciones de User...');
        
        try {
            // Verificar que el modelo User puede acceder a EconomicSector
            $user = new User();
            $economicSectorRelation = $user->economicSector();
            
            $this->info('✅ Relación economicSector creada correctamente');
            $this->line("   Clase relacionada: " . get_class($economicSectorRelation->getRelated()));
            
            // Verificar que hay usuarios con sectores económicos
            $usersWithSectors = User::whereNotNull('economic_sector')->count();
            $this->info("📊 Usuarios con sector económico: {$usersWithSectors}");
            
            // Verificar que la clave foránea funciona
            $sampleUser = User::whereNotNull('economic_sector')->first();
            if ($sampleUser) {
                $this->info("👤 Usuario de ejemplo: ID {$sampleUser->id}");
                $this->line("   Sector económico ID: {$sampleUser->economic_sector}");
                
                // Intentar cargar la relación
                $sector = $sampleUser->economicSector;
                if ($sector) {
                    $this->info("✅ Relación cargada correctamente");
                    $this->line("   Sector: {$sector->name}");
                } else {
                    $this->warn("⚠️  No se pudo cargar la relación");
                }
            } else {
                $this->info("ℹ️  No hay usuarios con sector económico asignado");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error al verificar relaciones:');
            $this->line("   {$e->getMessage()}");
        }
        
        return 0;
    }
} 