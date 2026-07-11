<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { donationTypeLabel } from '@/utils/labels';
import { groupStockNeedsByType } from '@/utils/needs';
import { buildNeedsMessage, buildWhatsAppShareUrl } from '@/utils/whatsapp';

const props = defineProps({
    stockNeeds: {
        type: Array,
        default: () => [],
    },
    additionalNeeds: {
        type: Array,
        default: () => [],
    },
    publicNeedsUrl: {
        type: String,
        required: true,
    },
    // Solo description + total_children_needing (ver
    // ChildNeed::pendingCountsGroupedByDescription) — a propósito, sin
    // ningún campo que identifique a un niño puntual.
    childProgramNeeds: {
        type: Array,
        default: () => [],
    },
});

const groupedStockNeeds = computed(() => groupStockNeedsByType(props.stockNeeds));

const activeAdditionalNeeds = computed(() =>
    props.additionalNeeds.filter((need) => need.active),
);

const inactiveAdditionalNeeds = computed(() =>
    props.additionalNeeds.filter((need) => ! need.active),
);

const shareUrl = computed(() =>
    buildWhatsAppShareUrl(
        buildNeedsMessage(groupedStockNeeds.value, activeAdditionalNeeds.value, props.publicNeedsUrl),
    ),
);

const form = useForm({
    description: '',
    quantity_needed: '',
});

