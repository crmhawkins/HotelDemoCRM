<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DemoCategoriasSeeder
 *
 * [DEMO 2026-04-17] Limpia categorías existentes y las reemplaza por un
 * set simplificado hotelero (sin ninguna categoría OBRA). Ejecutar SOLO
 * en BD nueva del entorno demo — truncará tablas reales si se usa en
 * producción.
 */
class DemoCategoriasSeeder extends Seeder
{
    public function run(): void
    {
        if (!config('demo.demo_mode', false)) {
            $this->command->warn('DemoCategoriasSeeder: DEMO_MODE=false — abortando para proteger datos reales');
            return;
        }

        // Desactivar FKs durante el truncate
        Schema::disableForeignKeyConstraints();
        DB::table('categoria_gastos')->truncate();
        DB::table('categoria_ingresos')->truncate();
        Schema::enableForeignKeyConstraints();

        $gastos = [
            ['id' => 1,  'nombre' => 'NOMINA'],
            ['id' => 2,  'nombre' => 'SEGUROS SOCIALES'],
            ['id' => 3,  'nombre' => 'ELECTRICIDAD'],
            ['id' => 4,  'nombre' => 'AGUA'],
            ['id' => 5,  'nombre' => 'GAS'],
            ['id' => 6,  'nombre' => 'TELEFONIA'],
            ['id' => 7,  'nombre' => 'LAVANDERIA'],
            ['id' => 8,  'nombre' => 'AMENITIES'],
            ['id' => 9,  'nombre' => 'MANTENIMIENTO'],
            ['id' => 10, 'nombre' => 'PRODUCTOS LIMPIEZA'],
            ['id' => 11, 'nombre' => 'ASCENSOR'],
            ['id' => 12, 'nombre' => 'SEGURO RC'],
            ['id' => 13, 'nombre' => 'ASESORIA'],
            ['id' => 14, 'nombre' => 'COMISION BANCARIA'],
            ['id' => 15, 'nombre' => 'COMISION BOOKING'],
            ['id' => 16, 'nombre' => 'COMISION EXPEDIA'],
            ['id' => 17, 'nombre' => 'COMISION STRIPE'],
            ['id' => 18, 'nombre' => 'DESAYUNOS COMPRA'],
            ['id' => 19, 'nombre' => 'PROVEEDOR RESTAURANTE'],
            ['id' => 20, 'nombre' => 'PRODUCTOS MINIBAR'],
            ['id' => 21, 'nombre' => 'OTROS'],
        ];

        $ingresos = [
            ['id' => 1, 'nombre' => 'ALOJAMIENTO'],
            ['id' => 2, 'nombre' => 'DESAYUNOS'],
            ['id' => 3, 'nombre' => 'RESTAURANTE'],
            ['id' => 4, 'nombre' => 'MINIBAR'],
            ['id' => 5, 'nombre' => 'SPA'],
            ['id' => 6, 'nombre' => 'LAVANDERIA CLIENTE'],
            ['id' => 7, 'nombre' => 'PARKING'],
            ['id' => 8, 'nombre' => 'SERVICIOS ADICIONALES'],
            ['id' => 9, 'nombre' => 'OTROS'],
        ];

        foreach ($gastos as $g) {
            DB::table('categoria_gastos')->insert(array_merge($g, [
                'contabilizar_misma_empresa' => 0,
            ]));
        }
        foreach ($ingresos as $i) {
            DB::table('categoria_ingresos')->insert(array_merge($i, [
                'contabilizar_misma_empresa' => 0,
            ]));
        }

        $this->command->info('DemoCategoriasSeeder: ' . count($gastos) . ' gastos + ' . count($ingresos) . ' ingresos');
    }
}
