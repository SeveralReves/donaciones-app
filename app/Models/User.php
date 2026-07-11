<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol', 'active', 'can_access_children_module', 'codigo_medico', 'servicio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'can_access_children_module' => 'boolean',
        ];
    }

    /**
     * 'super_admin' no es un rol asignable desde la interfaz (se asigna una
     * sola vez a mano, por tinker) — este helper centraliza la comparación
     * del string en un solo lugar para que las Gates de app/Providers/
     * AppServiceProvider.php no lo repitan.
     */
    public function isSuperAdmin(): bool
    {
        return $this->rol === 'super_admin';
    }

    /**
     * super_admin tiene todo el acceso de un admin normal, más su propia
     * protección especial (ver Gate 'modify-user'). Centraliza el "admin O
     * super_admin" para que las Gates que dan acceso a secciones enteras
     * (manage-users, manage-stock) no repitan la comparación — si alguna
     * vuelve a comparar 'admin' a mano y se olvida de super_admin, ese es
     * justo el bug que este helper evita.
     */
    public function isAdminOrAbove(): bool
    {
        return $this->rol === 'admin' || $this->isSuperAdmin();
    }

    /**
     * El módulo de niños es un dominio aparte del resto de la app: ni
     * siquiera 'admin' entra por defecto. Un super_admin sí, siempre — igual
     * que con cualquier otro módulo — porque tiene acceso a todo sin
     * excepción.
     */
    public function canAccessChildrenModule(): bool
    {
        return $this->isSuperAdmin() || $this->can_access_children_module;
    }
}
