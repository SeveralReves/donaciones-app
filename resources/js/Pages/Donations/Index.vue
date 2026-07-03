<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { donationStatusLabel, donationTypeLabel } from '@/utils/labels';
import { canCreateDonations } from '@/utils/permissions';

const paginationLabel = (label) =>
    label
        .replace('&laquo; Previous', 'Anterior')
        .replace('Next &raquo;', 'Siguiente');

const props = defineProps({
    donations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    statusCounts: {
        type: Object,
        default: () => ({}),
    },
});

const statuses = [
    'creada',
    'en_proceso',
    'esperando_delivery',
    'en_camino',
    'recibido',
    'cancelada',
];

const donationTypes = ['insumos_medicos', 'higiene', 'alimentos', 'otros'];

const filters = reactive({
    status: props.filters.status ?? '',
    donation_type: props.filters.donation_type ?? '',
    location: props.filters.location ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

let locationTimeout = null;

const applyFilters = () => {
    router.get(route('donations.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const hasActiveFilters = computed(() =>
    Object.values(filters).some((value) => value !== ''),
);

const clearFilters = () => {
    clearTimeout(locationTimeout);
    filters.status = '';
    filters.donation_type = '';
    filters.location = '';
    filters.from = '';
    filters.to = '';
    applyFilters();
};

const setStatus = (status) => {
    filters.status = status;
};

watch(
    () => [filters.status, filters.donation_type, filters.from, filters.to],
    () => applyFilters(),
);

watch(
    () => filters.location,
    () => {
        clearTimeout(locationTimeout);
        locationTimeout = setTimeout(applyFilters, 300);
    },
);

const allCount = computed(() =>
    statuses.reduce((sum, status) => sum + (props.statusCounts[status] ?? 0), 0),
);

const donSubtitle = computed(() =>
    filters.status === ''
        ? `${props.donations.total} donaciones registradas`
        : `${props.donations.total} ${props.donations.total === 1 ? 'donación' : 'donaciones'} · ${donationStatusLabel(filters.status)}`,
);

const showCreateCta = computed(() => canCreateDonations(usePage().props.auth.user));
</script>

<template>
    <Head title="Donaciones" />

    <AuthenticatedLayout>
        <div class="page donations-index">
            <div class="donations-index__header">
                <div>
                    <h1 class="donations-index__title">Donaciones</h1>
                    <p class="donations-index__subtitle">{{ donSubtitle }}</p>
                </div>
                <Link v-if="showCreateCta" :href="route('donations.create')" class="btn btn--primary">
                    <span class="donations-index__cta-icon">+</span> Nueva donación
                </Link>
            </div>

            <div class="donations-index__chips">
                <button
                    type="button"
                    class="chip chip--pill"
                    :class="{ 'chip--active': filters.status === '' }"
                    @click="setStatus('')"
                >
                    Todas · {{ allCount }}
                </button>
                <button
                    v-for="status in statuses"
                    :key="status"
                    type="button"
                    class="chip chip--pill"
                    :class="{ 'chip--active': filters.status === status }"
                    @click="setStatus(status)"
                >
                    {{ donationStatusLabel(status) }} · {{ statusCounts[status] ?? 0 }}
                </button>
            </div>

            <div class="surface donations-index__filters">
                <div class="form-field donations-index__filter-field donations-index__filter-field--type">
                    <label class="form-field__label">Tipo</label>
                    <select v-model="filters.donation_type" class="form-field__select">
                        <option value="">Todos</option>
                        <option v-for="type in donationTypes" :key="type" :value="type">
                            {{ donationTypeLabel(type) }}
                        </option>
                    </select>
                </div>

                <div class="form-field donations-index__filter-field donations-index__filter-field--location">
                    <label class="form-field__label">Ubicación</label>
                    <input
                        v-model="filters.location"
                        type="text"
                        placeholder="Buscar ubicación…"
                        class="form-field__input"
                    />
                </div>

                <div class="form-field donations-index__filter-field">
                    <label class="form-field__label">Desde</label>
                    <input v-model="filters.from" type="date" class="form-field__input" />
                </div>

                <div class="form-field donations-index__filter-field">
                    <label class="form-field__label">Hasta</label>
                    <input v-model="filters.to" type="date" class="form-field__input" />
                </div>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    class="btn btn--secondary"
                    @click="clearFilters"
                >
                    Borrar filtros
                </button>
            </div>

            <div class="donations-index__list">
                <div v-if="donations.data.length === 0" class="donations-index__empty">
                    No hay donaciones que coincidan con los filtros.
                </div>

                <Link
                    v-for="donation in donations.data"
                    :key="donation.id"
                    :href="route('donations.show', donation.id)"
                    class="donations-index__row"
                >
                    <span class="avatar avatar--square avatar--lg">
                        {{ donationTypeLabel(donation.donation_type).charAt(0) }}
                    </span>
                    <div class="donations-index__row-info">
                        <div class="donations-index__row-title">
                            {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
                        </div>
                        <div class="donations-index__row-meta">
                            {{ new Date(donation.created_at).toLocaleDateString() }} · {{ donation.creator?.name ?? '—' }}
                        </div>
                    </div>
                    <span class="donations-index__row-type">
                        {{ donationTypeLabel(donation.donation_type) }}
                    </span>
                    <span
                        :class="`donation-card__status donation-card__status--${donation.status}`"
                    >
                        {{ donationStatusLabel(donation.status) }}
                    </span>
                    <span class="donations-index__row-arrow">›</span>
                </Link>
            </div>

            <div v-if="donations.data.length > 0" class="donations-index__pagination">
                <Link
                    v-for="link in donations.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="donations-index__page-link"
                    :class="{
                        'donations-index__page-link--active': link.active,
                        'donations-index__page-link--disabled': !link.url,
                    }"
                >
                    <span v-html="paginationLabel(link.label)"></span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.donations-index__header {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.donations-index__title {
    margin-bottom: 2px;
}

.donations-index__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.donations-index__cta-icon {
    font-size: 17px;
    line-height: 1;
}

.donations-index__chips {
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.donations-index__filters {
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 1rem;
    padding: 1rem;
}

.donations-index__filter-field {
    margin-bottom: 0;
    width: auto;
    flex: 1;
    min-width: 160px;
}

.donations-index__filter-field--location {
    min-width: 200px;
}

.donations-index__list {
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #fff;
}

.donations-index__empty {
    padding: 44px;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: #8a969a;
}

.donations-index__row {
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid #f1f4f3;
    padding: 15px 1rem;
}

.donations-index__row:last-child {
    border-bottom: none;
}

.donations-index__row:hover {
    background-color: #f8faf9;
}

.donations-index__row-info {
    min-width: 0;
    flex: 1;
}

.donations-index__row-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 14.5px;
    font-weight: 600;
}

.donations-index__row-meta {
    font-size: 0.75rem;
    color: #8a969a;
}

.donations-index__row-type {
    display: none;
    white-space: nowrap;
    border-radius: 0.5rem;
    background-color: #f0f4f2;
    padding: 5px 11px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6a787d;
}

.donations-index__row-arrow {
    display: none;
    font-size: 1.125rem;
    color: #c2ccc7;
}

.donations-index__pagination {
    margin-top: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.donations-index__page-link {
    border-radius: 0.5rem;
    border: 1px solid #dbe7e1;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.donations-index__page-link--active {
    border-color: #148f5b;
    background-color: #148f5b;
    color: #fff;
}

.donations-index__page-link--disabled {
    pointer-events: none;
    color: #c2ccc7;
}

@media (min-width: 768px) {
    .donations-index__row {
        padding-inline: 22px;
    }

    .donations-index__row-type,
    .donations-index__row-arrow {
        display: inline-block;
    }
}
</style>
