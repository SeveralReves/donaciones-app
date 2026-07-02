<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['insumos_medicos', 'higiene', 'alimentos', 'otros']);
        $isMedical = $type === 'insumos_medicos';

        return [
            'patient_name' => fake()->optional()->name(),
            'donation_type' => $type,
            'location' => fake()->city(),
            'receiving_doctor_name' => $isMedical ? fake()->name() : null,
            'receiving_doctor_code' => $isMedical ? 'MED-'.fake()->unique()->numberBetween(100, 999) : null,
            'receiving_service' => $isMedical
                ? fake()->randomElement(['Pediatria', 'Cirugia', 'Urgencias', 'Medicina Interna'])
                : null,
            'contact_number' => fake()->optional()->phoneNumber(),
            'cedula' => fake()->optional()->numerify('#########'),
            'vehicle_type' => fake()->optional()->randomElement(['Moto', 'Carro', 'Camioneta']),
            'delivery_name' => fake()->optional()->name(),
            'delivery_contact' => fake()->optional()->phoneNumber(),
            'status' => fake()->randomElement([
                'creada', 'en_proceso', 'esperando_delivery', 'en_camino', 'recibido',
            ]),
            'created_by' => User::factory(),
        ];
    }
}
