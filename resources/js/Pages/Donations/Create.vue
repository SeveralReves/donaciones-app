<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const donationTypes = [
    { value: 'insumos_medicos', label: 'Insumos médicos' },
    { value: 'higiene', label: 'Higiene' },
    { value: 'alimentos', label: 'Alimentos' },
    { value: 'otros', label: 'Otros' },
];

const itemUnits = ['unidades', 'cajas', 'kg', 'litros', 'paquetes', 'frascos'];

const form = useForm({
    donation_type: 'insumos_medicos',
    location: '',
    patient_name: '',
    receiving_doctor_name: '',
    receiving_doctor_code: '',
    receiving_service: '',
    contact_number: '',
    cedula: '',
    items: [{ name: '', quantity: '', unit: '' }],
});

const requiresDoctor = computed(
    () => form.donation_type === 'insumos_medicos',
);

const addItem = () => {
    form.items.push({ name: '', quantity: '', unit: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('donations.store'));
};
</script>

<template>
    <Head title="Nueva donación" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-[720px] px-4 py-6 sm:px-6">
            <Link
                :href="route('donations.index')"
                class="mb-3 inline-flex items-center gap-1.5 text-sm font-semibold text-[#148f5b] hover:underline"
            >
                <span class="text-base leading-none">←</span> Volver a donaciones
            </Link>

            <h1 class="mb-1">Nueva donación</h1>
            <p class="mb-[22px] text-sm font-normal text-[#7a8b84]">
                Registra qué se dona y a dónde va
            </p>

            <div class="rounded-[18px] border border-[#e6ede9] bg-white px-6 py-7">
                <form @submit.prevent="submit">
                    <div class="mb-[11px] text-sm font-semibold">¿Qué tipo de donación es?</div>
                    <div class="mb-6 flex flex-wrap gap-[9px]">
                        <button
                            v-for="type in donationTypes"
                            :key="type.value"
                            type="button"
                            class="chip"
                            :class="{ 'chip--active': form.donation_type === type.value }"
                            @click="form.donation_type = type.value"
                        >
                            {{ type.label }}
                        </button>
                    </div>
                    <p v-if="form.errors.donation_type" class="form-field__error -mt-4 mb-4">
                        {{ form.errors.donation_type }}
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="form-field">
                            <label for="location" class="form-field__label">Ubicación</label>
                            <input
                                id="location"
                                v-model="form.location"
                                type="text"
                                placeholder="Refugio, hospital o dirección"
                                class="form-field__input"
                                required
                            />
                            <p v-if="form.errors.location" class="form-field__error">
                                {{ form.errors.location }}
                            </p>
                        </div>

                        <div class="form-field">
                            <label for="patient_name" class="form-field__label">Nombre del beneficiario</label>
                            <input
                                id="patient_name"
                                v-model="form.patient_name"
                                type="text"
                                placeholder="Nombre del paciente"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.patient_name" class="form-field__error">
                                {{ form.errors.patient_name }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-[18px] rounded-2xl border px-[18px] pb-1.5 pt-[18px]"
                        :class="requiresDoctor ? 'border-[#f7d7d2] bg-[#fef4f2]' : 'border-[#e6ede9] bg-[#f8faf9]'"
                    >
                        <div
                            class="mb-3.5 flex items-center gap-2 text-[13px] font-semibold"
                            :class="requiresDoctor ? 'text-[#c0392b]' : 'text-[#5a686d]'"
                        >
                            <span
                                v-if="requiresDoctor"
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-[#c0392b] text-xs text-white"
                            >!</span>
                            Médico / responsable que recibe
                            <span v-if="requiresDoctor">· obligatorio para insumos médicos</span>
                        </div>
                        <div class="mb-3.5 grid gap-3.5 sm:grid-cols-2">
                            <div class="form-field !mb-0">
                                <label for="receiving_doctor_name" class="form-field__label">Nombre</label>
                                <input
                                    id="receiving_doctor_name"
                                    v-model="form.receiving_doctor_name"
                                    type="text"
                                    placeholder="Nombre del médico"
                                    class="form-field__input"
                                />
                                <p v-if="form.errors.receiving_doctor_name" class="form-field__error">
                                    {{ form.errors.receiving_doctor_name }}
                                </p>
                            </div>

                            <div v-if="requiresDoctor" class="form-field !mb-0">
                                <label for="receiving_doctor_code" class="form-field__label">Código médico</label>
                                <input
                                    id="receiving_doctor_code"
                                    v-model="form.receiving_doctor_code"
                                    type="text"
                                    placeholder="Ej. 10021"
                                    class="form-field__input"
                                    required
                                />
                                <p v-if="form.errors.receiving_doctor_code" class="form-field__error">
                                    {{ form.errors.receiving_doctor_code }}
                                </p>
                            </div>
                        </div>

                        <div v-if="requiresDoctor" class="form-field">
                            <label for="receiving_service" class="form-field__label">Servicio</label>
                            <input
                                id="receiving_service"
                                v-model="form.receiving_service"
                                type="text"
                                placeholder="Ej. Urgencias, Pediatría"
                                class="form-field__input"
                                required
                            />
                            <p v-if="form.errors.receiving_service" class="form-field__error">
                                {{ form.errors.receiving_service }}
                            </p>
                        </div>
                    </div>

                    <div class="my-5 h-px bg-[#eef2f1]"></div>

                    <div class="mb-3.5 text-sm font-semibold">Contacto</div>
                    <div class="mb-5 grid gap-4 sm:grid-cols-2">
                        <div class="form-field !mb-0">
                            <label for="contact_number" class="form-field__label">Número de contacto</label>
                            <input
                                id="contact_number"
                                v-model="form.contact_number"
                                type="text"
                                placeholder="Teléfono"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.contact_number" class="form-field__error">
                                {{ form.errors.contact_number }}
                            </p>
                        </div>

                        <div class="form-field !mb-0">
                            <label for="cedula" class="form-field__label">Cédula</label>
                            <input
                                id="cedula"
                                v-model="form.cedula"
                                type="text"
                                placeholder="Documento"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.cedula" class="form-field__error">
                                {{ form.errors.cedula }}
                            </p>
                        </div>
                    </div>

                    <div class="my-5 h-px bg-[#eef2f1]"></div>

                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-sm font-semibold">Artículos donados</div>
                        <button type="button" class="btn btn--secondary" @click="addItem">
                            <span class="text-[15px] leading-none">+</span> Agregar artículo
                        </button>
                    </div>
                    <p v-if="form.errors.items" class="form-field__error mb-3">
                        {{ form.errors.items }}
                    </p>

                    <div class="flex flex-col gap-2.5">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-2 items-end gap-2.5 rounded-xl border border-[#e6ede9] bg-[#f8faf9] p-[13px] sm:grid-cols-12"
                        >
                            <div class="form-field !mb-0 col-span-2 sm:col-span-5">
                                <label :for="`item-name-${index}`" class="form-field__label">Nombre</label>
                                <input
                                    :id="`item-name-${index}`"
                                    v-model="item.name"
                                    type="text"
                                    placeholder="Ej. Gasas estériles"
                                    class="form-field__input"
                                    required
                                />
                                <p v-if="form.errors[`items.${index}.name`]" class="form-field__error">
                                    {{ form.errors[`items.${index}.name`] }}
                                </p>
                            </div>

                            <div class="form-field !mb-0 col-span-1 sm:col-span-3">
                                <label :for="`item-quantity-${index}`" class="form-field__label">Cantidad</label>
                                <input
                                    :id="`item-quantity-${index}`"
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    placeholder="0"
                                    class="form-field__input"
                                    required
                                />
                                <p v-if="form.errors[`items.${index}.quantity`]" class="form-field__error">
                                    {{ form.errors[`items.${index}.quantity`] }}
                                </p>
                            </div>

                            <div class="form-field !mb-0 col-span-1 sm:col-span-3">
                                <label :for="`item-unit-${index}`" class="form-field__label">Unidad</label>
                                <select
                                    :id="`item-unit-${index}`"
                                    v-model="item.unit"
                                    class="form-field__select"
                                >
                                    <option value="">Sin unidad</option>
                                    <option
                                        v-for="unit in itemUnits"
                                        :key="unit"
                                        :value="unit"
                                    >
                                        {{ unit }}
                                    </option>
                                </select>
                                <p v-if="form.errors[`items.${index}.unit`]" class="form-field__error">
                                    {{ form.errors[`items.${index}.unit`] }}
                                </p>
                            </div>

                            <div class="col-span-2 flex items-end justify-end sm:col-span-1">
                                <button
                                    type="button"
                                    class="btn btn--danger"
                                    :disabled="form.items.length <= 1"
                                    @click="removeItem(index)"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            class="btn btn--primary btn--full"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Crear donación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
