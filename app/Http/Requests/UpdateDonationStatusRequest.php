<?php

namespace App\Http\Requests;

use App\Models\Donation;
use App\Services\DonationStatusFlow;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDonationStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $donation = $this->route('donation');

        $rules = [
            'status' => ['required', Rule::in(DonationStatusFlow::allStatuses())],
        ];

        foreach (DonationStatusFlow::requiredFields($donation, $this->input('status', '')) as $field) {
            $rules[$field] = [
                Rule::requiredIf(blank($donation->{$field})),
                'nullable', 'string', 'max:255',
            ];
        }

        foreach (DonationStatusFlow::optionalFields($donation, $this->input('status', '')) as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return DonationStatusFlow::fieldLabels();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Donation $donation */
            $donation = $this->route('donation');
            $target = $this->input('status');

            // Cancelar es una transición aparte de la secuencia normal: se
            // permite desde cualquier estado salvo 'recibido'/'cancelada',
            // no solo desde "el siguiente" como el resto de los estados.
            if ($target === DonationStatusFlow::CANCELLED) {
                if (! DonationStatusFlow::canCancel($donation->status)) {
                    $validator->errors()->add(
                        'status',
                        "No se puede cancelar una donación en estado '{$donation->status}'."
                    );
                }

                return;
            }

            $expected = DonationStatusFlow::nextStatus($donation->status);

            if ($target !== $expected) {
                $validator->errors()->add(
                    'status',
                    $expected
                        ? "No se puede pasar de '{$donation->status}' a '{$target}'. El único siguiente estado válido es '{$expected}'."
                        : "La donación ya está en su estado final ('{$donation->status}') y no puede avanzar más."
                );
            }
        });
    }
}
