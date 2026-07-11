<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { donationTypeLabel } from '@/utils/labels';
import { groupStockNeedsByType } from '@/utils/needs';
import { buildWhatsAppUrl } from '@/utils/whatsapp';

const props = defineProps({
    stockNeeds: {
        type: Array,
        default: () => [],
    },
    additionalNeeds: {
        type: Array,
        default: () => [],
    },
    // Solo description + total_children_needing (ver NeedsController) — no
    // debe agregarse acá ningún campo que identifique a un niño puntual.
    childProgramNeeds: {
        type: Array,
        default: () => [],
    },
});

const groupedStockNeeds = computed(() => groupStockNeedsByType(props.stockNeeds));

const hasAnyNeeds = computed(
    () =>
        props.stockNeeds.length > 0 ||
        props.additionalNeeds.length > 0 ||
        props.childProgramNeeds.length > 0,
);

// Datos de contacto de la fundación, fijos a propósito: no son parte del
// catálogo ni de las necesidades adicionales, son quiénes responden por la
// organización.
const CONTACTS = [
    { role: 'Directora de la fundación', name: 'Dra. Arianni Quintana', phone: '+584142589339' },
    { role: 'Subdirector', name: 'Od. William Florez', phone: '+584120878902' },
    {
        role: 'Coordinador de Insumos y Logística, adjunto a Dirección General',
        name: 'Dr. Jose Daniel Cruz',
        phone: '+584129285046',
    },
];

const CONTACT_MESSAGE = 'Hola, tengo una consulta sobre las necesidades de la fundación.';

function formatPhone(phone) {
    const digits = phone.replace(/\D/g, '');
    return `+${digits.slice(0, 2)} ${digits.slice(2, 5)}-${digits.slice(5, 8)}-${digits.slice(8)}`;
}

const contacts = CONTACTS.map((contact) => ({
    ...contact,
    formattedPhone: formatPhone(contact.phone),
    whatsAppUrl: buildWhatsAppUrl(contact.phone, CONTACT_MESSAGE),
}));
</script>

<template>
    <Head title="Necesidades actuales" />

    <div class="needs-page">
        <header class="needs-page__header">
            <span class="needs-page__brand-mark">
                <span class="needs-page__brand-icon">
                    <span class="needs-page__brand-bar needs-page__brand-bar--h"></span>
                    <span class="needs-page__brand-bar needs-page__brand-bar--v"></span>
                </span>
            </span>
            <h1 class="needs-page__brand-name">Control de Donaciones</h1>
            <p class="needs-page__subtitle">Necesidades actuales de la fundación</p>
        </header>

        <main class="page page--narrow needs-page__content">
            <p v-if="!hasAnyNeeds" class="surface needs-page__empty">
                Por ahora no hay necesidades urgentes. ¡Gracias por tu apoyo!
            </p>

            <section
                v-for="group in groupedStockNeeds"
                :key="group.type"
                class="surface needs-page__section"
            >
                <h2 class="surface__title">{{ donationTypeLabel(group.type) }}</h2>
                <ul class="needs-page__list">
                    <li v-for="item in group.items" :key="item.id" class="needs-page__item">
                        <span class="needs-page__item-name">{{ item.name }}</span>
                        <span class="needs-page__item-qty">
                            Se necesitan {{ item.quantity_needed }} {{ item.unit }}
                        </span>
                    </li>
                </ul>
            </section>

            <section v-if="additionalNeeds.length > 0" class="surface needs-page__section">
                <h2 class="surface__title">Otras necesidades</h2>
                <ul class="needs-page__list">
                    <li
                        v-for="need in additionalNeeds"
                        :key="need.id"
                        class="needs-page__item"
                    >
                        <span class="needs-page__item-name">{{ need.description }}</span>
                        <span v-if="need.quantity_needed" class="needs-page__item-qty">
                            {{ need.quantity_needed }}
                        </span>
                    </li>
                </ul>
            </section>

            <section v-if="childProgramNeeds.length > 0" class="surface needs-page__section">
                <h2 class="surface__title">Necesidades del programa de atención infantil</h2>
                <ul class="needs-page__list">
                    <li
                        v-for="need in childProgramNeeds"
                        :key="need.description"
                        class="needs-page__item"
                    >
                        <span class="needs-page__item-name">{{ need.description }}</span>
                        <span class="needs-page__item-qty">
                            Necesaria para {{ need.total_children_needing }}
                            {{ need.total_children_needing === 1 ? 'niño' : 'niños' }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="surface needs-page__section">
                <h2 class="surface__title">Contacto</h2>
                <ul class="needs-page__contacts" role="list">
                    <li v-for="contact in contacts" :key="contact.phone" class="needs-page__contact">
                        <div class="needs-page__contact-role">{{ contact.role }}</div>
                        <div class="needs-page__contact-name">{{ contact.name }}</div>
                        <a
                            v-if="contact.whatsAppUrl"
                            :href="contact.whatsAppUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="needs-page__contact-phone"
                        >
                            {{ contact.formattedPhone }}
                        </a>
                        <span v-else class="needs-page__contact-phone">{{ contact.formattedPhone }}</span>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="needs-page__footer">
            Gracias por ayudar a quienes lo necesitan.
        </footer>
    </div>
</template>

<style scoped>
.needs-page {
    min-height: 100vh;
    background-color: #eef3f1;
}

.needs-page__header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.625rem;
    background-color: #0f5132;
    padding: 2rem 1rem 1.75rem;
    text-align: center;
}

.needs-page__brand-mark {
    display: flex;
    height: 44px;
    width: 44px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background-color: #3ec37a;
}

.needs-page__brand-icon {
    position: relative;
    display: block;
    height: 18px;
    width: 18px;
}

.needs-page__brand-bar {
    position: absolute;
    border-radius: 2px;
    background-color: #0f5132;
}

.needs-page__brand-bar--h {
    left: 0;
    top: 7px;
    height: 4px;
    width: 18px;
}

.needs-page__brand-bar--v {
    left: 7px;
    top: 0;
    height: 18px;
    width: 4px;
}

.needs-page__brand-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #fff;
}

.needs-page__subtitle {
    font-size: 0.875rem;
    color: #bfe0cd;
}

.needs-page__content {
    padding-top: 1.5rem;
    padding-bottom: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.needs-page__empty {
    text-align: center;
    font-size: 0.9375rem;
    color: #5a686d;
}

.needs-page__list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.needs-page__item {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.375rem 1rem;
    border-bottom: 1px solid #f1f4f3;
    padding-bottom: 0.75rem;
}

.needs-page__item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.needs-page__item-name {
    font-weight: 600;
    color: #14322a;
}

.needs-page__item-qty {
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
    white-space: nowrap;
}

.needs-page__contacts {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.needs-page__contact {
    border-bottom: 1px solid #f1f4f3;
    padding-bottom: 1rem;
}

.needs-page__contact:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.needs-page__contact-role {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
}

.needs-page__contact-name {
    margin-top: 0.125rem;
    font-weight: 600;
    color: #14322a;
}

.needs-page__contact-phone {
    display: inline-block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.needs-page__footer {
    padding: 1.5rem 1rem 2rem;
    text-align: center;
    font-size: 0.8125rem;
    color: #7a8b84;
}
</style>
