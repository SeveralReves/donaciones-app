<?php

namespace Database\Seeders;

use App\Models\DonationType;
use Illuminate\Database\Seeder;

/**
 * Catálogo real, no datos de prueba: estos son los tipos de donación que la
 * aplicación ya usa hoy como texto libre en donations.donation_type y
 * stock_items.donation_type. Los slugs deben coincidir exactamente con esos
 * valores para poder migrarlos en un paso posterior.
 */
class DonationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Insumos médicos', 'slug' => 'insumos_medicos', 'requires_doctor_data' => true],
            ['name' => 'Medicinas', 'slug' => 'medicinas', 'requires_doctor_data' => true],
            ['name' => 'Higiene', 'slug' => 'higiene', 'requires_doctor_data' => false],
            ['name' => 'Alimentos', 'slug' => 'alimentos', 'requires_doctor_data' => false],
            ['name' => 'Misceláneos', 'slug' => 'miscelaneos', 'requires_doctor_data' => false],
            ['name' => 'Otros', 'slug' => 'otros', 'requires_doctor_data' => false],
        ];

        foreach ($types as $type) {
            DonationType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
