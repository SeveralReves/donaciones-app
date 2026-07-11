<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::define('manage-users', fn (User $user): bool => $user->isAdminOrAbove());

        // Un super_admin es intocable desde la interfaz: nadie —ni otro
        // super_admin, ni él mismo— puede editar sus datos, cambiarle el
        // rol, resetearle la contraseña ni desactivarlo. Esa condición se
        // evalúa primero y por el rol ACTUAL del objetivo, sin importar
        // quién sea el actor. Fuera de eso, un super_admin puede administrar
        // usuarios exactamente igual que un admin normal.
        Gate::define('modify-user', function (User $actor, User $target): bool {
            if ($target->isSuperAdmin()) {
                return false;
            }

            return $actor->isAdminOrAbove();
        });

        // Separado de 'manage-users' aunque hoy ambos exigen lo mismo: el
        // inventario es un dominio distinto al de usuarios y podría abrirse
        // a un rol propio (p. ej. bodega) sin tocar el permiso de usuarios.
        Gate::define('manage-stock', fn (User $user): bool => $user->isAdminOrAbove());

        // Acceso de solo lectura al inventario: además de quien ya puede
        // administrarlo (manage-stock), un voluntario puede ver el listado
        // y el historial de ajustes, pero no crear insumos ni modificarlos
        // (eso lo sigue exigiendo manage-stock en cada endpoint de escritura).
        Gate::define('view-stock', fn (User $user): bool => $user->isAdminOrAbove() || $user->rol === 'voluntario');

        // Voluntarios pueden ver y avanzar donaciones, pero no registrar
        // donaciones nuevas — eso requiere reunir datos completos (médico,
        // insumos, etc.) que se espera maneje personal con más contexto.
        Gate::define('create-donations', fn (User $user): bool => $user->rol !== 'voluntario');

        // Módulo aparte del resto del sistema: ni 'admin' entra por defecto,
        // solo quien tenga can_access_children_module en true (o un
        // super_admin, que tiene acceso a todo sin excepción).
        Gate::define('access-children-module', fn (User $user): bool => $user->canAccessChildrenModule());
    }
}
