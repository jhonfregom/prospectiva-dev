<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health';
    protected $description = 'Verificar la salud general del sistema y rendimiento';

    public function handle()
    {
        $this->info('🏥 Verificando salud del sistema...');
        
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'openrouter' => $this->checkOpenRouter(),
            'ollama' => $this->checkOllama(),
            'performance' => $this->checkPerformance()
        ];

        $this->displayResults($checks);
        
        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');
        
        return $allHealthy ? 0 : 1;
    }

    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            return [
                'status' => 'healthy',
                'message' => "Conexión exitosa ({$responseTime}ms)",
                'details' => [
                    'driver' => config('database.default'),
                    'host' => config('database.connections.' . config('database.default') . '.host'),
                    'database' => config('database.connections.' . config('database.default') . '.database')
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'message' => "Cache funcionando ({$responseTime}ms)",
                    'details' => [
                        'driver' => config('cache.default'),
                        'store' => config('cache.stores.' . config('cache.default') . '.driver')
                    ]
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Cache no está funcionando correctamente',
                    'details' => []
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Error en cache: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'test content';
            
            Storage::put($testFile, $testContent);
            $retrieved = Storage::get($testFile);
            Storage::delete($testFile);
            
            if ($retrieved === $testContent) {
                return [
                    'status' => 'healthy',
                    'message' => 'Almacenamiento funcionando correctamente',
                    'details' => [
                        'driver' => config('filesystems.default'),
                        'disk' => config('filesystems.disks.' . config('filesystems.default') . '.driver')
                    ]
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Almacenamiento no está funcionando correctamente',
                    'details' => []
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Error en almacenamiento: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    private function checkOpenRouter(): array
    {
        try {
            $apiKey = config('services.openrouter.api_key');
            if (empty($apiKey)) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'API key de OpenRouter no configurada',
                    'details' => []
                ];
            }

            $startTime = microtime(true);
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get('https://openrouter.ai/api/v1/models');
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'message' => "OpenRouter disponible ({$responseTime}ms)",
                    'details' => [
                        'status_code' => $response->status(),
                        'response_time' => $responseTime
                    ]
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'OpenRouter no responde correctamente',
                    'details' => [
                        'status_code' => $response->status()
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Error en OpenRouter: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    private function checkOllama(): array
    {
        try {
            $startTime = microtime(true);
            $response = Http::timeout(5)
                ->get('http://localhost:11434/api/tags');
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $models = $response->json();
                $modelCount = count($models['models'] ?? []);
                
                return [
                    'status' => 'healthy',
                    'message' => "Ollama disponible ({$responseTime}ms, {$modelCount} modelos)",
                    'details' => [
                        'status_code' => $response->status(),
                        'response_time' => $responseTime,
                        'models_count' => $modelCount
                    ]
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Ollama no está ejecutándose',
                    'details' => [
                        'status_code' => $response->status()
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Ollama no disponible: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    private function checkPerformance(): array
    {
        $checks = [];
        
        // Verificar memoria
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryPercent = ($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100;
        
        $checks['memory'] = [
            'status' => $memoryPercent < 80 ? 'healthy' : 'warning',
            'message' => "Memoria: " . $this->formatBytes($memoryUsage) . " / {$memoryLimit} ({$memoryPercent}%)",
            'details' => [
                'usage' => $this->formatBytes($memoryUsage),
                'limit' => $memoryLimit,
                'percent' => round($memoryPercent, 2)
            ]
        ];
        
        // Verificar tiempo de ejecución
        $executionTime = microtime(true) - LARAVEL_START;
        $checks['execution_time'] = [
            'status' => $executionTime < 30 ? 'healthy' : 'warning',
            'message' => "Tiempo de ejecución: " . round($executionTime, 3) . "s",
            'details' => [
                'seconds' => round($executionTime, 3)
            ]
        ];
        
        // Verificar carga del sistema
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $checks['system_load'] = [
                'status' => $load[0] < 2 ? 'healthy' : 'warning',
                'message' => "Carga del sistema: " . round($load[0], 2),
                'details' => [
                    'load_1min' => round($load[0], 2),
                    'load_5min' => round($load[1], 2),
                    'load_15min' => round($load[2], 2)
                ]
            ];
        }
        
        return [
            'status' => collect($checks)->every(fn($check) => $check['status'] === 'healthy') ? 'healthy' : 'warning',
            'message' => 'Rendimiento del sistema',
            'details' => $checks
        ];
    }

    private function displayResults(array $checks): void
    {
        $this->newLine();
        
        foreach ($checks as $name => $check) {
            $status = match($check['status']) {
                'healthy' => '✅',
                'warning' => '⚠️',
                'unhealthy' => '❌'
            };
            
            $this->line("{$status} " . ucfirst($name) . ": {$check['message']}");
            
            if (!empty($check['details'])) {
                foreach ($check['details'] as $key => $value) {
                    if (is_array($value)) {
                        $this->line("   {$key}: " . json_encode($value));
                    } else {
                        $this->line("   {$key}: {$value}");
                    }
                }
            }
            
            $this->newLine();
        }
    }

    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        return match($unit) {
            'k' => $value * 1024,
            'm' => $value * 1024 * 1024,
            'g' => $value * 1024 * 1024 * 1024,
            default => $value
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}





