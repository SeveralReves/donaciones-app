<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { donationStatusLabel, donationTypeLabel } from '@/utils/labels';

const props = defineProps({
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

const statusBarColors = {
    creada: '#5b9bd8',
    en_proceso: '#e0a94a',
    esperando_delivery: '#8f88cf',
    en_camino: '#2aa89b',
    recibido: '#148f5b',
};

const donationTypes = ['insumos_medicos', 'higiene', 'alimentos', 'otros'];

const maxStatusCount = computed(() =>
    Math.max(1, ...statuses.map((status) => props.statusCounts[status] ?? 0)),
);

const statusBarWidth = (status) =>
    `${((props.statusCounts[status] ?? 0) / maxStatusCount.value) * 100}%`;

const initial = (donation) => donationTypeLabel(donation.donation_type).charAt(0);

const formatDate = (value) => new Date(value).toLocaleDateString();
</script>

<template>
    <Head title="Panel" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6">
            <h1 class="mb-1">Panel</h1>
            <p class="mb-6 text-sm font-normal text-[#7a8b84]">
                Resumen de la operación de hoy
            </p>

            <div class="stat-grid mb-4">
                <div class="stat stat--highlight">
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

            <div class="mb-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <div class="mb-[18px] text-[15px] font-semibold">Donaciones por estado</div>
                    <div class="flex flex-col gap-[13px]">
                        <div
                            v-for="status in statuses"
                            :key="status"
                            class="flex items-center gap-3"
                        >
                            <span class="w-[110px] shrink-0 text-[13px] font-medium text-[#5a686d]">
                                {{ donationStatusLabel(status) }}
                            </span>
                            <div class="h-[9px] flex-1 rounded-full bg-[#eef2f1]">
                                <div
                                    class="h-full rounded-full"
                                    :style="{ width: statusBarWidth(status), backgroundColor: statusBarColors[status] }"
                                ></div>
                            </div>
                            <b class="w-4 shrink-0 text-right text-[13px] font-bold">
                                {{ statusCounts[status] ?? 0 }}
                            </b>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <div class="mb-[18px] text-[15px] font-semibold">Donaciones por tipo</div>
                    <div class="grid grid-cols-2 gap-[14px]">
                        <div
                            v-for="type in donationTypes"
                            :key="type"
                            class="rounded-xl bg-[#eef7f0] px-4 py-[15px]"
                        >
                            <div class="text-[26px] font-extrabold leading-none text-[#148f5b]">
                                {{ typeCounts[type] ?? 0 }}
                            </div>
                            <div class="mt-[5px] text-xs font-medium text-[#6a787d]">
                                {{ donationTypeLabel(type) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                <div class="mb-1 flex items-center justify-between">
                    <div class="text-[15px] font-semibold">Donaciones recientes</div>
                    <Link
                        :href="route('donations.index')"
                        class="text-[13px] font-semibold text-[#148f5b] hover:underline"
                    >
                        Ver todas
                    </Link>
                </div>

                <p v-if="recentDonations.length === 0" class="mt-4 text-sm text-[#7a8b84]">
                    Todavía no hay donaciones registradas.
                </p>

                <div v-else>
                    <Link
                        v-for="donation in recentDonations"
                        :key="donation.id"
                        :href="route('donations.show', donation.id)"
                        class="flex items-center gap-[13px] border-b border-[#eef2f1] py-[13px] last:border-0"
                    >
                        <span class="avatar avatar--square">{{ initial(donation) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold">
                                {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
                            </div>
                            <div class="text-xs text-[#8a969a]">
                                {{ formatDate(donation.created_at) }} · {{ donation.creator?.name ?? '—' }}
                            </div>
                        </div>
                        <span
                            :class="`donation-card__status donation-card__status--${donation.status}`"
                        >
                            {{ donationStatusLabel(donation.status) }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
