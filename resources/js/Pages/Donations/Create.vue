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
        <div class="page page--narrow donation-form">
            <Link :href="route('donations.index')" class="donation-form__back">
                <span class="donation-form__back-icon">←</span> Volver a donaciones
            </Link>

            <h1 class="donation-form__title">Nueva donación</h1>
            <p class="donation-form__subtitle">Registra qué se dona y a dónde va</p>

            <div class="surface donation-form__panel">
                <form @submit.prevent="submit">
                    <div class="donation-form__label">¿Qué tipo de donación es?</div>
                    <div class="donation-form__chips">
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
                    <p v-if="form.errors.donation_type" class="form-field__error donation-form__chips-error">
                        {{ form.errors.donation_type }}
                    </p>

                    <div class="donation-form__grid">
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
                        class="donation-form__medical"
                        :class="{ 'donation-form__medical--required': requiresDoctor }"
                    >
                        <div class="donation-form__medical-header">
                            <span v-if="requiresDoctor" class="donation-form__medical-icon">!</span>
                            Médico / responsable que recibe
                            <span v-if="requiresDoctor">· obligatorio para insumos médicos</span>
                        </div>
                        <div class="donation-form__grid donation-form__grid--tight">
                            <div class="form-field donation-form__field">
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

                            <div v-if="requiresDoctor" class="form-field donation-form__field">
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

                    <div class="donation-form__divider"></div>

                    <div class="donation-form__label">Contacto</div>
                    <div class="donation-form__grid donation-form__grid--contact">
                        <div class="form-field donation-form__field">
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

                        <div class="form-field donation-form__field">
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

                    <div class="donation-form__divider"></div>

                    <div class="donation-form__items-header">
                        <div class="donation-form__label donation-form__label--flush">Artículos donados</div>
                        <button type="button" class="btn btn--secondary" @click="addItem">
                            <span class="donation-form__item-icon">+</span> Agregar artículo
                        </button>
                    </div>
                    <p v-if="form.errors.items" class="form-field__error donation-form__items-error">
                        {{ form.errors.items }}
                    </p>

                    <div class="donation-form__items">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="donation-form__item-row"
                        >
                            <div class="form-field donation-form__field donation-form__item-name">
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

                            <div class="form-field donation-form__field donation-form__item-quantity">
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

                            <div class="form-field donation-form__field donation-form__item-unit">
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

                            <div class="donation-form__item-remove">
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

                    <div class="donation-form__submit">
                        <button
                            type="submit"
                            class="btn btn--primary btn--full"
                            :class="{ 'is-busy': form.processing }"
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

<style scoped>
.donation-form__back {
    margin-bottom: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.donation-form__back:hover {
    text-decoration: underline;
}

.donation-form__back-icon {
    font-size: 1rem;
    line-height: 1;
}

.donation-form__title {
    margin-bottom: 0.25rem;
}

.donation-form__subtitle {
    margin-bottom: 22px;
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.donation-form__panel {
    padding: 1.75rem 1.5rem;
}

.donation-form__label {
    margin-bottom: 11px;
    font-size: 0.875rem;
    font-weight: 600;
}

.donation-form__label--flush {
    margin-bottom: 0;
}

.donation-form__chips {
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

.donation-form__chips-error {
    margin-top: -1rem;
    margin-bottom: 1rem;
}

.donation-form__grid {
    display: grid;
    gap: 1rem;
}

.donation-form__grid--tight {
    margin-bottom: 0.875rem;
    gap: 0.875rem;
}

.donation-form__grid--contact {
    margin-bottom: 1.25rem;
}

.donation-form__field {
    margin-bottom: 0;
}

.donation-form__medical {
    margin-top: 18px;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #f8faf9;
    padding: 18px 18px 6px;
}

.donation-form__medical--required {
    border-color: #f7d7d2;
    background-color: #fef4f2;
}

.donation-form__medical-header {
    margin-bottom: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 13px;
    font-weight: 600;
    color: #5a686d;
}

.donation-form__medical--required .donation-form__medical-header {
    color: #c0392b;
}

.donation-form__medical-icon {
    display: flex;
    height: 1.25rem;
    width: 1.25rem;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #c0392b;
    font-size: 0.75rem;
    color: #fff;
}

.donation-form__divider {
    margin-block: 1.25rem;
    height: 1px;
    background-color: #eef2f1;
}

.donation-form__items-header {
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.donation-form__items-error {
    margin-bottom: 0.75rem;
}

.donation-form__item-icon {
    font-size: 15px;
    line-height: 1;
}

.donation-form__items {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.donation-form__item-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    align-items: end;
    gap: 0.625rem;
    border-radius: 0.75rem;
    border: 1px solid #e6ede9;
    background-color: #f8faf9;
    padding: 13px;
}

.donation-form__item-name {
    grid-column: span 2 / span 2;
}

.donation-form__item-remove {
    grid-column: span 2 / span 2;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}

.donation-form__submit {
    margin-top: 1.5rem;
}

@media (min-width: 768px) {
    .donation-form__grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .donation-form__item-row {
        grid-template-columns: repeat(12, 1fr);
    }

    .donation-form__item-name {
        grid-column: span 5 / span 5;
    }

    .donation-form__item-quantity,
    .donation-form__item-unit {
        grid-column: span 3 / span 3;
    }

    .donation-form__item-remove {
        grid-column: span 1 / span 1;
    }
}
</style>
