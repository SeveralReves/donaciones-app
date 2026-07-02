# CLAUDE.md

Instrucciones de proyecto para Claude Code. El detalle completo de arquitectura, convenciones y dominio está en [AGENTS.md](AGENTS.md) (compartido con cualquier otro agente de IA que trabaje acá) — leelo antes de tocar código. Este archivo solo resume lo más importante y agrega notas específicas de Claude Code.

## Lo más importante, resumido

- **Nunca uses Tailwind, Bootstrap ni clases utilitarias de un framework de CSS.** Todo el CSS es propio, con metodología BEM, en `resources/sass/`. Se desinstaló Tailwind del proyecto a propósito — no lo reintroduzcas ni copies clases tipo `flex`, `px-4`, `text-gray-500`. Ver la sección correspondiente en `AGENTS.md` antes de escribir o tocar cualquier `.vue`.
- La secuencia de estados de una donación y los campos requeridos para avanzar viven **solo** en `app/Services/DonationStatusFlow.php`. No la reimplementes en otro lado.
- Las etiquetas en español (estados, tipos, roles) están centralizadas en `resources/js/utils/labels.js`.
- Los filtros de `Donations/Index.vue` son server-side (query params + `router.get`), no client-side.

## Notas de sesión

- El stack de diseño se migró completo de Tailwind a SCSS/BEM en una sesión larga; si encontrás alguna clase Tailwind residual en un `.vue`, es un descuido y hay que convertirla, no un patrón a imitar.
- `resources/sass/components/_field.scss` + `.btn-breeze` son el look neutro heredado de Breeze (Perfil, Admin de usuarios, Recuperar/Restablecer/Confirmar contraseña, Verificar email). `.form-field` + `.btn` (verde) son el look de marca (Panel, Donaciones, Nueva donación, Usuarios, y ahora Login). No mezcles ambas familias en la misma pantalla salvo que te pidan explícitamente unificar todo.
- Antes de dar por terminado un cambio visual, compilá (`npm run build`) y verificalo corriendo la app real (`php artisan serve` + navegador o Playwright headless) — no alcanza con que compile sin errores.
