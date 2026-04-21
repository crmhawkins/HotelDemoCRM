<?php

namespace Database\Seeders;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Edificio;
use App\Models\Gastos;
use App\Models\Ingresos;
use App\Models\Invoices;
use App\Models\InvoicesReferenceAutoincrement;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoHotelSeeder
 *
 * [DEMO 2026-04-17] Puebla una BD demo con:
 *   - 1 edificio "Hotel Demo ****"
 *   - 20 habitaciones (en tabla apartamentos)
 *   - 50 clientes con datos Faker españoles (ficticios)
 *   - 150 reservas (-6 meses a +2 meses)
 *   - 50 facturas de reservas pasadas
 *   - 30 gastos hoteleros (electricidad, amenities, etc.)
 *   - 10 ingresos por servicios adicionales
 *
 * IMPORTANTE: Solo corre con DEMO_MODE=true. En producción es no-op.
 * Requiere: tablas ya creadas por migraciones existentes.
 */
class DemoHotelSeeder extends Seeder
{
    private \Faker\Generator $faker;

    public function run(): void
    {
        if (!config('demo.demo_mode', false)) {
            $this->command->warn('DemoHotelSeeder: DEMO_MODE=false — abortando para proteger datos reales');
            return;
        }

        $this->faker = \Faker\Factory::create('es_ES');

        $this->command->info('DemoHotelSeeder: iniciando...');

        // 1. Edificio
        $edificio = Edificio::firstOrCreate(
            ['nombre' => config('demo.hotel.nombre', 'Hotel Demo Costa Sur')],
            [
                'clave' => 'DEMO',
                'codigo_establecimiento' => 'DEMO0001',
                'mir_activo' => 0,
            ]
        );
        $this->command->info("  Edificio: {$edificio->nombre}");

        // 2. Suites (20) — en el demo los alojamientos se llaman "suite" en el
        // front publico. El modelo sigue siendo `Apartamento` (columna BD).
        $this->command->info('  Creando 20 suites...');
        $nombresHab = [
            'Suite 101', 'Suite 102', 'Suite 103', 'Suite 104',
            'Suite 201', 'Suite 202', 'Suite 203', 'Suite 204',
            'Suite 301', 'Suite 302',
            'Suite Mediterránea', 'Suite Costa Sol', 'Suite Vista Mar',
            'Suite Vista Jardín', 'Suite Vista Piscina',
            'Suite Familiar 401', 'Suite Familiar 402',
            'Junior Suite Norte', 'Junior Suite Sur',
            'Suite Panorámica',
        ];
        $habitaciones = collect();
        foreach ($nombresHab as $i => $nom) {
            $habitaciones->push(Apartamento::create([
                'nombre' => $nom,
                'titulo' => $nom,
                'edificio_id' => $edificio->id,
                // id_channex dummy: el portal publico filtra por whereNotNull('id_channex'),
                // asi que sin este campo las suites no aparecen en /web/apartamentos.
                'id_channex' => 'demo-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'currency' => 'EUR',
                'country' => 'ES',
                'state' => 'Málaga',
                'city' => 'Marbella',
                'address' => 'Avenida Demo ' . ($i + 1),
                'zip_code' => '29600',
                'timezone' => 'Europe/Madrid',
                'property_type' => 'hotel_room',
                'bedrooms' => $i < 10 ? 1 : ($i < 15 ? 2 : 3),
                'bathrooms' => 1,
                'max_guests' => $i < 10 ? 2 : ($i < 15 ? 3 : 4),
                'size' => rand(18, 45),
            ]));
        }

        // 3. Clientes (50)
        $this->command->info('  Creando 50 clientes...');
        $clientes = collect();
        for ($c = 0; $c < 50; $c++) {
            $clientes->push(Cliente::create([
                'is_null' => 0,
                'nombre' => $this->faker->firstName,
                'apellido1' => $this->faker->lastName,
                'apellido2' => $this->faker->lastName,
                'nacionalidad' => 'ESPAÑOLA',
                'tipo_documento' => 'DNI',
                'num_identificacion' => $this->faker->numerify('########') . $this->faker->randomLetter,
                'telefono' => '6' . $this->faker->numerify('########'),
                'email' => 'demo' . $c . '@' . $this->faker->safeEmailDomain,
                'direccion' => $this->faker->streetAddress,
                'localidad' => $this->faker->city,
                'codigo_postal' => $this->faker->postcode,
                'provincia' => 'Málaga',
                'estado' => 1,
                'fecha_nacimiento' => $this->faker->dateTimeBetween('-70 years', '-20 years')->format('Y-m-d'),
                'sexo' => $this->faker->randomElement(['M', 'F']),
                'idioma' => 'es',
                'password' => Hash::make(Str::random(16)),
            ]));
        }

        // 4. Reservas (150)
        $this->command->info('  Creando 150 reservas...');
        $reservas = collect();
        for ($r = 0; $r < 150; $r++) {
            $offsetDias = rand(-180, 60); // últimos 6 meses hasta +2 meses
            $entrada = Carbon::today()->addDays($offsetDias);
            $noches = rand(1, 7);
            $salida = (clone $entrada)->addDays($noches);
            $precioNoche = rand(60, 220);
            $precio = $precioNoche * $noches;

            // Estado: 1-confirmada, 2-terminada, 4-cancelada
            if ($salida->isPast()) {
                $estadoId = rand(1, 10) === 1 ? 4 : 2;
            } else {
                $estadoId = 1;
            }

            $reservas->push(Reserva::create([
                'cliente_id' => $clientes->random()->id,
                'apartamento_id' => $habitaciones->random()->id,
                'estado_id' => $estadoId,
                'origen' => collect(['directa', 'booking', 'airbnb', 'expedia', 'web'])->random(),
                'fecha_entrada' => $entrada->format('Y-m-d'),
                'fecha_salida' => $salida->format('Y-m-d'),
                'fecha_hora_entrada' => $entrada->format('Y-m-d') . ' 15:00:00',
                'fecha_hora_salida' => $salida->format('Y-m-d') . ' 11:00:00',
                'precio' => $precio,
                'neto' => round($precio / 1.10, 2),
                'iva' => round($precio - ($precio / 1.10), 2),
                'comision' => 0,
                'numero_personas' => rand(1, 4),
                'codigo_reserva' => 'DEMO' . str_pad((string)$r, 5, '0', STR_PAD_LEFT),
                'verificado' => 1,
                'dni_entregado' => $salida->isPast() ? 1 : 0,
                'no_facturar' => 0,
                'mir_estado' => $salida->isPast() ? 'enviado' : null,
            ]));
        }

        // 5. Facturas (50 de reservas pasadas)
        $this->command->info('  Creando 50 facturas...');
        $pasadas = $reservas->filter(fn($r) => Carbon::parse($r->fecha_salida)->isPast() && $r->estado_id != 4);
        $mesActual = now()->format('Y/m');
        $counter = 1;

        // Reset referencia autoincrement si existe
        $ref = InvoicesReferenceAutoincrement::firstOrNew(['year' => now()->year, 'month' => now()->month]);
        $ref->counter = 0;
        $ref->save();

        foreach ($pasadas->take(50) as $reserva) {
            $reference = $mesActual . '/' . str_pad((string)$counter, 6, '0', STR_PAD_LEFT);
            $base = round($reserva->precio / 1.10, 2);
            $iva = round($reserva->precio - $base, 2);

            Invoices::create([
                'cliente_id' => $reserva->cliente_id,
                'reserva_id' => $reserva->id,
                'invoice_status_id' => 2, // 2 = pagada / emitida (ajustar si la tabla tiene otros valores)
                'concepto' => 'Alojamiento habitación',
                'description' => 'Alojamiento en Hotel Demo',
                'fecha' => $reserva->fecha_salida,
                'fecha_cobro' => $reserva->fecha_salida,
                'base' => $base,
                'iva' => $iva,
                'total' => $reserva->precio,
                'descuento' => 0,
                'reference' => $reference,
                'reference_autoincrement_id' => $ref->id,
                'es_rectificativa' => 0,
            ]);
            $counter++;
        }
        $ref->counter = $counter - 1;
        $ref->save();

        // 6. Gastos demo (30) — solo categorías hotel
        $this->command->info('  Creando 30 gastos...');
        // IDs de categorías creados por DemoCategoriasSeeder:
        // 3=ELECTRICIDAD 4=AGUA 5=GAS 6=TELEFONIA 7=LAVANDERIA 8=AMENITIES
        // 9=MANTENIMIENTO 10=PRODUCTOS LIMPIEZA 18=DESAYUNOS 19=RESTAURANTE
        $catsGasto = [3, 4, 5, 6, 7, 8, 9, 10, 18, 19, 20];
        $titulos = [
            3 => 'Factura Endesa',
            4 => 'Aqualia recibo',
            5 => 'Gas Natural Fenosa',
            6 => 'Movistar fibra',
            7 => 'Lavandería Blanca',
            8 => 'Pedido amenities',
            9 => 'Reparación caldera',
            10 => 'Productos limpieza Mayumar',
            18 => 'Compra desayunos',
            19 => 'Proveedor restaurante',
            20 => 'Reposición minibar',
        ];
        for ($g = 0; $g < 30; $g++) {
            $cat = $catsGasto[array_rand($catsGasto)];
            Gastos::create([
                'categoria_id' => $cat,
                'bank_id' => 1,
                'is_apartamento' => 0,
                'title' => $titulos[$cat] ?? 'Gasto demo',
                'quantity' => rand(50, 800) + (rand(0, 99) / 100),
                'date' => Carbon::today()->subDays(rand(1, 180))->format('Y-m-d'),
                'estado_id' => 2,
            ]);
        }

        // 7. Ingresos demo (10) — solo servicios adicionales
        $this->command->info('  Creando 10 ingresos por servicios...');
        // 2=DESAYUNOS 3=RESTAURANTE 4=MINIBAR 5=SPA 7=PARKING 8=SERVICIOS
        $catsIngreso = [2, 3, 4, 5, 7, 8];
        $titulosIng = [
            2 => 'Desayuno buffet clientes',
            3 => 'Cena restaurante',
            4 => 'Consumos minibar',
            5 => 'Tratamiento spa',
            7 => 'Parking clientes',
            8 => 'Servicio lavandería cliente',
        ];
        for ($i = 0; $i < 10; $i++) {
            $cat = $catsIngreso[array_rand($catsIngreso)];
            Ingresos::create([
                'categoria_id' => $cat,
                'bank_id' => 1,
                'title' => $titulosIng[$cat] ?? 'Ingreso extra',
                'quantity' => rand(20, 350) + (rand(0, 99) / 100),
                'date' => Carbon::today()->subDays(rand(1, 90))->format('Y-m-d'),
                'estado_id' => 2,
            ]);
        }

        $this->command->info('DemoHotelSeeder: completado.');
        $this->command->info('  - 1 edificio, 20 habitaciones, 50 clientes');
        $this->command->info('  - 150 reservas, 50 facturas');
        $this->command->info('  - 30 gastos hoteleros, 10 ingresos');
    }
}
