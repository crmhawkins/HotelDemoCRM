# HotelDemoCRM — Memoria del proyecto

Archivo de memoria local para Claude en futuras conversaciones.
Última actualización: 2026-04-17.

---

## 1. Qué es este proyecto

Copia **independiente** del CRM de producción `NuevoHeraAppartment`, orientada
a demostraciones a clientes de hotelería. Todas las integraciones externas
están estubeadas para que no pueda enviar mensajes, hacer scraping, ni
contactar con APIs reales.

**NO compartir historia git con el repo de producción**. Los arreglos viajan
solo en sentido producción → demo, manualmente.

## 2. Infraestructura

### Servidor externo (Coolify + Traefik)
- **IP**: 217.160.39.81
- **SSH**: `ssh -i ~/.ssh/hawcert_server claude@217.160.39.81`
- **Carpeta**: `/home/claude/demo-hotel/`
- **Contenedores**:
  - `demo-hotel-app` (serversideup/php:8.3-fpm-nginx) — en red `coolify`
  - `demo-hotel-db` (mariadb:10.11) — en red `demo-hotel-internal`
- **URL pública**: https://demo.hawkins.es (Traefik + Let's Encrypt auto)

### Credenciales demo (NO sensibles, cualquiera puede probar)
- Email: `demo@hotel.es`
- Password: `demo1234`
- Rol: admin

## 3. Flujo de deploy (actualizar demo con nuevos cambios)

```bash
# Local: commit + push al repo
cd D:\proyectos\programasivan\HotelDemoCRM
git add .
git commit -m "..."
git push origin main

# Servidor: pull + recargar
ssh -i ~/.ssh/hawcert_server claude@217.160.39.81
cd ~/demo-hotel/app && git pull
docker exec -u 9999 demo-hotel-app sh -c 'cd /var/www/html && php artisan config:clear && php artisan view:clear'
```

## 4. Modo DEMO — cómo funciona

Flag `DEMO_MODE=true` en `.env` activa los stubs. Cada servicio tiene su
propio flag granular (`DEMO_DISABLE_WHATSAPP`, etc.) por si se quisiera
activar uno en concreto.

El gate se hace con `\App\Services\DemoModeService::isStubbed('nombre')`.
Servicios cubiertos:
- `whatsapp` — WhatsappNotificationService, TecnicoNotificationService
- `mir` — MIRService::enviarReserva
- `bankinter` — BankinterScraperService::descargarMovimientos
- `channex` — (pending si se usa)
- `ai` — AIGatewayService::chatCompletion (mocks deterministas)
- `stripe` — dummy keys
- Email → `MAIL_MAILER=log` en .env

## 5. Seeders

- `database/seeders/DemoCategoriasSeeder.php` — 21 categorías gasto + 9 ingreso hoteleras (TRUNCATE + insert)
- `database/seeders/DemoHotelSeeder.php` — 1 hotel, 20 habs, 50 clientes, 150 reservas, 50 facturas, 30 gastos, 10 ingresos

Para regenerar datos:
```bash
docker exec -u 9999 demo-hotel-app sh -c 'cd /var/www/html && php artisan db:seed --class=DemoHotelSeeder --force'
```

## 6. Tablas de catálogo importadas de producción

El demo usa el schema de producción (137 tablas) + estos catálogos copiados
para satisfacer FKs de los seeders:

- `estados` (10 filas)
- `estados_gastos` (3)
- `estados_ingresos` (3)
- `bank_accounts` (2 — BANKINTER, CAJA)
- `invoices_status` (7)
- `apartamento_estado` (4 — Sucio, En Limpieza, Limpio, No realizada)

Si el `DemoHotelSeeder` falla por una FK nueva, identifica qué catálogo
falta y cópialo de la producción (servidor interno `217.160.39.79`, container
`laravel-f6irzmls5je67llxtivpv7lx`, DB `crm_apartamentos`).

## 7. Operación del contenedor

```bash
# Parar
cd ~/demo-hotel && docker compose down

# Arrancar
cd ~/demo-hotel && docker compose up -d

# Logs app
docker compose logs -f demo-hotel-app

# Entrar al container
docker exec -it -u 9999 demo-hotel-app sh

# Resetear base de datos
docker exec demo-hotel-db sh -c 'mariadb -uroot -pdemoroot -e "DROP DATABASE demo_hotel; CREATE DATABASE demo_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON demo_hotel.* TO demo@\"%\";"'
# Luego importar schema + seeders
```

## 8. TODO pendientes

- [ ] Migrar progresivamente blades a `@lang('hotel.X')` en vez de textos "apartamento" hardcoded (~157 blades)
- [ ] Revisar código muerto de integraciones externas (si en futuro se añaden más, aplicar guard DemoModeService)
- [ ] Si quieres endurecer: cambiar permisos de `storage/` y `bootstrap/cache/` (hoy 777, simple para demo)
- [ ] ClienteService.php tiene 2 llamadas directas a OpenAI en L326, L377 sin pasar por AIGatewayService — añadir guard si se activan

## 9. Dónde NO tocar

- **NO** hacer pull request/merge al repo `NuevoHeraAppartment`. Este es un
  repo independiente permanentemente.
- **NO** activar ningún `DEMO_DISABLE_*=false` en producción — equivale a
  conectar el demo con APIs reales.
- Cualquier cambio al schema de `invoices.reference` debe respetar que el
  CRM de producción sigue usando el mismo código (hay commits compartidos
  desde el punto de divergencia).

---

## 10. Origen histórico

Este repo nace como snapshot del branch `demo/hotel-setup` de
`NuevoHeraAppartment` en el commit `814f650`, el 2026-04-17. La rama origen
fue eliminada del repo principal tras la migración.
