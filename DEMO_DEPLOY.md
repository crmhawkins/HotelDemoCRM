# Guía de despliegue — Demo Hotel

Rama: `demo/hotel-setup` · Fecha: 2026-04-17

Objetivo: montar una **instancia paralela** del CRM a partir de esta rama,
con `DEMO_MODE=true`, base de datos limpia, datos demo poblados y sin riesgo
de tocar producción (`laravel-f6irzmls5je67llxtivpv7lx`).

---

## 1. Flujo recomendado en Coolify

1. **Nuevo proyecto** en Coolify. Sugerencia de nombre: `hera-demo-hotel`.
2. **Nueva resource**: "Public Repository" apuntando al repo `crmhawkins/NuevoHeraAppartment` rama `demo/hotel-setup`.
3. **Nueva base de datos MariaDB** (nombre sugerido `crm_demo_hotel`). NO reutilizar la base de datos de producción.
4. Configurar el contenedor Laravel con build basado en el `docker-compose.yml` existente (igual que producción).
5. URL sugerida: `demo.crm.apartamentosalgeciras.com` o `hotel-demo.hawkins.es`.

---

## 2. Variables de entorno (.env del contenedor demo)

Copiar todo lo siguiente al `.env` del contenedor recién creado. Las claves
que NO aparezcan aquí pueden mantenerse iguales a producción, pero **cualquier
credencial externa debe ser dummy**.

```env
# --- Laravel base ---
APP_NAME="Hotel Demo"
APP_ENV=production
APP_KEY=  # generar con: docker exec CONTAINER php artisan key:generate
APP_DEBUG=false
APP_URL=https://demo.crm.apartamentosalgeciras.com

# --- DEMO MODE (CRÍTICO) ---
DEMO_MODE=true
DEMO_HOTEL_NOMBRE="Hotel Demo Costa Sur ****"
DEMO_HOTEL_CATEGORIA="4 estrellas"
DEMO_HOTEL_UBICACION="Marbella, Málaga"
DEMO_HOTEL_HABITACIONES=20

# --- Base de datos (nueva, vacía) ---
DB_CONNECTION=mysql
DB_HOST=mariadb-demo   # el nombre del contenedor mariadb de Coolify
DB_PORT=3306
DB_DATABASE=crm_demo_hotel
DB_USERNAME=demo
DB_PASSWORD=generar_password_fuerte

# --- Email: driver log (NO ENVÍA NADA) ---
MAIL_MAILER=log
MAIL_FROM_ADDRESS="demo@hotel-demo.local"
MAIL_FROM_NAME="Hotel Demo"

# --- WhatsApp Meta (dummy, el guard del servicio evita llamadas) ---
META_WHATSAPP_TOKEN=DEMO_TOKEN_INVALID
META_WHATSAPP_PHONE_ID=DEMO_PHONE_ID
META_WHATSAPP_URL=https://graph.facebook.com/v17.0/DEMO/messages

# --- OpenAI (dummy, guard en AIGatewayService devuelve mocks) ---
OPENAI_API_KEY=sk-DEMO-INVALID-KEY
OPENAI_MODEL=gpt-4

# --- Bankinter (dummy, guard en BankinterScraperService no ejecuta puppeteer) ---
BANKINTER_USER=demo
BANKINTER_PASSWORD=demo
BANKINTER_ENABLED=false

# --- Stripe (test mode, NO production keys) ---
STRIPE_KEY=pk_test_DEMO_INVALID
STRIPE_SECRET=sk_test_DEMO_INVALID
STRIPE_WEBHOOK_SECRET=whsec_DEMO

# --- MIR / Ministerio del Interior (dummy, guard en MIRService) ---
MIR_CODIGO_ARRENDADOR=0000000000
MIR_PASSWORD=DEMO_INVALID
MIR_URL=https://hospedajes.ses.mir.es/hospedajes-web/services/hospedajes?wsdl

# --- Channex (dummy, no se usará) ---
CHANNEX_API_KEY=DEMO_INVALID
CHANNEX_API_URL=https://api.channex.io/v1
CHANNEX_WEBHOOK_SECRET=demo

# --- Flags granulares de stub (si quieres desactivar un sólo servicio
#     mientras pruebas, pon DEMO_MODE=false y usa estos). Con DEMO_MODE=true
#     todos se fuerzan a stub automáticamente ---
DEMO_DISABLE_WHATSAPP=true
DEMO_DISABLE_MIR=true
DEMO_DISABLE_BANKINTER=true
DEMO_DISABLE_CHANNEX=true
DEMO_DISABLE_OPENAI=true
DEMO_DISABLE_EMAIL=true
DEMO_DISABLE_STRIPE=true
```

---

## 3. Primera carga (dentro del contenedor)

