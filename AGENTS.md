# AGENTS.md

Guía para agentes de IA (Claude Code, Cursor, Copilot, Codex, etc.) que trabajen en este repo. Para instalación y comandos básicos ver [README.md](README.md).

## Qué es esto

Control de Donaciones: Laravel 13 + Inertia.js + Vue 3, PostgreSQL. No hay una API REST separada ni SPA router propio — cada "página" es un componente Vue en `resources/js/Pages` que un controlador renderiza vía `Inertia::render(...)`, y la navegación entre páginas la maneja Inertia (`<Link>`, `router.get(...)`).

## Regla más importante: sin frameworks de CSS

**No uses Tailwind, Bootstrap ni ninguna clase utilitaria de un framework de CSS.** El proyecto migró explícitamente de Tailwind a CSS propio con metodología **BEM** (`bloque__elemento--modificador`). No agregues clases como `flex`, `px-4`, `text-gray-500`, `rounded-lg`, `md:hidden`, etc. — ni siquiera aunque Vue/Vite tengan Tailwind instalable como dependencia (no lo está: se desinstaló por completo, ver `package.json`).

Antes de escribir CSS nuevo:
1. Revisá si ya existe un componente BEM reutilizable en `resources/sass/components/` (`.btn`, `.form-field`, `.chip`, `.avatar`, `.card`/`.donation-card`, `.surface`, `.panel`, `.stat`, `.dropdown`, `.modal`, `.alert`, `.link`, etc.).
2. Si el estilo es específico de una sola página, agregá un `<style scoped>` en ese `.vue` con clases BEM propias del bloque de esa página (ej. `.donation-form__grid`, `.dashboard__status-row`).
3. Si es un patrón que se repite en 2+ páginas, promovelo a un componente global nuevo en `resources/sass/components/` y registralo con `@use` en `resources/sass/app.scss`.

Hay dos familias de componentes de formulario/botón que **no son intercambiables**:
- `.form-field` / `.btn` / `.chip` (verde, marca de la app) — usado en Panel, Donaciones, Nueva donación, Usuarios.
- `.field` / `.btn-breeze` (gris/índigo, look heredado de Breeze) — usado en Login (parcialmente, ver abajo), Recuperar/Restablecer/Confirmar contraseña, Verificar email, Perfil, Alta/Edición de usuario admin.

Si tocás una pantalla del segundo grupo, mantené su estilo neutro salvo que te pidan explícitamente rediseñarla (así se decidió Login: ahora tiene su propio `<style scoped>` con la marca verde, sin usar `GuestLayout` ni los componentes Breeze compartidos, precisamente para no arrastrar el cambio a Perfil/Admin de usuarios).

Tokens de diseño (colores, tipografía, radios, breakpoints) están en `resources/sass/abstracts/_variables.scss` y `_mixins.scss` (`@include respond(tablet|desktop)`, mobile-first).

## Dominio

- **Estados de donación** (secuencia fija): `creada` → `en_proceso` → `esperando_delivery` → `en_camino` → `recibido`. La única fuente de verdad de la secuencia y de qué campos exige cada transición es `app/Services/DonationStatusFlow.php` — no dupliques esa lógica en el frontend ni en otro sitio del backend.
- **Tipos de donación:** `insumos_medicos`, `higiene`, `alimentos`, `otros`. Las etiquetas en español viven en `resources/js/utils/labels.js` (`DONATION_STATUS_LABELS`, `DONATION_TYPE_LABELS`, `USER_ROLE_LABELS`) — usá esas funciones (`donationStatusLabel`, `donationTypeLabel`, `userRoleLabel`) en vez de hardcodear texto.
- **Roles:** `admin`, `medico`, `odontologo`. La gestión de usuarios está gateada por `Gate::define('manage-users', ...)` en `AppServiceProvider` (solo `admin`).
- Los IDs de `Donation` son UUID ordenables (`HasUuids`), no autoincrementales.
- WhatsApp: `resources/js/utils/whatsapp.js` arma los mensajes y normaliza teléfonos venezolanos a formato internacional para `wa.me`. Solo se usa en `Donations/Show.vue`.

## Filtros de Donaciones

`Donations/Index.vue` filtra por estado (chips clicables, no un `<select>`), tipo, ubicación (con debounce de 300ms) y rango de fechas, todo vía `router.get(route('donations.index'), filters, { preserveState: true, replace: true })` — es decir, son query params de servidor, no un filtro client-side sobre datos ya cargados. El controlador (`DonationController@index`) calcula `statusCounts` aplicando los filtros de tipo/ubicación/fecha pero **no** el de estado, para que los contadores de los chips reflejen "cuántas hay en cada estado dado el resto de filtros activos". Si agregás un filtro nuevo, replicá ese patrón en vez de filtrar en el cliente.

## PWA

`public/manifest.json` + `public/sw.js`. El service worker cachea **solo** el shell estático (JS/CSS de Vite bajo `/build/`, íconos, el manifest) con estrategia cache-first, leyendo `public/build/manifest.json` (el manifest de Vite, con hashes de build) en el evento `install` para saber qué precachear — nunca hardcodees nombres de archivo con hash. **No agregues cacheo de rutas dinámicas** (`/donations`, `/admin/users`, cualquier respuesta de Inertia o de formularios) sin implementar antes una estrategia real de sincronización/invalidación; eso quedó explícitamente para una fase 2.

## Comandos

```bash
composer run dev     # server + queue + logs + vite, todo junto
npm run build         # build de producción (correr antes de verificar visualmente sin `composer run dev`)
composer test         # php artisan test
```

## Verificar cambios de UI

Este proyecto no tiene tests de UI automatizados. Para verificar un cambio visual: `npm run build`, levantar `php artisan serve`, y revisar en navegador (o headless vía Playwright) las pantallas afectadas — hay datos de prueba reales vía `DonationSeeder` y un admin creado por `ADMIN_EMAIL`/`ADMIN_PASSWORD` (o `admin@example.com` / `password` en local si no se definieron). `route()` (Ziggy) genera URLs **absolutas**, tenerlo en cuenta si escribís selectores tipo `a[href="/donations"]` en un script de verificación — no van a matchear.
