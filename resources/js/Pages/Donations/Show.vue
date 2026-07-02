<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { getDeliveryWhatsAppUrl, getReceiverWhatsAppUrl } from '@/utils/whatsapp';
import { donationStatusLabel, donationTypeLabel } from '@/utils/labels';

const props = defineProps({
    donation: {
        type: Object,
        required: true,
    },
    statusLogs: {
        type: Array,
        required: true,
    },
    nextStatus: {
        type: String,
        default: null,
    },
    missingFields: {
        type: Array,
        default: () => [],
    },
    optionalFields: {
        type: Array,
        default: () => [],
    },
    fieldLabels: {
        type: Object,
        default: () => ({}),
    },
});

// Inertia reutiliza esta misma instancia de componente al navegar de vuelta
// a esta misma página (p. ej. tras el redirect de un avance de estado
// exitoso), así que <script setup> no vuelve a correr. Si `useForm` se
// llamara una sola vez aquí arriba, el formulario quedaría con el
// `nextStatus`/`missingFields` del PRIMER avance para siempre. Por eso se
// reconstruye cada vez que esas props cambian.
function buildStatusForm() {
    return useForm(
        Object.fromEntries([
            ...props.missingFields.map((field) => [field, '']),
            ...props.optionalFields.map((field) => [field, '']),
            ['status', props.nextStatus],
        ]),
    );
}

const statusForm = ref(buildStatusForm());

watch(
    () => [props.nextStatus, props.missingFields, props.optionalFields],
    () => {
        statusForm.value = buildStatusForm();
    },
    { deep: true },
);

const canAdvance = computed(() =>
    props.missingFields.every(
        (field) => String(statusForm.value[field] ?? '').trim().length > 0,
    ),
);

const advance = () => {
    statusForm.value.patch(route('donations.update-status', props.donation.id));
};

const formatDate = (value) => new Date(value).toLocaleString();

const deliveryAssigned = computed(
    () => Boolean(props.donation.vehicle_type && props.donation.delivery_name),
);

const deliveryWhatsAppUrl = computed(() =>
    deliveryAssigned.value ? getDeliveryWhatsAppUrl(props.donation) : null,
);

const receiverWhatsAppUrl = computed(() =>
    getReceiverWhatsAppUrl(props.donation),
);
</script>

