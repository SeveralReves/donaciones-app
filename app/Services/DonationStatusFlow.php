<?php

namespace App\Services;

use App\Models\Donation;

/**
 * Única fuente de verdad para la secuencia de estados de una donación y los
 * campos que se requieren para avanzar a cada uno. Tanto la validación
 * (UpdateDonationStatusRequest) como la vista de detalle (para saber qué
 * formulario mostrar) consultan esta clase, así las reglas no se duplican.
 */
class DonationStatusFlow
{
    /**
     * @var list<string>
     */
    public const SEQUENCE = [
        'creada',
        'en_proceso',
        'esperando_delivery',
        'en_camino',
        'recibido',
    ];

    /**
     * El único estado al que se puede avanzar desde el actual, o null si ya
     * está en el último estado de la secuencia.
     */
    public static function nextStatus(string $currentStatus): ?string
    {
        $index = array_search($currentStatus, self::SEQUENCE, true);

        if ($index === false || ! isset(self::SEQUENCE[$index + 1])) {
            return null;
        }

        return self::SEQUENCE[$index + 1];
    }

    /**
     * Campos que deben tener valor en la donación para poder avanzar a
     * $toStatus. Depende del estado de la donación (p. ej. donation_type)
     * para las reglas condicionales.
     *
     * @return list<string>
     */
    public static function requiredFields(Donation $donation, string $toStatus): array
    {
        return match ($toStatus) {
            'en_proceso' => [
                'receiving_doctor_name',
                ...($donation->donation_type === 'insumos_medicos'
                    ? ['receiving_doctor_code', 'receiving_service']
                    : []),
            ],
            'esperando_delivery' => ['vehicle_type', 'delivery_name'],
            default => [],
        };
    }

    /**
     * De los campos requeridos para $toStatus, cuáles siguen sin valor tanto
     * en la donación como en los datos entrantes (p. ej. los que el usuario
     * está a punto de enviar en el formulario de avance).
     *
     * @param  array<string, mixed>  $incoming
     * @return list<string>
     */
    public static function missingFields(Donation $donation, string $toStatus, array $incoming = []): array
    {
        return array_values(array_filter(
            self::requiredFields($donation, $toStatus),
            fn (string $field) => blank($donation->{$field}) && blank($incoming[$field] ?? null),
        ));
    }

    /**
     * Campos que se pueden completar al avanzar a $toStatus pero que no
     * bloquean el avance si faltan (a diferencia de requiredFields). Solo se
     * muestran/aceptan si la donación todavía no tiene valor para ellos.
     *
     * @return list<string>
     */
    public static function optionalFields(Donation $donation, string $toStatus): array
    {
        $fields = match ($toStatus) {
            'esperando_delivery' => ['delivery_contact'],
            default => [],
        };

        return array_values(array_filter(
            $fields,
            fn (string $field) => blank($donation->{$field}),
        ));
    }

    /**
     * Etiquetas legibles para los campos que puede pedir el flujo, usadas
     * tanto en mensajes de validación como en la UI.
     *
     * @return array<string, string>
     */
    public static function fieldLabels(): array
    {
        return [
            'receiving_doctor_name' => 'nombre del médico o responsable que recibe',
            'receiving_doctor_code' => 'código del médico',
            'receiving_service' => 'servicio del médico',
            'vehicle_type' => 'tipo de vehículo',
            'delivery_name' => 'nombre de quien entrega',
            'delivery_contact' => 'contacto del delivery (opcional)',
        ];
    }
}
