<script setup>
import ChildrenLayout from '@/Layouts/ChildrenLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    child: {
        type: Object,
        required: true,
    },
    conditions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.child.name,
    birthdate: props.child.birthdate ?? '',
    guardian_name: props.child.guardian_name,
    guardian_phone: props.child.guardian_phone,
    address: props.child.address,
    condition_ids: props.child.conditions.map((condition) => condition.id),
    new_condition_names: '',
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            new_condition_names: data.new_condition_names
                .split(',')
                .map((name) => name.trim())
                .filter(Boolean),
        }))
        .put(route('children.update', props.child.id));
};
</script>

<template>
    <Head title="Editar niño" />

    <ChildrenLayout>
        <div class="page page--narrow children-form">
            <h1 class="children-form__title">Editar niño</h1>

            <form @submit.prevent="submit" class="panel children-form__panel">
                <div class="form-field">
                    <label for="name" class="form-field__label">Nombre</label>
                    <input id="name" v-model="form.name" type="text" class="form-field__input" required autofocus />
                    <p v-if="form.errors.name" class="form-field__error">{{ form.errors.name }}</p>
                </div>

                <div class="form-field">
                    <label for="birthdate" class="form-field__label">Fecha de nacimiento (opcional)</label>
                    <input id="birthdate" v-model="form.birthdate" type="date" class="form-field__input" />
                    <p v-if="form.errors.birthdate" class="form-field__error">{{ form.errors.birthdate }}</p>
                </div>

                <div class="form-field">
                    <label for="guardian_name" class="form-field__label">Nombre del responsable</label>
                    <input id="guardian_name" v-model="form.guardian_name" type="text" class="form-field__input" required />
                    <p v-if="form.errors.guardian_name" class="form-field__error">{{ form.errors.guardian_name }}</p>
                </div>

                <div class="form-field">
                    <label for="guardian_phone" class="form-field__label">Teléfono del responsable</label>
                    <input id="guardian_phone" v-model="form.guardian_phone" type="text" class="form-field__input" required />
                    <p v-if="form.errors.guardian_phone" class="form-field__error">{{ form.errors.guardian_phone }}</p>
                </div>

                <div class="form-field">
                    <label for="address" class="form-field__label">Dirección</label>
                    <input id="address" v-model="form.address" type="text" class="form-field__input" required />
                    <p v-if="form.errors.address" class="form-field__error">{{ form.errors.address }}</p>
                </div>

                <div class="form-field">
                    <span class="form-field__label">Condiciones</span>
                    <div class="children-form__conditions">
                        <label
                            v-for="condition in conditions"
                            :key="condition.id"
                            class="children-form__condition-option"
                        >
                            <input
                                type="checkbox"
                                :value="condition.id"
                                v-model="form.condition_ids"
                            />
                            {{ condition.name }}
                        </label>
                        <p v-if="conditions.length === 0" class="children-form__no-conditions">
                            Todavía no hay condiciones en el catálogo.
                        </p>
                    </div>
                    <p v-if="form.errors.condition_ids" class="form-field__error">{{ form.errors.condition_ids }}</p>
                </div>

                <div class="form-field">
                    <label for="new_condition_names" class="form-field__label">
                        Agregar condiciones nuevas (opcional)
                    </label>
                    <input
                        id="new_condition_names"
                        v-model="form.new_condition_names"
                        type="text"
                        placeholder="Ej. Asma, Déficit de atención"
                        class="form-field__input"
                    />
                    <p class="children-form__hint">
                        Separadas por coma. Se agregan al catálogo y quedan disponibles para otros niños.
                    </p>
                    <p v-if="form.errors.new_condition_names" class="form-field__error">
                        {{ form.errors.new_condition_names }}
                    </p>
                </div>

                <div class="children-form__actions">
                    <button type="submit" class="btn btn--primary" :disabled="form.processing">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </ChildrenLayout>
</template>

<style scoped>
.children-form__title {
    margin-bottom: 1rem;
}

.children-form__panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.children-form__conditions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.375rem;
}

.children-form__condition-option {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    border-radius: 999px;
    border: 1px solid #e6ede9;
    padding: 5px 12px;
    font-size: 0.8125rem;
    font-weight: 500;
}

.children-form__condition-option input[type='checkbox'] {
    accent-color: #148f5b;
}

.children-form__no-conditions {
    font-size: 0.8125rem;
    color: #8a969a;
}

.children-form__hint {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #8a969a;
}

.children-form__actions {
    margin-top: 0.5rem;
    display: flex;
    justify-content: flex-end;
}
</style>
