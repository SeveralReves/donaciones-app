<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Crea el primer usuario admin a partir de las variables de entorno
     * ADMIN_EMAIL y ADMIN_PASSWORD. En producción esas variables son
     * obligatorias: el seeder falla si no están definidas fuera de un
     * entorno local, para evitar crear un admin con credenciales
     * predecibles. Los valores de abajo son solo un fallback de desarrollo
     * local y nunca deben usarse en producción.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (! $adminEmail || ! $adminPassword) {
            if (! app()->environment('local')) {
                throw new \RuntimeException(
                    'ADMIN_EMAIL y ADMIN_PASSWORD deben estar definidas antes de correr el seeder en este entorno.'
                );
            }

            $adminEmail = $adminEmail ?: 'admin@example.com';
            $adminPassword = $adminPassword ?: 'password';
        }

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador',
                'password' => $adminPassword,
                'rol' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
