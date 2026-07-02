<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stockItem: {
        type: Object,
        required: true,
    },
    adjustments: {
        type: Array,
        required: true,
    },
});

const formatDate = (value) =>
    new Date(value).toLocaleString('es', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
</script>

<template>
    <Head :title="`Historial — ${stockItem.name}`" />

    <AuthenticatedLayout>
        <div class="page page--narrow stock-history">
            <Link :href="route('admin.stock-items.index')" class="stock-history__back">
                <span class="stock-history__back-icon">←</span> Volver a inventario
            </Link>

            <h1 class="stock-history__title">{{ stockItem.name }}</h1>
            <p class="stock-history__subtitle">
                Disponible actual: {{ stockItem.quantity_available }} {{ stockItem.unit }}
            </p>

            <div class="surface">
                <h2 class="surface__title">Historial de ajustes</h2>

                <ol v-if="adjustments.length > 0" class="status-timeline">
                    <li v-for="adjustment in adjustments" :key="adjustment.id" class="status-timeline__item">
                        <div class="status-timeline__date">{{ formatDate(adjustment.changed_at) }}</div>
                        <div
                            class="status-timeline__transition"
                            :class="{
                                'stock-history__change--positive': adjustment.quantity_change > 0,
                                'stock-history__change--negative': adjustment.quantity_change < 0,
                            }"
                        >
                            {{ adjustment.quantity_change > 0 ? '+' : '' }}{{ adjustment.quantity_change }}
                            {{ stockItem.unit }} — {{ adjustment.reason }}
                        </div>
                        <div class="status-timeline__actor">
                            por {{ adjustment.changed_by?.name ?? '—' }}
                        </div>
                    </li>
                </ol>

                <p v-else class="stock-history__empty">
                    Este insumo todavía no tiene ajustes registrados.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.stock-history__back {
    margin-bottom: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.stock-history__back:hover {
    text-decoration: underline;
}

.stock-history__back-icon {
    font-size: 1rem;
    line-height: 1;
}

.stock-history__title {
    margin-bottom: 0.25rem;
}

.stock-history__subtitle {
    margin-bottom: 22px;
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.stock-history__change--positive {
    color: #148f5b;
}

.stock-history__change--negative {
    color: #d9534f;
}

.stock-history__empty {
    font-size: 0.875rem;
    color: #8a969a;
}
</style>
