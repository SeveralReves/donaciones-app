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
</script>

<template>
    <Head title="Donaciones" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6">
            <div class="mb-[18px] flex flex-wrap items-center justify-between gap-3.5">
                <div>
                    <h1 class="mb-0.5">Donaciones</h1>
                    <p class="text-sm font-normal text-[#7a8b84]">{{ donSubtitle }}</p>
                </div>
                <Link :href="route('donations.create')" class="btn btn--primary">
                    <span class="text-[17px] leading-none">+</span> Nueva donación
                </Link>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
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

            <div class="mb-4 flex flex-wrap items-end gap-4 rounded-2xl border border-[#e6ede9] bg-white p-4">
                <div class="form-field !mb-0 w-auto min-w-[160px] flex-1">
                    <label class="form-field__label">Tipo</label>
                    <select v-model="filters.donation_type" class="form-field__select">
                        <option value="">Todos</option>
                        <option v-for="type in donationTypes" :key="type" :value="type">
                            {{ donationTypeLabel(type) }}
                        </option>
                    </select>
                </div>

                <div class="form-field !mb-0 w-auto min-w-[200px] flex-1">
                    <label class="form-field__label">Ubicación</label>
                    <input
                        v-model="filters.location"
                        type="text"
                        placeholder="Buscar ubicación…"
                        class="form-field__input"
                    />
                </div>

                <div class="form-field !mb-0 w-auto flex-1">
                    <label class="form-field__label">Desde</label>
                    <input v-model="filters.from" type="date" class="form-field__input" />
                </div>

                <div class="form-field !mb-0 w-auto flex-1">
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

            <div class="overflow-hidden rounded-2xl border border-[#e6ede9] bg-white">
                <div v-if="donations.data.length === 0" class="p-11 text-center text-sm font-medium text-[#8a969a]">
                    No hay donaciones que coincidan con los filtros.
                </div>

                <Link
                    v-for="donation in donations.data"
                    :key="donation.id"
                    :href="route('donations.show', donation.id)"
                    class="flex items-center gap-3.5 border-b border-[#f1f4f3] px-4 py-[15px] last:border-0 hover:bg-[#f8faf9] sm:px-[22px]"
                >
                    <span class="avatar avatar--square avatar--lg">
                        {{ donationTypeLabel(donation.donation_type).charAt(0) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[14.5px] font-semibold">
                            {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
                        </div>
                        <div class="text-xs text-[#8a969a]">
                            {{ new Date(donation.created_at).toLocaleDateString() }} · {{ donation.creator?.name ?? '—' }}
                        </div>
                    </div>
                    <span class="hidden whitespace-nowrap rounded-lg bg-[#f0f4f2] px-[11px] py-[5px] text-xs font-medium text-[#6a787d] sm:inline-block">
                        {{ donationTypeLabel(donation.donation_type) }}
                    </span>
                    <span
                        :class="`donation-card__status donation-card__status--${donation.status}`"
                    >
                        {{ donationStatusLabel(donation.status) }}
                    </span>
                    <span class="hidden text-lg text-[#c2ccc7] sm:inline">›</span>
                </Link>
            </div>

            <div v-if="donations.data.length > 0" class="mt-4 flex flex-wrap gap-2">
                <Link
                    v-for="link in donations.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-lg border border-[#dbe7e1] px-3 py-1 text-sm"
                    :class="{
                        'border-[#148f5b] bg-[#148f5b] text-white': link.active,
                        'pointer-events-none text-[#c2ccc7]': !link.url,
                    }"
                >
                    <span v-html="paginationLabel(link.label)"></span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
