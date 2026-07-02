<?php

namespace Database\Seeders;

use App\Models\Donation;
use App\Models\DonationItem;
use App\Models\MedicalReceiver;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Datos de prueba, no pensados para producción. Corre con:
 *   php artisan db:seed --class=DonationSeeder
 */
class DonationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $users = collect([
                User::factory()->create([
                    'name' => 'Admin Demo',
                    'email' => 'admin-demo@example.com',
                    'rol' => 'admin',
                ]),
                User::factory()->create([
                    'name' => 'Medico Demo',
                    'email' => 'medico-demo@example.com',
                    'rol' => 'medico',
                    'codigo_medico' => 'MED-000',
                    'servicio' => 'General',
                ]),
            ]);
        }

        Donation::factory()
            ->count(20)
            ->make()
            ->each(function (Donation $donation) use ($users): void {
                $donation->created_by = $users->random()->id;
                $donation->save();

                DonationItem::factory()
                    ->count(fake()->numberBetween(1, 4))
                    ->create(['donation_id' => $donation->id]);

                $donation->statusLogs()->create([
                    'from_status' => null,
                    'to_status' => 'creada',
                    'changed_by' => $donation->created_by,
                    'changed_at' => $donation->created_at,
                ]);

                if ($donation->status !== 'creada') {
                    $donation->statusLogs()->create([
                        'from_status' => 'creada',
                        'to_status' => $donation->status,
                        'changed_by' => $users->random()->id,
                        'changed_at' => now(),
                    ]);
                }

                if ($donation->receiving_doctor_code) {
                    MedicalReceiver::updateOrCreate(
                        ['codigo_medico' => $donation->receiving_doctor_code],
                        [
                            'name' => $donation->receiving_doctor_name ?? '',
                            'servicio' => $donation->receiving_service,
                        ],
                    );
                }
            });
    }
}
