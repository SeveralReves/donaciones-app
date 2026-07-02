<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { donationStatusLabel, donationTypeLabel } from '@/utils/labels';

defineProps({
    totalDonations: {
        type: Number,
        required: true,
    },
    totalItems: {
        type: Number,
        required: true,
    },
    donationsToday: {
        type: Number,
        required: true,
    },
    donationsThisWeek: {
        type: Number,
        required: true,
    },
    statusCounts: {
        type: Object,
        default: () => ({}),
    },
    typeCounts: {
        type: Object,
        default: () => ({}),
    },
    recentDonations: {
        type: Array,
        default: () => [],
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

const formatDate = (value) => new Date(value).toLocaleDateString();
</script>

<template>
    <Head title="Panel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Panel
            </h2>
        </template>

        <div class="container">
            <div class="space-y-6">
                <div class="stat-grid">
                    <div class="stat">
                        <span class="stat__value">{{ totalDonations }}</span>
                        <span class="stat__label">Donaciones totales</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value">{{ donationsToday }}</span>
                        <span class="stat__label">Creadas hoy</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value">{{ donationsThisWeek }}</span>
                        <span class="stat__label">Creadas esta semana</span>
                    </div>
                    <div class="stat">
                        <span class="stat__value">{{ totalItems }}</span>
                        <span class="stat__label">Artículos registrados</span>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Donaciones por estado
                    </h3>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="status in statuses"
                            :key="status"
                            :class="`donation-card__status donation-card__status--${status}`"
                        >
                            {{ statusCounts[status] ?? 0 }} {{ donationStatusLabel(status) }}
                        </span>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Donaciones por tipo
                    </h3>

                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div v-for="type in donationTypes" :key="type">
                            <dt class="text-gray-500">{{ donationTypeLabel(type) }}</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ typeCounts[type] ?? 0 }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">
                            Donaciones recientes
                        </h3>
                        <Link
                            :href="route('donations.index')"
                            class="text-sm text-indigo-600 hover:underline"
                        >
                            Ver todas
                        </Link>
                    </div>

                    <p v-if="recentDonations.length === 0" class="mt-4 text-sm text-gray-500">
                        Todavía no hay donaciones registradas.
                    </p>

                    <ul v-else class="mt-4 divide-y divide-gray-100">
                        <li v-for="donation in recentDonations" :key="donation.id" class="py-3">
                            <Link
                                :href="route('donations.show', donation.id)"
                                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ formatDate(donation.created_at) }} · creada por {{ donation.creator?.name ?? '—' }}
                                    </div>
                                </div>
                                <span
                                    :class="`donation-card__status donation-card__status--${donation.status}`"
                                >
                                    {{ donationStatusLabel(donation.status) }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
