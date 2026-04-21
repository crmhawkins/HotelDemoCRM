<?php

/*
|--------------------------------------------------------------------------
| Demo Mode Configuration
|--------------------------------------------------------------------------
|
| When DEMO_MODE=true en el .env, todas las integraciones externas
| (WhatsApp, MIR, Bankinter, Channex, OpenAI, Stripe, Email) devuelven
| respuestas simuladas sin llamar a servicios reales. Usado para demos
| a clientes que no deben poder enviar notificaciones reales.
|
| Helper recomendado:
|   if (config('demo.demo_mode')) { ... stub ... }
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    */
    'demo_mode' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Datos del hotel demo
    |--------------------------------------------------------------------------
    | Usados por seeders y dashboard para branding "Hotel Demo"
    */
    'hotel' => [
        'nombre' => env('DEMO_HOTEL_NOMBRE', 'Hotel Demo Costa Sur'),
        'categoria' => env('DEMO_HOTEL_CATEGORIA', '4 estrellas'),
        'ubicacion' => env('DEMO_HOTEL_UBICACION', 'Costa del Sol, Málaga'),
        'habitaciones_totales' => env('DEMO_HOTEL_HABITACIONES', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kill switches por servicio (granular)
    |--------------------------------------------------------------------------
    | Si demo_mode=true todos se fuerzan a true. Se pueden habilitar
    | individualmente con DEMO_MODE=false + DEMO_DISABLE_X=true.
    */
    'disable' => [
        'whatsapp' => env('DEMO_DISABLE_WHATSAPP', false),
        'mir'      => env('DEMO_DISABLE_MIR', false),
        'bankinter'=> env('DEMO_DISABLE_BANKINTER', false),
        'channex'  => env('DEMO_DISABLE_CHANNEX', false),
        'openai'   => env('DEMO_DISABLE_OPENAI', false),
        'email'    => env('DEMO_DISABLE_EMAIL', false),
        'stripe'   => env('DEMO_DISABLE_STRIPE', false),
        // [2026-04-21] Cerraduras digitales (Tuya X7 y TTLock via Tuyalaravel).
        // El demo no debe llamar a la app Tuyalaravel ni a las cerraduras reales.
        'tuya'     => env('DEMO_DISABLE_TUYA', false),
        'ttlock'   => env('DEMO_DISABLE_TTLOCK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper quick-check
    |--------------------------------------------------------------------------
    | Un servicio queda "stubbed" si demo_mode=true o su disable específico=true.
    | Código: app('demo')->isStubbed('whatsapp')
    */

];
