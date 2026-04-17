<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * DemoModeService
 *
 * Helper central para saber si el sistema está en modo demo y producir
 * respuestas simuladas determinísticas. Se inyecta o se accede vía:
 *
 *   app(\App\Services\DemoModeService::class)
 *   DemoModeService::active()
 *
 * [DEMO 2026-04-17] Nuevo servicio añadido para preparar demo a cliente
 * de hotel. No afecta a producción mientras DEMO_MODE=false.
 */
class DemoModeService
{
    /** Atajo estático */
    public static function active(): bool
    {
        return (bool) config('demo.demo_mode', false);
    }

    /** ¿Está un servicio concreto stubbed? */
    public static function isStubbed(string $service): bool
    {
        if (self::active()) {
            return true;
        }
        return (bool) config("demo.disable.$service", false);
    }

    /**
     * Log homogéneo para stubs. Devuelve el payload simulado que se
     * quiere retornar al caller.
     */
    public static function stubResponse(string $service, string $action, array $extra = []): array
    {
        $payload = array_merge([
            'success' => true,
            'demo'    => true,
            'service' => $service,
            'action'  => $action,
            'at'      => now()->toDateTimeString(),
        ], $extra);

        Log::info("[DEMO STUB] $service::$action", $payload);

        return $payload;
    }
}
