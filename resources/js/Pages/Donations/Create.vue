<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nueva donación
            </h2>
        </template>

        <div class="container">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form @submit.prevent="submit">
                    <div class="form-field">
                        <label for="donation_type" class="form-field__label">Tipo de donación</label>
                        <select
                            id="donation_type"
                            v-model="form.donation_type"
                            class="form-field__select"
                            required
                        >
                            <option
                                v-for="type in donationTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.donation_type" class="form-field__error">
                            {{ form.errors.donation_type }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="location" class="form-field__label">Ubicación</label>
                        <input
                            id="location"
                            v-model="form.location"
                            type="text"
                            class="form-field__input"
                            required
                        />
                        <p v-if="form.errors.location" class="form-field__error">
                            {{ form.errors.location }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="patient_name" class="form-field__label">Nombre del paciente</label>
                        <input
                            id="patient_name"
                            v-model="form.patient_name"
                            type="text"
                            class="form-field__input"
                        />
                        <p v-if="form.errors.patient_name" class="form-field__error">
                            {{ form.errors.patient_name }}
                        </p>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-sm font-semibold text-gray-700">
                            Médico / responsable que recibe
                            <span v-if="requiresDoctor" class="text-red-600">
                                (obligatorio para insumos médicos)
                            </span>
                        </h3>

                        <div class="form-field mt-4">
                            <label for="receiving_doctor_name" class="form-field__label">Nombre</label>
                            <input
                                id="receiving_doctor_name"
                                v-model="form.receiving_doctor_name"
                                type="text"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.receiving_doctor_name" class="form-field__error">
                                {{ form.errors.receiving_doctor_name }}
                            </p>
                        </div>

                        <div v-if="requiresDoctor" class="form-field">
                            <label for="receiving_doctor_code" class="form-field__label">Código médico</label>
                            <input
                                id="receiving_doctor_code"
                                v-model="form.receiving_doctor_code"
                                type="text"
                                class="form-field__input"
                                required
                            />
                            <p v-if="form.errors.receiving_doctor_code" class="form-field__error">
                                {{ form.errors.receiving_doctor_code }}
                            </p>
                        </div>

                        <div v-if="requiresDoctor" class="form-field">
                            <label for="receiving_service" class="form-field__label">Servicio</label>
                            <input
                                id="receiving_service"
                                v-model="form.receiving_service"
                                type="text"
                                class="form-field__input"
                                required
                            />
                            <p v-if="form.errors.receiving_service" class="form-field__error">
                                {{ form.errors.receiving_service }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-sm font-semibold text-gray-700">
                            Contacto
                        </h3>

                        <div class="form-field mt-4">
                            <label for="contact_number" class="form-field__label">Número de contacto</label>
                            <input
                                id="contact_number"
                                v-model="form.contact_number"
                                type="text"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.contact_number" class="form-field__error">
                                {{ form.errors.contact_number }}
                            </p>
                        </div>

                        <div class="form-field">
                            <label for="cedula" class="form-field__label">Cédula</label>
                            <input
                                id="cedula"
                                v-model="form.cedula"
                                type="text"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.cedula" class="form-field__error">
                                {{ form.errors.cedula }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Artículos donados
                            </h3>
                            <button type="button" class="btn btn--secondary" @click="addItem">
                                Agregar artículo
                            </button>
                        </div>
                        <p v-if="form.errors.items" class="form-field__error">
                            {{ form.errors.items }}
                        </p>

                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="mt-4 grid grid-cols-12 gap-2 rounded-md border border-gray-200 p-3"
                        >
                            <div class="form-field col-span-12 sm:col-span-5">
                                <label :for="`item-name-${index}`" class="form-field__label">Nombre</label>
                                <input
                                    :id="`item-name-${index}`"
                                    v-model="item.name"
                                    type="text"
                                    class="form-field__input"
                                    required
                                />
                                <p v-if="form.errors[`items.${index}.name`]" class="form-field__error">
                                    {{ form.errors[`items.${index}.name`] }}
                                </p>
                            </div>

                            <div class="form-field col-span-6 sm:col-span-3">
                                <label :for="`item-quantity-${index}`" class="form-field__label">Cantidad</label>
                                <input
                                    :id="`item-quantity-${index}`"
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    class="form-field__input"
                                    required
                                />
                                <p v-if="form.errors[`items.${index}.quantity`]" class="form-field__error">
                                    {{ form.errors[`items.${index}.quantity`] }}
                                </p>
                            </div>

                            <div class="form-field col-span-6 sm:col-span-3">
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

                            <div class="col-span-12 flex items-end justify-end sm:col-span-1">
                                <button
                                    type="button"
                                    class="btn btn--danger"
                                    :disabled="form.items.length <= 1"
                                    @click="removeItem(index)"
                                >
                                    X
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
