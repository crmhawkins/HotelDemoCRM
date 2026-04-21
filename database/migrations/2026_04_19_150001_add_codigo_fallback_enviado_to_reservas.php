<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [2026-04-19] Tracking: si a este huésped ya le enviamos el código de
 * emergencia del portal (para no mandarle el mismo WhatsApp dos veces
 * cuando se activa el fallback).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $t) {
            if (!Schema::hasColumn('reservas', 'codigo_fallback_enviado')) {
                $col = $t->boolean('codigo_fallback_enviado')->default(false);
                // 'codigo_acceso_enviado' existe en produccion pero no en demo.
                if (Schema::hasColumn('reservas', 'codigo_acceso_enviado')) {
                    $col->after('codigo_acceso_enviado');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $t) {
            if (Schema::hasColumn('reservas', 'codigo_fallback_enviado')) {
                $t->dropColumn('codigo_fallback_enviado');
            }
        });
    }
};
