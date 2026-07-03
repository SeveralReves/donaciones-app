<?php

namespace App\Http\Requests;

use App\Models\StockItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDonationRequest extends FormRequest
{
    /**
     * Los voluntarios no pueden registrar donaciones nuevas.
     */
    public function authorize(): bool
    {
        return Gate::allows('create-donations');
    }

    /**
     * Opciones válidas para items.*.unit. Debe mantenerse en sincronía con
     * el select de resources/js/Pages/Donations/Create.vue.
     *
     * @var list<string>
     */
    public const ITEM_UNITS = ['unidades', 'cajas', 'kg', 'litros', 'paquetes', 'frascos'];

    /**
     * Normaliza unidades vacías ('') a null, ya que el select del
     * formulario envía '' para "sin unidad" pero el campo es nullable, no
     * un valor válido de la lista.
     */
    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('items'))) {
            return;
        }

        $this->merge([
            'items' => array_map(function ($item) {
                if (! is_array($item)) {
                    return $item;
                }

                if (($item['unit'] ?? null) === '') {
                    $item['unit'] = null;
                }

                // El select de Create.vue usa '' (nada elegido todavía) y
                // 'otro' (especificar a mano) como valores centinela para
                // "este item no viene del catálogo"; ambos deben guardarse
                // como null, no como el string literal.
                if (in_array($item['stock_item_id'] ?? null, ['', 'otro'], true)) {
                    $item['stock_item_id'] = null;
                }

                return $item;
            }, $this->input('items')),
        ]);
    }

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
            'items.*.stock_item_id' => ['nullable', 'string', Rule::exists('stock_items', 'id')->where('active', true)],
            // Obligatorio solo cuando no viene del catálogo (stock_item_id
            // null); esa condición se valida aparte en withValidator()
            // porque depende del valor de un campo hermano dentro del mismo
            // item del array.
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', Rule::in(self::ITEM_UNITS)],

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
            'items.*.unit.in' => 'La unidad debe ser una de: '.implode(', ', self::ITEM_UNITS).'.',
        ];
    }

    /**
     * Reglas que dependen de más de un campo del mismo item (nombre
     * obligatorio solo si no viene del catálogo; cantidad acotada al stock
     * disponible), así que no se pueden expresar como reglas planas de
     * rules().
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items', []);

            if (! is_array($items)) {
                return;
            }

            $donationType = $this->input('donation_type');
            $requestedByStockItem = [];

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $stockItemId = $item['stock_item_id'] ?? null;

                if (blank($stockItemId)) {
                    if (blank($item['name'] ?? null)) {
                        $validator->errors()->add("items.$index.name", 'El nombre del artículo es obligatorio.');
                    }

                    continue;
                }

                $stockItem = StockItem::find($stockItemId);

                if (! $stockItem) {
                    continue; // ya lo rechaza la regla exists de items.*.stock_item_id
                }

                if ($stockItem->donation_type !== $donationType) {
                    $validator->errors()->add(
                        "items.$index.stock_item_id",
                        "\"{$stockItem->name}\" no pertenece al tipo de donación seleccionado.",
                    );

                    continue;
                }

                // Si el mismo insumo del catálogo aparece en más de una
                // fila, la cantidad pedida es la suma de todas —
                // descontarlas por separado igual dejaría el stock en
                // negativo si cada fila individualmente pasa pero la suma
                // no cabe.
                $requestedByStockItem[$stockItemId] ??= ['stock_item' => $stockItem, 'quantity' => 0, 'index' => $index];
                $requestedByStockItem[$stockItemId]['quantity'] += (float) ($item['quantity'] ?? 0);
            }

            foreach ($requestedByStockItem as $data) {
                if ($data['quantity'] > $data['stock_item']->quantity_available) {
                    $validator->errors()->add(
                        "items.{$data['index']}.quantity",
                        "No hay suficiente stock de \"{$data['stock_item']->name}\": disponible {$data['stock_item']->quantity_available}, solicitado {$data['quantity']}.",
                    );
                }
            }
        });
    }
}
