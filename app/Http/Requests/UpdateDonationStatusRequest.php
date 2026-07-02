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
            'status' => ['required', Rule::in(DonationStatusFlow::SEQUENCE)],
        ];

        foreach (DonationStatusFlow::requiredFields($donation, $this->input('status', '')) as $field) {
            $rules[$field] = [
                Rule::requiredIf(blank($donation->{$field})),
                'nullable', 'string', 'max:255',
            ];
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
            $expected = DonationStatusFlow::nextStatus($donation->status);

            if ($this->input('status') !== $expected) {
                $validator->errors()->add(
                    'status',
                    $expected
                        ? "No se puede pasar de '{$donation->status}' a '{$this->input('status')}'. El único siguiente estado válido es '{$expected}'."
                        : "La donación ya está en su estado final ('{$donation->status}') y no puede avanzar más."
                );
            }
        });
    }
}
