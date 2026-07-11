<script setup>
import ChildrenLayout from '@/Layouts/ChildrenLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    children: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Niños" />

    <ChildrenLayout>
        <div class="page children-index">
            <div class="children-index__header">
                <div>
                    <h1 class="children-index__title">Niños</h1>
                    <p class="children-index__subtitle">Registro de niños y sus necesidades</p>
                </div>
                <Link :href="route('children.create')" class="btn btn--primary">
                    <span class="children-index__cta-icon">+</span> Nuevo niño
                </Link>
            </div>

            <div class="children-index__table-wrap">
                <table class="children-index__table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="children-index__col--md">Responsable</th>
                            <th class="children-index__col--md">Condiciones</th>
                            <th>Necesidades</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="child in children"
                            :key="child.id"
                            :class="{ 'children-index__row--inactive': !child.active }"
                        >
                            <td class="children-index__name">
                                {{ child.name }}
                                <span v-if="!child.active" class="children-index__inactive-tag">Inactivo</span>
                            </td>
                            <td class="children-index__col--md">
                                {{ child.guardian_name }}
                                <span class="children-index__guardian-phone">{{ child.guardian_phone }}</span>
                            </td>
                            <td class="children-index__col--md">
                                <span
                                    v-for="condition in child.conditions"
                                    :key="condition.id"
                                    class="children-index__condition-chip"
                                >
                                    {{ condition.name }}
                                </span>
                                <span v-if="child.conditions.length === 0" class="children-index__no-conditions">—</span>
                            </td>
                            <td>
                                <span
                                    v-if="child.pending_needs_count > 0"
                                    class="children-index__needs-badge children-index__needs-badge--pending"
                                >
                                    {{ child.pending_needs_count }} pendiente{{ child.pending_needs_count === 1 ? '' : 's' }}
                                </span>
                                <span v-else class="children-index__needs-badge children-index__needs-badge--none">
                                    Sin pendientes
                                </span>
                            </td>
                            <td class="children-index__actions">
                                <Link :href="route('children.show', child.id)" class="children-index__view">
                                    Ver detalle
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="children.length === 0" class="children-index__empty">
                    Todavía no hay niños registrados.
                </p>
            </div>
        </div>
    </ChildrenLayout>
</template>

<style scoped>
.children-index__header {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.children-index__title {
    margin-bottom: 2px;
}

.children-index__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.children-index__cta-icon {
    font-size: 17px;
    line-height: 1;
}

.children-index__table-wrap {
    overflow-x: auto;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #fff;
}

.children-index__table {
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    border-collapse: collapse;
}

.children-index__table thead {
    border-bottom: 1px solid #eef2f1;
    background-color: #f6f9f8;
}

.children-index__table thead tr {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6a787d;
}

.children-index__table th,
.children-index__table td {
    padding: 14px 12px;
}

.children-index__table th:first-child,
.children-index__table td:first-child {
    padding-inline: 1.25rem;
}

.children-index__table th:last-child,
.children-index__table td:last-child {
    padding-inline: 1.25rem;
}

.children-index__table tbody tr {
    border-bottom: 1px solid #f1f4f3;
}

.children-index__table tbody tr:last-child {
    border-bottom: none;
}

.children-index__row--inactive {
    opacity: 0.55;
}

.children-index__col--md {
    display: none;
}

.children-index__name {
    font-weight: 600;
}

.children-index__inactive-tag {
    margin-left: 8px;
    display: inline-block;
    border-radius: 999px;
    background-color: #f1f4f3;
    padding: 2px 8px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
}

.children-index__guardian-phone {
    display: block;
    font-size: 0.8125rem;
    color: #8a969a;
}

.children-index__condition-chip {
    display: inline-block;
    margin: 2px 4px 2px 0;
    border-radius: 999px;
    background-color: #efeef8;
    padding: 3px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6d67b0;
}

.children-index__no-conditions {
    color: #8a969a;
}

.children-index__needs-badge {
    display: inline-block;
    white-space: nowrap;
    border-radius: 999px;
    padding: 4px 11px;
    font-size: 0.75rem;
    font-weight: 700;
}

.children-index__needs-badge--pending {
    background-color: rgba(217, 119, 6, 0.14);
    color: #d97706;
}

.children-index__needs-badge--none {
    background-color: #f1f4f3;
    color: #8a969a;
}

.children-index__actions {
    text-align: right;
}

.children-index__view {
    font-weight: 600;
    color: #6d67b0;
}

.children-index__view:hover {
    text-decoration: underline;
}

.children-index__empty {
    padding: 44px;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: #8a969a;
}

@media (min-width: 768px) {
    .children-index__col--md {
        display: table-cell;
    }
}
</style>
