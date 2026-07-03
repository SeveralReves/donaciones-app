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

        Gate::define('manage-users', fn (User $user): bool => $user->rol === 'admin');

        // Separado de 'manage-users' aunque hoy ambos exigen lo mismo: el
        // inventario es un dominio distinto al de usuarios y podría abrirse
        // a un rol propio (p. ej. bodega) sin tocar el permiso de usuarios.
        Gate::define('manage-stock', fn (User $user): bool => $user->rol === 'admin');

        // Voluntarios pueden ver y avanzar donaciones, pero no registrar
        // donaciones nuevas — eso requiere reunir datos completos (médico,
        // insumos, etc.) que se espera maneje personal con más contexto.
        Gate::define('create-donations', fn (User $user): bool => $user->rol !== 'voluntario');
    }
}
