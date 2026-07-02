<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const donationTypes = [
    { value: 'insumos_medicos', label: 'Insumos médicos' },
    { value: 'higiene', label: 'Higiene' },
    { value: 'alimentos', label: 'Alimentos' },
    { value: 'otros', label: 'Otros' },
];

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

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="donation_type" value="Tipo de donación" />
                            <select
                                id="donation_type"
                                v-model="form.donation_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                            <InputError class="mt-2" :message="form.errors.donation_type" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="location" value="Ubicación" />
                            <TextInput
                                id="location"
                                v-model="form.location"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="patient_name" value="Nombre del paciente" />
                            <TextInput
                                id="patient_name"
                                v-model="form.patient_name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.patient_name" />
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Médico / responsable que recibe
                                <span v-if="requiresDoctor" class="text-red-600">
                                    (obligatorio para insumos médicos)
                                </span>
                            </h3>

                            <div class="mt-4">
                                <InputLabel
                                    for="receiving_doctor_name"
                                    value="Nombre"
                                />
                                <TextInput
                                    id="receiving_doctor_name"
                                    v-model="form.receiving_doctor_name"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.receiving_doctor_name"
                                />
                            </div>

                            <div v-if="requiresDoctor" class="mt-4">
                                <InputLabel
                                    for="receiving_doctor_code"
                                    value="Código médico"
                                />
                                <TextInput
                                    id="receiving_doctor_code"
                                    v-model="form.receiving_doctor_code"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.receiving_doctor_code"
                                />
                            </div>

                            <div v-if="requiresDoctor" class="mt-4">
                                <InputLabel
                                    for="receiving_service"
                                    value="Servicio"
                                />
                                <TextInput
                                    id="receiving_service"
                                    v-model="form.receiving_service"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.receiving_service"
                                />
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Contacto
                            </h3>

                            <div class="mt-4">
                                <InputLabel
                                    for="contact_number"
                                    value="Número de contacto"
                                />
                                <TextInput
                                    id="contact_number"
                                    v-model="form.contact_number"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.contact_number"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="cedula" value="Cédula" />
                                <TextInput
                                    id="cedula"
                                    v-model="form.cedula"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.cedula" />
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-700">
                                    Artículos donados
                                </h3>
                                <SecondaryButton type="button" @click="addItem">
                                    Agregar artículo
                                </SecondaryButton>
                            </div>
                            <InputError class="mt-2" :message="form.errors.items" />

                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="mt-4 grid grid-cols-12 gap-2 rounded-md border border-gray-200 p-3"
                            >
                                <div class="col-span-5">
                                    <InputLabel :for="`item-name-${index}`" value="Nombre" />
                                    <TextInput
                                        :id="`item-name-${index}`"
                                        v-model="item.name"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors[`items.${index}.name`]"
                                    />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel :for="`item-quantity-${index}`" value="Cantidad" />
                                    <TextInput
                                        :id="`item-quantity-${index}`"
                                        v-model="item.quantity"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors[`items.${index}.quantity`]"
                                    />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel :for="`item-unit-${index}`" value="Unidad" />
                                    <TextInput
                                        :id="`item-unit-${index}`"
                                        v-model="item.unit"
                                        class="mt-1 block w-full"
                                    />
                                </div>

                                <div class="col-span-1 flex items-end justify-end">
                                    <DangerButton
                                        type="button"
                                        :disabled="form.items.length <= 1"
                                        @click="removeItem(index)"
                                    >
                                        X
                                    </DangerButton>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Crear donación
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