```bash
# 1. Entrar al contenedor Laravel demo (sustituye CONT por el nombre real)
docker exec -it CONT bash

# 2. Generar APP_KEY si está vacía
php artisan key:generate

# 3. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Ejecutar TODAS las migraciones de cero en la BD demo
php artisan migrate --force

# 5. Correr seeders básicos del sistema (los que ya existen)
php artisan db:seed --class=UsersTableSeeder --force
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=ApartamentoEstadoTableSeeder --force
php artisan db:seed --class=EstadosTableSeeder --force

# 6. Correr seeders DEMO (solo se ejecutan si DEMO_MODE=true)
php artisan db:seed --class=DemoCategoriasSeeder --force
php artisan db:seed --class=DemoHotelSeeder --force

# 7. Crear symlink de storage (por si acaso)
php artisan storage:link

# 8. Re-cachear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Al final deberías ver en el log:
- `DemoCategoriasSeeder: 21 gastos + 9 ingresos`
- `DemoHotelSeeder: completado. 1 edificio, 20 habitaciones, 50 clientes, 150 reservas, 50 facturas, 30 gastos, 10 ingresos`

---

## 4. Verificación rápida que el demo está aislado

1. Navegar a `/admin/dashboard`. Debes ver el **banner amarillo "MODO DEMO"** y el **hero del hotel**.
2. Intentar enviar una notificación WhatsApp manual (ej. crear incidencia). En `storage/logs/laravel.log` debe aparecer `[DEMO STUB] whatsapp::...`. Ningún mensaje real saldrá.
3. Intentar sincronizar Bankinter (menú contabilidad). Debe responder con `"mensaje":"[DEMO] Descarga de movimientos Bankinter simulada"`.
4. Intentar enviar MIR de una reserva. Devuelve lote `DEMOYYYYMMDDHHMMSS` sin SOAP real.
5. `Mail::raw` con driver `log`: los emails van al log, no al servidor SMTP.

---

## 5. Usuarios de acceso

El seeder `UsersTableSeeder` ya crea los admin estándar. Para la demo te sugiero cambiar passwords manualmente una vez creados:

```bash
docker exec CONT php artisan tinker
>>> \App\Models\User::where('email','ADMIN_EMAIL')->first()->update(['password' => bcrypt('demoHotel2026!')])
```

---

## 6. Cosas que NO se han tocado y requieren decisión del usuario

- **Renombrado masivo de "apartamento" → "habitación" en blades**: hay ~157 archivos afectados. Se ha creado `resources/lang/es/hotel.php` con las cadenas base, pero los blades actuales siguen diciendo "apartamento". Dos alternativas:
  1. Usar `@lang('hotel.apartamento_singular_cap')` progresivamente en los blades más visibles (dashboard, menús, listados).
  2. Hacer un `sed`/grep-replace global en `resources/views/` limitándolo a HTML visible. Riesgo alto si afecta atributos.
- **Renombrar tabla `apartamentos` → `habitaciones`**: NO hacerlo. Rompería facturación y MIR. Se queda como está.
- **OpenAI**: aunque `DEMO_MODE=true` corta las llamadas, queda una llamada directa en `app/Services/ClienteService.php` L326/L377 que NO pasa por `AIGatewayService`. Si fuera crítico, migrar esas dos líneas al gateway en otra rama.

---

## 7. Limitaciones conocidas del stub

- **Stripe**: las claves dummy harán que `stripe-php` lance excepciones auth si se intenta un pago. UX puede ser fea si se entra por el flujo de pago; es conveniente **ocultar el botón de pago** en la demo o probar flujos alternativos.
- **Channex webhooks**: la URL del webhook en la config de Channex apunta a producción real. Si alguien configura Channex en el demo es su responsabilidad apuntar a una URL segura.
- **Cron scheduler**: el cron de `app/Console/Kernel.php` sigue activo. Si el demo tiene el cron arrancado, se ejecutarán tareas como `generateBudgetReference` (inofensivas en DEMO) y cualquier `enviar_mir` / `bankinter` quedarán stubbed por los guards.

---

## 8. Checklist final antes de mostrar al cliente

- [ ] `DEMO_MODE=true` en `.env` del contenedor
- [ ] `MAIL_MAILER=log` verificado
- [ ] BD nueva y seeders ejecutados OK
- [ ] Dashboard muestra banner amarillo + hero del hotel
- [ ] Logs `laravel.log` muestran `[DEMO STUB]` cuando se disparan acciones externas
- [ ] Password admin cambiada (no usar la de producción)
- [ ] Dominio HTTPS configurado en Traefik/Coolify
- [ ] No hay ninguna referencia a "Hera Apartments" en la UI visible del dashboard (queda lo que el cliente vea)
- [ ] Probar flujo completo: crear reserva → facturar → intentar enviar al cliente (stub lo bloquea) → crear incidencia (stub bloquea WhatsApp técnico)
