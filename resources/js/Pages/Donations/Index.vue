<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';

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

                <Link
                    :href="route('donations.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                >
                    Nueva donación
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap gap-3 rounded-md bg-white p-4 text-sm shadow-sm">
                    <span
                        v-for="status in statuses"
                        :key="status"
                        class="rounded-full bg-gray-100 px-3 py-1"
                    >
                        {{ statusCounts[status] ?? 0 }} {{ status }}
                    </span>
                </div>

                <div class="mb-4 flex flex-wrap items-end gap-4 rounded-md bg-white p-4 shadow-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="filters.status"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Todos</option>
                            <option v-for="status in statuses" :key="status" :value="status">
                                {{ status }}
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
                                {{ type }}
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

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Ubicación</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Creado por</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="donation in donations.data"
                                :key="donation.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-6 py-3">{{ donation.donation_type }}</td>
                                <td class="px-6 py-3">{{ donation.location }}</td>
                                <td class="px-6 py-3">{{ donation.status }}</td>
                                <td class="px-6 py-3">
                                    {{ new Date(donation.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-3">{{ donation.creator?.name ?? '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <Link
                                        :href="route('donations.show', donation.id)"
                                        class="text-indigo-600 hover:underline"
                                    >
                                        Ver detalle
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="donations.data.length === 0">
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    No hay donaciones que coincidan con los filtros.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <Link
                        v-for="link in donations.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded-md border px-3 py-1 text-sm"
                        :class="{
                            'bg-indigo-600 text-white': link.active,
                            'text-gray-400 pointer-events-none': !link.url,
                        }"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
