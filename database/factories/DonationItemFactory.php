<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\DonationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationItem>
 */
class DonationItemFactory extends Factory
{
    protected $model = DonationItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'name' => fake()->randomElement([
                'Guantes', 'Jeringas', 'Gasas', 'Jabon', 'Alcohol',
                'Arroz', 'Aceite', 'Pasta dental', 'Cepillo dental', 'Suero',
            ]),
            'quantity' => fake()->randomFloat(2, 1, 100),
            'unit' => fake()->randomElement(['unidades', 'cajas', 'paquetes', 'litros', 'kg']),
        ];
    }
}
