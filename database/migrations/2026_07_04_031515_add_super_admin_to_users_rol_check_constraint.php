<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mismo caso que la migración que agregó 'voluntario': users.rol es un
 * varchar(255) con CHECK constraint (users_rol_check), no un enum nativo de
 * Postgres, así que hay que rehacer el constraint con raw SQL.
 *
 * 'super_admin' NO se agrega a ningún select ni seeder — el primer (y único
 * por ahora) super_admin se asigna a mano por tinker, directamente en base
 * de datos. Esta migración solo permite que el valor exista en la columna.
 */
return new class extends Migration
{
    private const OLD_VALUES = ['admin', 'medico', 'odontologo', 'voluntario'];

    private const NEW_VALUES = ['admin', 'medico', 'odontologo', 'voluntario', 'super_admin'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_rol_check');
        DB::statement($this->buildCheckConstraintSql(self::NEW_VALUES));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_rol_check');
        DB::statement($this->buildCheckConstraintSql(self::OLD_VALUES));
    }

    /**
     * @param  list<string>  $values
     */
    private function buildCheckConstraintSql(array $values): string
    {
        $list = implode(', ', array_map(fn (string $value) => "'{$value}'::character varying", $values));

        return "ALTER TABLE users ADD CONSTRAINT users_rol_check CHECK (rol::text = ANY (ARRAY[{$list}]::text[]))";
    }
};
