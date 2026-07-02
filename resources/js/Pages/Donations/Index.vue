<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    donations: {
        type: Object,
        required: true,
    },
    filters: {
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
});

let locationTimeout = null;

const applyFilters = () => {
    router.get(route('donations.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

watch(
    () => [filters.status, filters.donation_type],
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Donaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-wrap items-end gap-4">
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
                    </div>

                    <Link
                        :href="route('donations.create')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                    >
                        Nueva donación
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Ubicación</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Fecha</th>
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
                                <td class="px-6 py-3 text-right">
                                    <Link
                                        :href="`/donations/${donation.id}`"
                                        class="text-indigo-600 hover:underline"
                                    >
                                        Ver detalle
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="donations.data.length === 0">
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">
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
