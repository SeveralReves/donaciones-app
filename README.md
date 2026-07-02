# Control de Donaciones

Aplicación web para registrar y dar seguimiento a donaciones (alimentos, higiene, insumos médicos, otros) desde que se crean hasta que llegan a quien las recibe: estado, artículos, responsables, delivery y notificación por WhatsApp.

## Stack

- **Backend:** Laravel 13 (PHP ^8.3), PostgreSQL.
- **Frontend:** Inertia.js + Vue 3 (`<script setup>`), sin SPA router propio — las páginas son componentes en `resources/js/Pages`.
- **Estilos:** SCSS propio con metodología BEM (`resources/sass`). **No se usa Tailwind, Bootstrap ni ningún framework de CSS.**
- **Build:** Vite.
- **PWA:** instalable (manifest + service worker mínimo que solo cachea el shell estático). Sin soporte offline de datos todavía.

## Requisitos

- PHP >= 8.3, Composer
- Node >= 18, npm
- PostgreSQL

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurá la conexión a PostgreSQL en `.env` (`DB_*`). Para el primer usuario admin, definí `ADMIN_EMAIL` y `ADMIN_PASSWORD` en `.env` (fuera de `local` son obligatorias; el seeder falla si faltan).

```bash
php artisan migrate --seed
npm install
npm run build
```

## Desarrollo

```bash
composer run dev
```

Levanta en paralelo: servidor PHP (`artisan serve`), worker de colas, `artisan pail` (logs) y Vite en modo watch.

## Tests

```bash
composer test
```

## Estructura relevante

```
app/Http/Controllers/          Dashboard, Donation, Admin/User
app/Services/DonationStatusFlow.php   única fuente de verdad de la secuencia de estados
                                       y los campos requeridos para avanzar a cada uno
app/Models/                    Donation, DonationItem, DonationStatusLog, MedicalReceiver, User
resources/js/Pages/            Dashboard, Donations/{Index,Create,Show}, Admin/Users/*, Auth/*, Profile/*
resources/js/Layouts/          AuthenticatedLayout (header verde + nav), GuestLayout (login)
resources/sass/                Design system BEM: abstracts (tokens), base, layout, components
public/manifest.json, sw.js    PWA
```

## Dominio

**Estados de una donación** (secuencia fija, ver `DonationStatusFlow`):
`creada` → `en_proceso` → `esperando_delivery` → `en_camino` → `recibido`

**Tipos de donación:** `insumos_medicos`, `higiene`, `alimentos`, `otros` (los insumos médicos exigen datos del médico/servicio receptor).

**Roles de usuario:** `admin` (gestiona usuarios, `Gate::define('manage-users', ...)`), `medico`, `odontologo`.

**WhatsApp:** desde el detalle de una donación se puede enviar un mensaje prearmado al delivery o a quien recibe (`resources/js/utils/whatsapp.js`), abriendo `wa.me` con el número normalizado.

## Convenciones de estilos

Todo el CSS vive en `resources/sass/` con metodología BEM (`bloque__elemento--modificador`). Componentes reutilizables (botones, campos, tarjetas, chips, avatares, tablas) están en `resources/sass/components/`; el layout de página en `resources/sass/layout/`. Las páginas Vue pueden agregar un `<style scoped>` propio para su layout específico, siempre con clases BEM — nunca clases utilitarias tipo Tailwind.