<template>
    <Head title="Detalle de donación" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6">
            <Link
                :href="route('donations.index')"
                class="mb-3 inline-flex items-center gap-1.5 text-sm font-semibold text-[#148f5b] hover:underline"
            >
                <span class="text-base leading-none">←</span> Volver a donaciones
            </Link>

            <h1 class="mb-6">
                {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
            </h1>

            <div class="space-y-4">
                <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <h3 class="text-sm font-semibold text-[#5a686d]">
                        Estado actual:
                        <span
                            :class="`donation-card__status donation-card__status--${donation.status}`"
                        >
                            {{ donationStatusLabel(donation.status) }}
                        </span>
                    </h3>

                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-[#8a969a]">Paciente</dt>
                            <dd>{{ donation.patient_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Ubicación</dt>
                            <dd>{{ donation.location }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Contacto</dt>
                            <dd>{{ donation.contact_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Cédula</dt>
                            <dd>{{ donation.cedula ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Médico / responsable receptor</dt>
                            <dd>{{ donation.receiving_doctor_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Código médico</dt>
                            <dd>{{ donation.receiving_doctor_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Servicio</dt>
                            <dd>{{ donation.receiving_service ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Tipo de vehículo</dt>
                            <dd>{{ donation.vehicle_type ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Nombre de quien entrega</dt>
                            <dd>{{ donation.delivery_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#8a969a]">Contacto de quien entrega</dt>
                            <dd>{{ donation.delivery_contact ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <h3 class="text-sm font-semibold text-[#5a686d]">Artículos</h3>
                    <table class="mt-4 w-full text-left text-sm">
                        <thead class="border-b border-[#eef2f1] bg-[#f6f9f8]">
                            <tr>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Cantidad</th>
                                <th class="px-4 py-2">Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in donation.items"
                                :key="item.id"
                                class="border-b border-[#f1f4f3] last:border-0"
                            >
                                <td class="px-4 py-2">{{ item.name }}</td>
                                <td class="px-4 py-2">{{ item.quantity }}</td>
                                <td class="px-4 py-2">{{ item.unit ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="(deliveryAssigned && donation.delivery_contact) || donation.contact_number"
                    class="rounded-2xl border border-[#e6ede9] bg-white p-6"
                >
                    <h3 class="text-sm font-semibold text-[#5a686d]">
                        Enviar por WhatsApp
                    </h3>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <div v-if="deliveryAssigned && donation.delivery_contact">
                            <a
                                v-if="deliveryWhatsAppUrl"
                                :href="deliveryWhatsAppUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn--success btn--full"
                            >
                                Enviar mensaje al delivery
                            </a>
                            <button
                                v-else
                                type="button"
                                disabled
                                class="btn btn--secondary btn--full"
                            >
                                Falta un número de contacto válido del delivery
                            </button>
                        </div>

                        <div v-if="donation.contact_number">
                            <a
                                v-if="receiverWhatsAppUrl"
                                :href="receiverWhatsAppUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn--success btn--full"
                            >
                                Enviar mensaje a quien recibe
                            </a>
                            <button
                                v-else
                                type="button"
                                disabled
                                class="btn btn--secondary btn--full"
                            >
                                Falta un número de contacto válido de quien recibe
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="nextStatus" class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <h3 class="text-sm font-semibold text-[#5a686d]">
                        Avanzar a "{{ donationStatusLabel(nextStatus) }}"
                    </h3>

                    <form @submit.prevent="advance">
                        <div
                            v-for="field in missingFields"
                            :key="field"
                            class="form-field mt-4"
                        >
                            <label :for="field" class="form-field__label">
                                {{ fieldLabels[field] ?? field }}
                            </label>
                            <input
                                :id="field"
                                v-model="statusForm[field]"
                                type="text"
                                class="form-field__input"
                                required
                            />
                            <p v-if="statusForm.errors[field]" class="form-field__error">
                                {{ statusForm.errors[field] }}
                            </p>
                        </div>

                        <div
                            v-for="field in optionalFields"
                            :key="field"
                            class="form-field mt-4"
                        >
                            <label :for="field" class="form-field__label">
                                {{ fieldLabels[field] ?? field }}
                            </label>
                            <input
                                :id="field"
                                v-model="statusForm[field]"
                                type="text"
                                class="form-field__input"
                            />
                            <p v-if="statusForm.errors[field]" class="form-field__error">
                                {{ statusForm.errors[field] }}
                            </p>
                        </div>

                        <p v-if="statusForm.errors.status" class="form-field__error">
                            {{ statusForm.errors.status }}
                        </p>

                        <div class="mt-6">
                            <button
                                type="submit"
                                class="btn btn--primary btn--full"
                                :disabled="!canAdvance || statusForm.processing"
                                :class="{ 'opacity-25': !canAdvance || statusForm.processing }"
                            >
                                Avanzar a {{ donationStatusLabel(nextStatus) }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-[#e6ede9] bg-white p-6">
                    <h3 class="text-sm font-semibold text-[#5a686d]">
                        Historial de estados
                    </h3>

                    <ol class="status-timeline mt-4">
                        <li v-for="log in statusLogs" :key="log.id" class="status-timeline__item">
                            <div class="status-timeline__date">{{ formatDate(log.changed_at) }}</div>
                            <div class="status-timeline__transition">
                                <span v-if="log.from_status">{{ donationStatusLabel(log.from_status) }}</span>
                                <span v-else class="italic">inicio</span>
                                →
                                <span>{{ donationStatusLabel(log.to_status) }}</span>
                            </div>
                            <div class="status-timeline__actor">
                                por {{ log.changed_by?.name ?? '—' }}
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
