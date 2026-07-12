<script setup>
import ChildrenLayout from '@/Layouts/ChildrenLayout.vue';
import { childNeedStatusLabel } from '@/utils/labels';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    child: {
        type: Object,
        required: true,
    },
    needs: {
        type: Array,
        required: true,
    },
});

const formatDate = (value) => {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleDateString('es-CL');
};

const needForm = useForm({
    description: '',
    is_recurring: false,
    recurrence_interval_days: '',
});

const submitNeed = () => {
    needForm.post(route('children.needs.store', props.child.id), {
        preserveScroll: true,
        onSuccess: () => needForm.reset(),
    });
};

const markingCoveredId = ref(null);

const markCovered = (need) => {
    markingCoveredId.value = need.id;

    router.patch(
        route('child-needs.mark-covered', need.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                markingCoveredId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head :title="child.name" />

    <ChildrenLayout>
        <div class="page children-show">
            <div class="children-show__header">
                <div>
                    <h1 class="children-show__title">{{ child.name }}</h1>
                    <p class="children-show__subtitle">
                        Responsable: {{ child.guardian_name ?? 'Sin nombre registrado' }} ·
                        {{ child.guardian_phone ?? 'Sin teléfono' }}
                    </p>
                </div>
                <Link :href="route('children.edit', child.id)" class="btn btn--secondary">
                    Editar
                </Link>
            </div>

            <div class="panel children-show__info">
                <div class="children-show__info-item">
                    <span class="children-show__info-label">Dirección</span>
                    <span>{{ child.address }}</span>
                </div>
                <div class="children-show__info-item">
                    <span class="children-show__info-label">Fecha de nacimiento</span>
                    <span>{{ formatDate(child.birthdate) ?? 'No registrada' }}</span>
                </div>
                <div class="children-show__info-item">
                    <span class="children-show__info-label">Condiciones</span>
                    <span v-if="child.conditions.length === 0">—</span>
                    <span v-else class="children-show__conditions">
                        <span
                            v-for="condition in child.conditions"
                            :key="condition.id"
                            class="children-show__condition-chip"
                        >
                            {{ condition.name }}
                        </span>
                    </span>
                </div>
            </div>

            <h2 class="children-show__section-title">Necesidades</h2>

            <div class="children-show__needs">
                <div v-for="need in needs" :key="need.id" class="panel children-show__need-card">
                    <div class="children-show__need">
                        <div class="children-show__need-main">
                            <p class="children-show__need-description">{{ need.description }}</p>
                            <p v-if="need.is_recurring" class="children-show__need-recurring">
                                Recurrente cada {{ need.recurrence_interval_days }} días
                            </p>
                            <p v-if="need.last_covered_at" class="children-show__need-last-covered">
                                Última vez cubierta: {{ formatDate(need.last_covered_at) }}
                            </p>
                        </div>

                        <div class="children-show__need-side">
                            <span
                                class="children-show__status"
                                :class="`children-show__status--${need.status}`"
                            >
                                {{ childNeedStatusLabel(need.status) }}
                            </span>

                            <template v-if="need.status === 'pendiente'">
                                <Link
                                    :href="route('child-needs.initiate-donation', need.id)"
                                    class="btn btn--primary children-show__cover-btn"
                                >
                                    Iniciar donación para esta necesidad
                                </Link>
                                <button
                                    type="button"
                                    class="btn btn--secondary children-show__cover-btn"
                                    :disabled="markingCoveredId === need.id"
                                    @click="markCovered(need)"
                                >
                                    Marcar como cubierta
                                </button>
                            </template>
                        </div>
                    </div>

                    <div v-if="need.fulfillments.length > 0" class="children-show__history">
                        <p class="children-show__history-title">Historial</p>
                        <ul class="children-show__history-list">
                            <li
                                v-for="fulfillment in need.fulfillments"
                                :key="fulfillment.id"
                                class="children-show__history-item"
                            >
                                {{ formatDate(fulfillment.fulfilled_at) }} · cubierta por
                                {{ fulfillment.fulfilled_by?.name ?? '—' }}
                                <template v-if="fulfillment.donation">
                                    ·
                                    <Link
                                        :href="route('donations.show', fulfillment.donation.id)"
                                        class="children-show__history-link"
                                    >
                                        Ver donación
                                    </Link>
                                </template>
                            </li>
                        </ul>
                    </div>
                </div>

                <p v-if="needs.length === 0" class="children-show__empty">
                    Este niño todavía no tiene necesidades registradas.
                </p>
            </div>

            <h2 class="children-show__section-title">Agregar necesidad</h2>

            <form @submit.prevent="submitNeed" class="panel children-show__need-form">
                <div class="form-field">
                    <label for="description" class="form-field__label">Descripción</label>
                    <input
                        id="description"
                        v-model="needForm.description"
                        type="text"
                        placeholder="Ej. Leche formula, control dental, ayuda psicológica"
                        class="form-field__input"
                        required
                    />
                    <p v-if="needForm.errors.description" class="form-field__error">
                        {{ needForm.errors.description }}
                    </p>
                </div>

                <div class="form-field children-show__recurring-field">
                    <label class="children-show__checkbox-label">
                        <input type="checkbox" v-model="needForm.is_recurring" />
                        Es una necesidad recurrente
                    </label>
                </div>

                <div v-if="needForm.is_recurring" class="form-field">
                    <label for="recurrence_interval_days" class="form-field__label">
                        Cada cuántos días (ej. 30 para mensual)
                    </label>
                    <input
                        id="recurrence_interval_days"
                        v-model="needForm.recurrence_interval_days"
                        type="number"
                        step="1"
                        min="1"
                        class="form-field__input"
                    />
                    <p v-if="needForm.errors.recurrence_interval_days" class="form-field__error">
                        {{ needForm.errors.recurrence_interval_days }}
                    </p>
                </div>

                <div class="children-show__need-form-actions">
                    <button type="submit" class="btn btn--primary" :disabled="needForm.processing">
                        Agregar necesidad
                    </button>
                </div>
            </form>
        </div>
    </ChildrenLayout>
</template>

<style scoped>
.children-show__header {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.children-show__title {
    margin-bottom: 2px;
}

.children-show__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.children-show__info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.children-show__info-item {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    font-size: 0.9375rem;
}

.children-show__info-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
}

.children-show__conditions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}

.children-show__condition-chip {
    display: inline-block;
    border-radius: 999px;
    background-color: #efeef8;
    padding: 3px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6d67b0;
}

.children-show__section-title {
    margin-bottom: 0.75rem;
    font-size: 1.0625rem;
}

.children-show__needs {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.75rem;
}

.children-show__need-card {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.children-show__need {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.children-show__need-description {
    font-weight: 600;
}

.children-show__need-recurring {
    margin-top: 0.25rem;
    font-size: 0.8125rem;
    color: #6d67b0;
}

.children-show__need-last-covered {
    margin-top: 0.25rem;
    font-size: 0.8125rem;
    color: #8a969a;
}

.children-show__need-side {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

/* $color-warning / $color-success (resources/sass/abstracts/_variables.scss)
   repetidos como hex — los estilos scoped de un .vue no pueden @use
   parciales de Sass. */
.children-show__status {
    display: inline-block;
    white-space: nowrap;
    border-radius: 999px;
    padding: 4px 11px;
    font-size: 0.75rem;
    font-weight: 700;
}

.children-show__status--pendiente {
    background-color: rgba(217, 119, 6, 0.14);
    color: #d97706;
}

.children-show__status--cubierta {
    background-color: #e7f3ec;
    color: #148f5b;
}

.children-show__cover-btn {
    min-height: 36px;
    padding: 6px 14px;
    font-size: 0.8125rem;
    white-space: nowrap;
}

.children-show__history {
    border-top: 1px solid #eef2f1;
    padding-top: 0.75rem;
}

.children-show__history-title {
    margin-bottom: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
}

.children-show__history-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.children-show__history-item {
    font-size: 0.8125rem;
    color: #5a686d;
}

.children-show__history-link {
    font-weight: 600;
    color: #148f5b;
}

.children-show__history-link:hover {
    text-decoration: underline;
}

.children-show__empty {
    padding: 24px;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: #8a969a;
}

.children-show__need-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.children-show__checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.children-show__checkbox-label input[type='checkbox'] {
    accent-color: #148f5b;
}

.children-show__need-form-actions {
    display: flex;
    justify-content: flex-end;
}
</style>
