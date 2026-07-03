<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * users.rol se creó como Blueprint::enum(), que en Postgres no es un tipo
 * enum nativo sino un varchar(255) con un CHECK constraint (users_rol_check)
 * — no hay forma de "agregar un valor" a eso con el schema builder, hay que
 * rehacer el constraint con raw SQL.
 */
return new class extends Migration
{
    private const OLD_VALUES = ['admin', 'medico', 'odontologo'];

    private const NEW_VALUES = ['admin', 'medico', 'odontologo', 'voluntario'];

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
