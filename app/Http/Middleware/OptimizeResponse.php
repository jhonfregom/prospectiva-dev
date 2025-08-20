<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class OptimizeResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo aplicar optimizaciones a respuestas exitosas
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->addOptimizationHeaders($response, $request);
        }

        return $response;
    }

    private function addOptimizationHeaders(BaseResponse $response, Request $request): void
    {
        // Headers de cache para recursos estáticos
        if ($this->isStaticResource($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        }
        
        // Headers de cache para API responses
        elseif ($request->is('api/*') || $request->expectsJson()) {
            $response->headers->set('Cache-Control', 'private, max-age=300'); // 5 minutos
        }
        
        // Headers de cache para páginas dinámicas
        else {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }

        // Headers de seguridad
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Headers de rendimiento
        $response->headers->set('Vary', 'Accept-Encoding');
        
        // Compresión si está disponible
        if (extension_loaded('zlib') && $this->shouldCompress($request, $response)) {
            $response->headers->set('Content-Encoding', 'gzip');
        }
    }

    private function isStaticResource(Request $request): bool
    {
        $path = $request->path();
        $extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        
        foreach ($extensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }

    private function shouldCompress(Request $request, BaseResponse $response): bool
    {
        // Solo comprimir si el cliente lo acepta
        if (!$request->accepts('gzip')) {
            return false;
        }

        // Solo comprimir respuestas de texto
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/',
            'application/json',
            'application/xml',
            'application/javascript',
            'text/css',
            'text/html'
        ];

        foreach ($compressibleTypes as $type) {
            if (str_starts_with($contentType, $type)) {
                return true;
            }
        }

        return false;
    }
}





