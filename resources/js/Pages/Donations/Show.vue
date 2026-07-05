<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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

// null si no aplica (unit distinto de 'cajas', o units_per_box sin definir
// para ese item) — el molde de "cajas sueltas sin desglosar" sigue siendo
// válido, no todo lo que se dona en cajas trae ese dato.
const itemBoxTotal = (item) => {
    if (item.unit !== 'cajas' || item.units_per_box === null || item.units_per_box === undefined) {
        return null;
    }

    return Number(item.quantity) * Number(item.units_per_box);
};

// Espejo de DonationStatusFlow::canTransitionTo() en el backend: un
// voluntario puede avanzar cualquier estado excepto confirmar la recepción.
const isVolunteer = computed(() => usePage().props.auth.user.rol === 'voluntario');

const canConfirmReceipt = computed(
    () => ! (props.nextStatus === 'recibido' && isVolunteer.value),
);

const canCancel = computed(
    () => ! ['recibido', 'cancelada'].includes(props.donation.status),
);

const showCancelModal = ref(false);

const cancelForm = useForm({
    status: 'cancelada',
    cancellation_reason: '',
});

const openCancelModal = () => {
    cancelForm.reset();
    cancelForm.clearErrors();
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
};

const submitCancellation = () => {
    cancelForm.patch(route('donations.update-status', props.donation.id), {
        preserveScroll: true,
        onSuccess: () => closeCancelModal(),
    });
};

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
        <div class="page donation-detail">
            <Link :href="route('donations.index')" class="donation-detail__back">
                <span class="donation-detail__back-icon">←</span> Volver a donaciones
            </Link>

            <h1 class="donation-detail__title">
                {{ donationTypeLabel(donation.donation_type) }} — {{ donation.location }}
            </h1>

            <div class="donation-detail__sections">
                <div class="surface">
                    <div class="donation-detail__status-row">
                        <h3 class="donation-detail__heading">
                            Estado actual:
                            <span
                                :class="`donation-card__status donation-card__status--${donation.status}`"
                            >
                                {{ donationStatusLabel(donation.status) }}
                            </span>
                        </h3>

                        <button
                            v-if="canCancel"
                            type="button"
                            class="btn btn--danger donation-detail__cancel-btn"
                            @click="openCancelModal"
                        >
                            Cancelar donación
                        </button>
                    </div>

                    <p v-if="donation.status === 'cancelada' && donation.cancellation_reason" class="donation-detail__cancellation-reason">
                        <strong>Motivo de cancelación:</strong> {{ donation.cancellation_reason }}
                    </p>

                    <dl class="donation-detail__fields">
                        <div>
                            <dt class="donation-detail__field-label">Paciente</dt>
                            <dd>{{ donation.patient_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Ubicación</dt>
                            <dd>{{ donation.location }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Contacto</dt>
                            <dd>{{ donation.contact_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Cédula</dt>
                            <dd>{{ donation.cedula ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Médico / responsable receptor</dt>
                            <dd>{{ donation.receiving_doctor_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Código médico</dt>
                            <dd>{{ donation.receiving_doctor_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Servicio</dt>
                            <dd>{{ donation.receiving_service ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Tipo de vehículo</dt>
                            <dd>{{ donation.vehicle_type ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Nombre de quien entrega</dt>
                            <dd>{{ donation.delivery_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="donation-detail__field-label">Contacto de quien entrega</dt>
                            <dd>{{ donation.delivery_contact ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="surface">
                    <h3 class="donation-detail__heading">Artículos</h3>
                    <table class="donation-detail__table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in donation.items" :key="item.id">
                                <td>{{ item.name }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>
                                    {{ item.unit ?? '—' }}
                                    <span v-if="itemBoxTotal(item) !== null" class="donation-detail__box-total">
                                        ({{ itemBoxTotal(item) }} unidades)
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="(deliveryAssigned && donation.delivery_contact) || donation.contact_number"
                    class="surface"
                >
                    <h3 class="donation-detail__heading">Enviar por WhatsApp</h3>

                    <div class="donation-detail__whatsapp">
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

                <div v-if="nextStatus && !canConfirmReceipt" class="surface">
                    <h3 class="donation-detail__heading">Avanzar a "Recibido"</h3>
                    <p class="donation-detail__receipt-notice">
                        Confirmar la recepción de esta donación requiere un rol con ese
                        permiso (admin, médico u odontólogo). Pide a alguien con ese rol que
                        confirme la recepción.
                    </p>
                </div>

                <div v-else-if="nextStatus" class="surface">
                    <h3 class="donation-detail__heading">
                        Avanzar a "{{ donationStatusLabel(nextStatus) }}"
                    </h3>

                    <form @submit.prevent="advance">
                        <div
                            v-for="field in missingFields"
                            :key="field"
                            class="form-field donation-detail__form-field"
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
                            class="form-field donation-detail__form-field"
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

                        <div class="donation-detail__advance">
                            <button
                                type="submit"
                                class="btn btn--primary btn--full"
                                :disabled="!canAdvance || statusForm.processing"
                                :class="{ 'is-busy': !canAdvance || statusForm.processing }"
                            >
                                Avanzar a {{ donationStatusLabel(nextStatus) }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="surface">
                    <h3 class="donation-detail__heading">Historial de estados</h3>

                    <ol class="status-timeline donation-detail__timeline">
                        <li v-for="log in statusLogs" :key="log.id" class="status-timeline__item">
                            <div class="status-timeline__date">{{ formatDate(log.changed_at) }}</div>
                            <div class="status-timeline__transition">
                                <span v-if="log.from_status">{{ donationStatusLabel(log.from_status) }}</span>
                                <span v-else class="donation-detail__timeline-start">inicio</span>
                                →
                                <span>{{ donationStatusLabel(log.to_status) }}</span>
                            </div>
                            <div class="status-timeline__actor">
                                por {{ log.changed_by?.name ?? '—' }}
                            </div>
                            <div
                                v-if="log.to_status === 'cancelada' && donation.cancellation_reason"
                                class="status-timeline__reason"
                            >
                                Motivo: {{ donation.cancellation_reason }}
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <Modal :show="showCancelModal" @close="closeCancelModal">
            <div class="modal__content">
                <h2 class="form-section__title">Cancelar donación</h2>
                <p class="form-section__description">
                    Esta acción no se puede deshacer. Si algún artículo tenía stock reservado
                    del catálogo, se devolverá automáticamente a la cantidad disponible.
                </p>

                <form @submit.prevent="submitCancellation" class="cancel-donation-form">
                    <div class="form-field">
                        <label for="cancellation_reason" class="form-field__label">
                            Motivo de la cancelación
                        </label>
                        <textarea
                            id="cancellation_reason"
                            v-model="cancelForm.cancellation_reason"
                            class="form-field__input"
                            rows="3"
                            required
                        ></textarea>
                        <p v-if="cancelForm.errors.cancellation_reason" class="form-field__error">
                            {{ cancelForm.errors.cancellation_reason }}
                        </p>
                    </div>

                    <p v-if="cancelForm.errors.status" class="form-field__error">
                        {{ cancelForm.errors.status }}
                    </p>

                    <div class="cancel-donation-form__actions">
                        <button type="button" class="btn btn--secondary" @click="closeCancelModal">
                            Volver
                        </button>
                        <button
                            type="submit"
                            class="btn btn--danger"
                            :class="{ 'is-busy': cancelForm.processing }"
                            :disabled="cancelForm.processing"
                        >
                            Confirmar cancelación
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.donation-detail__back {
    margin-bottom: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.donation-detail__back:hover {
    text-decoration: underline;
}

.donation-detail__back-icon {
    font-size: 1rem;
    line-height: 1;
}

.donation-detail__title {
    margin-bottom: 1.5rem;
}

.donation-detail__sections {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.donation-detail__heading {
    font-size: 0.875rem;
    font-weight: 600;
    color: #5a686d;
}

.donation-detail__status-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.donation-detail__cancel-btn {
    min-height: 36px;
    padding: 6px 14px;
    font-size: 0.8125rem;
}

.donation-detail__cancellation-reason {
    margin-top: 0.75rem;
    border-radius: 0.75rem;
    background-color: #fbeae8;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #c0392b;
}

.donation-detail__fields {
    margin-top: 1rem;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    font-size: 0.875rem;
}

.donation-detail__field-label {
    color: #8a969a;
}

.donation-detail__table {
    margin-top: 1rem;
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    border-collapse: collapse;
}

.donation-detail__table thead {
    border-bottom: 1px solid #eef2f1;
    background-color: #f6f9f8;
}

.donation-detail__table th,
.donation-detail__table td {
    padding: 0.5rem 1rem;
}

.donation-detail__table tbody tr {
    border-bottom: 1px solid #f1f4f3;
}

.donation-detail__table tbody tr:last-child {
    border-bottom: none;
}

.donation-detail__box-total {
    font-size: 0.8125rem;
    color: #8a969a;
}

.donation-detail__whatsapp {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.donation-detail__form-field {
    margin-top: 1rem;
}

.donation-detail__advance {
    margin-top: 1.5rem;
}

.donation-detail__receipt-notice {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: #7a8b84;
}

.donation-detail__timeline {
    margin-top: 1rem;
}

.donation-detail__timeline-start {
    font-style: italic;
}

.status-timeline__reason {
    font-size: 0.8125rem;
    color: #c0392b;
}

.cancel-donation-form {
    margin-top: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.cancel-donation-form__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

@media (min-width: 768px) {
    .donation-detail__whatsapp {
        flex-direction: row;
        flex-wrap: wrap;
    }
}
</style>
