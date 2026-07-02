<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { donationStatusLabel, donationTypeLabel } from '@/utils/labels';

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
</script>

<template>
    <Head title="Donaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Donaciones
                </h2>

                <Link :href="route('donations.create')" class="btn btn--primary">
                    Nueva donación
                </Link>
            </div>
        </template>

        <div class="container">
            <div class="mb-4 flex flex-wrap gap-2">
                <span
                    v-for="status in statuses"
                    :key="status"
                    :class="`donation-card__status donation-card__status--${status}`"
                >
                    {{ statusCounts[status] ?? 0 }} {{ donationStatusLabel(status) }}
                </span>
            </div>

            <div class="mb-4 flex flex-wrap items-end gap-4 rounded-md bg-white p-4 shadow-sm">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select
                        v-model="filters.status"
                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Todos</option>
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ donationStatusLabel(status) }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select
                        v-model="filters.donation_type"
                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Todos</option>
                        <option v-for="type in donationTypes" :key="type" :value="type">
                            {{ donationTypeLabel(type) }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                    <input
                        v-model="filters.location"
                        type="text"
                        placeholder="Buscar ubicación..."
                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Desde</label>
                    <input
                        v-model="filters.from"
                        type="date"
                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Hasta</label>
                    <input
                        v-model="filters.to"
                        type="date"
                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    @click="clearFilters"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                >
                    Borrar filtros
                </button>
            </div>

            <div v-if="donations.data.length === 0" class="rounded-md bg-white p-6 text-center text-sm text-gray-500 shadow-sm">
                No hay donaciones que coincidan con los filtros.
            </div>

            <div v-else class="donation-grid">
                <article
                    v-for="donation in donations.data"
                    :key="donation.id"
                    class="donation-card"
                >
                    <header class="donation-card__header">
                        <span class="donation-card__title">
                            {{ donationTypeLabel(donation.donation_type) }}
                        </span>
                        <span
                            :class="`donation-card__status donation-card__status--${donation.status}`"
                        >
                            {{ donationStatusLabel(donation.status) }}
                        </span>
                    </header>

                    <div class="donation-card__body">
                        <div>{{ donation.location }}</div>
                        <div>{{ new Date(donation.created_at).toLocaleDateString() }}</div>
                        <div>Creado por {{ donation.creator?.name ?? '—' }}</div>
                    </div>

                    <div class="donation-card__footer">
                        <Link
                            :href="route('donations.show', donation.id)"
                            class="btn btn--secondary btn--full"
                        >
                            Ver detalle
                        </Link>
                    </div>
                </article>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <Link
                    v-for="link in donations.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="paginationLabel(link.label)"
                    class="rounded-md border px-3 py-1 text-sm"
                    :class="{
                        'bg-indigo-600 text-white': link.active,
                        'text-gray-400 pointer-events-none': !link.url,
                    }"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
