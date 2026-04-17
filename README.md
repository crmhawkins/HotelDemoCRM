# Hotel Demo CRM

CRM Laravel 10 (PHP 8.3) para **demostración a clientes de hotelería**.
Repositorio independiente, derivado del CRM de producción
[NuevoHeraAppartment](https://github.com/crmhawkins/NuevoHeraAppartment) pero
**NO sincronizado con él**. Todas las integraciones externas están estubeadas.

## Demo en vivo

- **URL**: https://demo.hawkins.es
- **Login**: `demo@hotel.es` / `demo1234`

## Modo DEMO (seguro para enseñar)

Con `DEMO_MODE=true` todas las integraciones externas son mocks:

- WhatsApp Business API (Meta) — deshabilitado
- MIR (Ministerio del Interior) — deshabilitado
- Bankinter scraping — deshabilitado
- Channex (OTA) — deshabilitado
- OpenAI / Hawkins AI — mocks deterministas
- Stripe — claves dummy
- Email — solo a log

Ver `config/demo.php` y `app/Services/DemoModeService.php` para cómo funciona
el sistema de stubs. Guards por servicio con `DemoModeService::isStubbed('whatsapp')` etc.

## Datos demo (seeders)

- 1 edificio "Hotel Demo Costa Sur ****"
- 20 habitaciones con nombres hoteleros
- 50 clientes (Faker es_ES)
- 150 reservas distribuidas en ±6 meses
- 50 facturas consecutivas
- 30 gastos, 10 ingresos
- 21 categorías gasto + 9 ingreso (solo hotelería, sin OBRA)

## Deploy rápido

Ver [`DEMO_DEPLOY.md`](./DEMO_DEPLOY.md) para los pasos completos.

## Regenerar datos demo

```bash
php artisan db:seed --class=DemoHotelSeeder --force
```

## Diferencias respecto al CRM de producción

Este repo parte de `NuevoHeraAppartment` pero NUNCA hace merge de vuelta. Si
hay un bug en código común, se arregla primero en el repo de producción y se
aplica aquí manualmente.

Cambios específicos del demo:

- `config/demo.php` + `app/Services/DemoModeService.php` (sistema de stubs)
- Guards `if (DemoModeService::isStubbed(...))` en integraciones externas
- `resources/lang/es/hotel.php` (traducciones apartamento → habitación)
- `database/seeders/DemoHotelSeeder.php` + `DemoCategoriasSeeder.php`
- Dashboard con hero hotelero y KPIs (ocupación, ADR, etc.)

## Importante

- **Nunca conectar este CRM a APIs reales**. Está diseñado para demo.
- Si necesitas probar cambios nuevos, hazlo en una rama local y pruébalo aquí
  antes de propagarlos al repo de producción.
