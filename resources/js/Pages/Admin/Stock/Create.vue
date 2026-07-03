<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    donationTypes: {
        type: Array,
        default: () => [],
    },
});

const itemUnits = ['unidades', 'cajas', 'kg', 'litros', 'paquetes', 'frascos'];

const form = useForm({
    name: '',
    unit: '',
    donation_type_id: props.donationTypes[0]?.id ?? '',
    quantity_available: '',
    minimum_threshold: '',
});

const submit = () => {
    form.post(route('admin.stock-items.store'));
};
</script>

<template>
    <Head title="Nuevo insumo" />

    <AuthenticatedLayout>
        <div class="page page--narrow stock-form">
            <Link :href="route('admin.stock-items.index')" class="stock-form__back">
                <span class="stock-form__back-icon">←</span> Volver a inventario
            </Link>

            <h1 class="stock-form__title">Nuevo insumo</h1>
            <p class="stock-form__subtitle">Agrégalo al catálogo para poder llevar su stock</p>

            <div class="surface stock-form__panel">
                <form @submit.prevent="submit">
                    <div class="form-field">
                        <label for="name" class="form-field__label">Nombre</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Ej. Paracetamol 500mg"
                            class="form-field__input"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.name" class="form-field__error">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="donation_type_id" class="form-field__label">Tipo de donación</label>
                        <select id="donation_type_id" v-model="form.donation_type_id" class="form-field__select" required>
                            <option v-for="type in donationTypes" :key="type.id" :value="type.id">
                                {{ type.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.donation_type_id" class="form-field__error">
                            {{ form.errors.donation_type_id }}
                        </p>
                    </div>

                    <div class="stock-form__grid">
                        <div class="form-field">
                            <label for="unit" class="form-field__label">Unidad</label>
                            <select id="unit" v-model="form.unit" class="form-field__select" required>
                                <option value="" disabled>Selecciona una unidad</option>
                                <option v-for="unit in itemUnits" :key="unit" :value="unit">
                                    {{ unit }}
                                </option>
                            </select>
                            <p v-if="form.errors.unit" class="form-field__error">
                                {{ form.errors.unit }}
                            </p>
                        </div>

                        <div class="form-field">
                            <label for="quantity_available" class="form-field__label">
                                Cantidad inicial disponible
                            </label>
                            <input
                                id="quantity_available"
                                v-model="form.quantity_available"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="form-field__input"
                            />
                            <p v-if="form.errors.quantity_available" class="form-field__error">
                                {{ form.errors.quantity_available }}
                            </p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="minimum_threshold" class="form-field__label">
                            Umbral mínimo (opcional)
                        </label>
                        <input
                            id="minimum_threshold"
                            v-model="form.minimum_threshold"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="Sin definir"
                            class="form-field__input"
                        />
                        <p class="stock-form__hint">
                            Si lo defines, el insumo se resalta en el listado cuando el stock
                            disponible caiga por debajo de este número. Déjalo vacío para no
                            monitorearlo.
                        </p>
                        <p v-if="form.errors.minimum_threshold" class="form-field__error">
                            {{ form.errors.minimum_threshold }}
                        </p>
                    </div>

                    <div class="stock-form__submit">
                        <button
                            type="submit"
                            class="btn btn--primary btn--full"
                            :class="{ 'is-busy': form.processing }"
                            :disabled="form.processing"
                        >
                            Crear insumo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.stock-form__back {
    margin-bottom: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.stock-form__back:hover {
    text-decoration: underline;
}

.stock-form__back-icon {
    font-size: 1rem;
    line-height: 1;
}

.stock-form__title {
    margin-bottom: 0.25rem;
}

.stock-form__subtitle {
    margin-bottom: 22px;
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.stock-form__panel {
    padding: 1.75rem 1.5rem;
}

.stock-form__grid {
    display: grid;
    gap: 1rem;
}

.stock-form__hint {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: #8a969a;
}

.stock-form__submit {
    margin-top: 0.5rem;
}

@media (min-width: 768px) {
    .stock-form__grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