const submit = () => {
    form.post(route('admin.needs.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const toggleActive = (need) => {
    router.patch(route('admin.needs.toggle-active', need.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Necesidades" />

    <AuthenticatedLayout>
        <div class="page needs-admin">
            <div class="needs-admin__header">
                <div>
                    <h1 class="needs-admin__title">Necesidades</h1>
                    <p class="needs-admin__subtitle">
                        Lo que hoy está bajo mínimo más las necesidades adicionales
                    </p>
                </div>
                <a :href="shareUrl" target="_blank" rel="noopener noreferrer" class="btn btn--success">
                    Compartir necesidades por WhatsApp
                </a>
            </div>

            <p class="needs-admin__public-link">
                Página pública:
                <a :href="publicNeedsUrl" target="_blank" rel="noopener noreferrer">{{ publicNeedsUrl }}</a>
            </p>

            <div class="surface needs-admin__section">
                <h2 class="surface__title">Insumos por debajo del mínimo</h2>

                <p v-if="stockNeeds.length === 0" class="needs-admin__empty">
                    Ningún insumo con umbral definido está bajo mínimo ahora mismo.
                </p>

                <div v-for="group in groupedStockNeeds" :key="group.type" class="needs-admin__group">
                    <h3 class="needs-admin__group-title">{{ donationTypeLabel(group.type) }}</h3>
                    <ul class="needs-admin__list">
                        <li v-for="item in group.items" :key="item.id" class="needs-admin__item">
                            <span class="needs-admin__item-name">{{ item.name }}</span>
                            <span class="needs-admin__item-qty">
                                Se necesitan {{ item.quantity_needed }} {{ item.unit }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="childProgramNeeds.length > 0" class="surface needs-admin__section">
                <h2 class="surface__title">Necesidades del programa de atención infantil</h2>
                <p class="needs-admin__hint">
                    Agregado y anónimo a propósito: no identifica a qué niño corresponde cada
                    necesidad. Para ver el detalle por niño, entrá al módulo de niños.
                </p>

                <ul class="needs-admin__list">
                    <li
                        v-for="need in childProgramNeeds"
                        :key="need.description"
                        class="needs-admin__item"
                    >
                        <span class="needs-admin__item-name">{{ need.description }}</span>
                        <span class="needs-admin__item-qty">
                            Necesaria para {{ need.total_children_needing }}
                            {{ need.total_children_needing === 1 ? 'niño' : 'niños' }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="surface needs-admin__section">
                <h2 class="surface__title">Nueva necesidad adicional</h2>
                <p class="needs-admin__hint">
                    Para cosas fuera del catálogo de inventario, ej. "gasolina para el vehículo
                    de delivery".
                </p>

                <form @submit.prevent="submit" class="needs-admin__form">
                    <div class="form-field">
                        <label for="description" class="form-field__label">Descripción</label>
                        <input
                            id="description"
                            v-model="form.description"
                            type="text"
                            placeholder="Ej. Gasolina para el vehículo de delivery"
                            class="form-field__input"
                            required
                        />
                        <p v-if="form.errors.description" class="form-field__error">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="quantity_needed" class="form-field__label">
                            Cantidad (opcional, texto libre)
                        </label>
                        <input
                            id="quantity_needed"
                            v-model="form.quantity_needed"
                            type="text"
                            placeholder="Ej. lo que se pueda, 2 tanques"
                            class="form-field__input"
                        />
                        <p v-if="form.errors.quantity_needed" class="form-field__error">
                            {{ form.errors.quantity_needed }}
                        </p>
                    </div>

                    <div class="needs-admin__form-submit">
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :class="{ 'is-busy': form.processing }"
                            :disabled="form.processing"
                        >
                            Agregar necesidad
                        </button>
                    </div>
                </form>
            </div>

            <div class="surface needs-admin__section">
                <h2 class="surface__title">Necesidades adicionales activas</h2>

                <p v-if="activeAdditionalNeeds.length === 0" class="needs-admin__empty">
                    No hay necesidades adicionales activas.
                </p>

                <ul v-else class="needs-admin__list">
                    <li v-for="need in activeAdditionalNeeds" :key="need.id" class="needs-admin__item">
                        <span class="needs-admin__item-name">
                            {{ need.description }}
                            <span v-if="need.quantity_needed" class="needs-admin__item-qty-inline">
                                — {{ need.quantity_needed }}
                            </span>
                        </span>
                        <button
                            type="button"
                            class="btn btn--secondary needs-admin__toggle-btn"
                            @click="toggleActive(need)"
                        >
                            Marcar como cubierta
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="inactiveAdditionalNeeds.length > 0" class="surface needs-admin__section">
                <h2 class="surface__title">Necesidades ya cubiertas</h2>

                <ul class="needs-admin__list">
                    <li
                        v-for="need in inactiveAdditionalNeeds"
                        :key="need.id"
                        class="needs-admin__item needs-admin__item--inactive"
                    >
                        <span class="needs-admin__item-name">
                            {{ need.description }}
                            <span v-if="need.quantity_needed" class="needs-admin__item-qty-inline">
                                — {{ need.quantity_needed }}
                            </span>
                        </span>
                        <button
                            type="button"
                            class="btn btn--secondary needs-admin__toggle-btn"
                            @click="toggleActive(need)"
                        >
                            Reactivar
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.needs-admin__header {
    margin-bottom: 0.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.needs-admin__title {
    margin-bottom: 2px;
}

.needs-admin__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.needs-admin__public-link {
    margin-bottom: 1.5rem;
    font-size: 0.8125rem;
    color: #7a8b84;
    word-break: break-all;
}

.needs-admin__public-link a {
    font-weight: 600;
    color: #148f5b;
}

.needs-admin__section {
    margin-bottom: 1rem;
}

.needs-admin__hint {
    margin-top: -0.5rem;
    margin-bottom: 1rem;
    font-size: 0.8125rem;
    color: #8a969a;
}

.needs-admin__empty {
    font-size: 0.875rem;
    color: #8a969a;
}

.needs-admin__group {
    margin-top: 1rem;
}

.needs-admin__group:first-of-type {
    margin-top: 0.75rem;
}

.needs-admin__group-title {
    margin-bottom: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6a787d;
}

.needs-admin__list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.needs-admin__item {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem 1rem;
    border-bottom: 1px solid #f1f4f3;
    padding-bottom: 0.625rem;
}

.needs-admin__item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.needs-admin__item--inactive {
    opacity: 0.6;
}

.needs-admin__item-name {
    font-weight: 600;
    color: #14322a;
}

.needs-admin__item-qty-inline {
    font-weight: 400;
    color: #7a8b84;
}

.needs-admin__item-qty {
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
    white-space: nowrap;
}

.needs-admin__toggle-btn {
    min-height: 32px;
    padding: 5px 12px;
    font-size: 0.75rem;
}

.needs-admin__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.needs-admin__form-submit {
    display: flex;
    justify-content: flex-end;
}
</style>
