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

const donationTypes = ['insumos_medicos', 'medicinas', 'higiene', 'alimentos', 'miscelaneos', 'otros'];

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
        <div class="page dashboard">
            <h1 class="dashboard__title">Panel</h1>
            <p class="dashboard__subtitle">Resumen de la operación de hoy</p>

            <div class="stat-grid dashboard__stats">
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

            <div class="dashboard__panels">
                <div class="surface">
                    <div class="surface__title">Donaciones por estado</div>
                    <div class="dashboard__status-list">
                        <div
                            v-for="status in statuses"
                            :key="status"
                            class="dashboard__status-row"
                        >
                            <span class="dashboard__status-label">
                                {{ donationStatusLabel(status) }}
                            </span>
                            <div class="dashboard__status-track">
                                <div
                                    class="dashboard__status-fill"
                                    :style="{ width: statusBarWidth(status), backgroundColor: statusBarColors[status] }"
                                ></div>
                            </div>
                            <b class="dashboard__status-count">
                                {{ statusCounts[status] ?? 0 }}
                            </b>
                        </div>
                    </div>
                </div>

                <div class="surface">
                    <div class="surface__title">Donaciones por tipo</div>
                    <div class="dashboard__types">
                        <div
                            v-for="type in donationTypes"
                            :key="type"
                            class="dashboard__type-tile"
                        >
                            <div class="dashboard__type-value">
                                {{ typeCounts[type] ?? 0 }}
                            </div>
                            <div class="dashboard__type-label">
                                {{ donationTypeLabel(type) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface">
                <div class="dashboard__recent-header">
                    <div class="surface__title dashboard__recent-title-bar">Donaciones recientes</div>
                    <Link :href="route('donations.index')" class="dashboard__recent-link">
                        Ver todas
                    </Link>
                </div>

                <p v-if="recentDonations.length === 0" class="dashboard__recent-empty">
                    Todavía no hay donaciones registradas.
                </p>

                <div v-else>
                    <Link
                        v-for="donation in recentDonations"
                        :key="donation.id"
                        :href="route('donations.show', donation.id)"
                        class="dashboard__recent-item"
                    >
                        <span class="avatar avatar--square">{{ initial(donation) }}</span>
                        <div class="dashboard__recent-info">
                            <div class="dashboard__recent-item-title">
                                {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
                            </div>
                            <div class="dashboard__recent-meta">
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

<style scoped>
.dashboard__title {
    margin-bottom: 0.25rem;
}

.dashboard__subtitle {
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.dashboard__stats {
    margin-bottom: 1rem;
}

.dashboard__panels {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

.dashboard__status-list {
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.dashboard__status-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dashboard__status-label {
    width: 110px;
    flex-shrink: 0;
    font-size: 13px;
    font-weight: 500;
    color: #5a686d;
}

.dashboard__status-track {
    height: 9px;
    flex: 1;
    border-radius: 999px;
    background-color: #eef2f1;
}

.dashboard__status-fill {
    height: 100%;
    border-radius: 999px;
}

.dashboard__status-count {
    width: 1rem;
    flex-shrink: 0;
    text-align: right;
    font-size: 13px;
    font-weight: 700;
}

.dashboard__types {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.dashboard__type-tile {
    border-radius: 0.75rem;
    background-color: #eef7f0;
    padding: 15px 1rem;
}

.dashboard__type-value {
    font-size: 26px;
    font-weight: 800;
    line-height: 1;
    color: #148f5b;
}

.dashboard__type-label {
    margin-top: 5px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6a787d;
}

.dashboard__recent-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}

.dashboard__recent-title-bar {
    margin-bottom: 0;
}

.dashboard__recent-link {
    font-size: 13px;
    font-weight: 600;
    color: #148f5b;
}

.dashboard__recent-link:hover {
    text-decoration: underline;
}

.dashboard__recent-empty {
    margin-top: 1rem;
    font-size: 0.875rem;
    color: #7a8b84;
}

.dashboard__recent-item {
    display: flex;
    align-items: center;
    gap: 13px;
    border-bottom: 1px solid #eef2f1;
    padding-block: 13px;
}

.dashboard__recent-item:last-child {
    border-bottom: none;
}

.dashboard__recent-info {
    min-width: 0;
    flex: 1;
}

.dashboard__recent-item-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.875rem;
    font-weight: 600;
}

.dashboard__recent-meta {
    font-size: 0.75rem;
    color: #8a969a;
}

@media (min-width: 768px) {
    .dashboard__panels {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
