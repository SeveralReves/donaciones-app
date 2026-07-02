<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requiresDoctor = $this->input('donation_type') === 'insumos_medicos';

        return [
            'donation_type' => ['required', Rule::in(['insumos_medicos', 'higiene', 'alimentos', 'otros'])],
            'location' => ['required', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.unit' => ['nullable', 'string', 'max:255'],

            'patient_name' => ['nullable', 'string', 'max:255'],
            'receiving_doctor_name' => ['nullable', 'string', 'max:255'],
            'receiving_doctor_code' => [Rule::requiredIf($requiresDoctor), 'nullable', 'string', 'max:255'],
            'receiving_service' => [Rule::requiredIf($requiresDoctor), 'nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiving_doctor_code.required' => 'El código del médico es obligatorio cuando la donación es de insumos médicos.',
            'receiving_service.required' => 'El servicio del médico es obligatorio cuando la donación es de insumos médicos.',
        ];
    }
}
