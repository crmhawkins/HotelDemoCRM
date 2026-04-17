<?php

/*
|--------------------------------------------------------------------------
| Traducciones hotel (demo)
|--------------------------------------------------------------------------
|
| Archivo creado 2026-04-17 para renombrar UI de "apartamento" a
| "habitación" sin tocar nombres de modelos/columnas/rutas. Usar así en
| blades:
|
|   @lang('hotel.apartamento_singular_cap')   -> "Habitación"
|   @lang('hotel.apartamentos_plural_cap')    -> "Habitaciones"
|
| Para cambios masivos en blades podemos hacer grep-replace solo en textos
| visibles (no en atributos name=, route(), class=, etc.).
*/

return [

    // Sustituciones básicas apartamento -> habitación
    'apartamento_singular'       => 'habitación',
    'apartamento_singular_cap'   => 'Habitación',
    'apartamento_plural'         => 'habitaciones',
    'apartamento_plural_cap'     => 'Habitaciones',

    // Variantes con determinante
    'el_apartamento'             => 'la habitación',
    'El_apartamento'             => 'La habitación',
    'un_apartamento'             => 'una habitación',
    'Un_apartamento'             => 'Una habitación',
    'los_apartamentos'           => 'las habitaciones',
    'Los_apartamentos'           => 'Las habitaciones',

    // Conceptos hoteleros típicos
    'edificio'                   => 'hotel',
    'Edificio'                   => 'Hotel',
    'edificios'                  => 'hoteles',
    'huesped'                    => 'huésped',
    'huespedes'                  => 'huéspedes',
    'reserva'                    => 'reserva',
    'reservas'                   => 'reservas',
    'checkin'                    => 'check-in',
    'checkout'                   => 'check-out',

    // KPIs hoteleros (dashboard)
    'kpi_ocupacion'              => 'Ocupación',
    'kpi_adr'                    => 'Tarifa media (ADR)',
    'kpi_revpar'                 => 'RevPAR',
    'kpi_ingresos_mes'           => 'Ingresos del mes',
    'kpi_estancia_media'         => 'Estancia media',
    'kpi_facturas_pendientes'    => 'Facturas pendientes',
    'kpi_habitaciones_libres'    => 'Habitaciones libres hoy',
    'kpi_checkins_hoy'           => 'Check-ins hoy',
    'kpi_checkouts_hoy'          => 'Check-outs hoy',

    // Labels comunes
    'hero_titulo'                => 'Bienvenido a :hotel',
    'hero_subtitulo'             => 'Panel de gestión hotelera',
    'demo_banner'                => 'MODO DEMO — Los datos son ficticios y ninguna acción envía notificaciones reales.',

];
