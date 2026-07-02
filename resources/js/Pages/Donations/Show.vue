<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Donación — {{ donationTypeLabel(donation.donation_type) }} ({{ donation.location }})
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Estado actual:
                        <span class="font-mono">{{ donationStatusLabel(donation.status) }}</span>
                    </h3>

                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Paciente</dt>
                            <dd>{{ donation.patient_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Ubicación</dt>
                            <dd>{{ donation.location }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Contacto</dt>
                            <dd>{{ donation.contact_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Cédula</dt>
                            <dd>{{ donation.cedula ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Médico / responsable receptor</dt>
                            <dd>{{ donation.receiving_doctor_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Código médico</dt>
                            <dd>{{ donation.receiving_doctor_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Servicio</dt>
                            <dd>{{ donation.receiving_service ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Tipo de vehículo</dt>
                            <dd>{{ donation.vehicle_type ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Nombre de quien entrega</dt>
                            <dd>{{ donation.delivery_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Contacto de quien entrega</dt>
                            <dd>{{ donation.delivery_contact ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">Artículos</h3>
                    <table class="mt-4 w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
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
                                class="border-b last:border-0"
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
                    class="bg-white p-6 shadow-sm sm:rounded-lg"
                >
                    <h3 class="text-sm font-semibold text-gray-700">
                        Enviar por WhatsApp
                    </h3>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <div v-if="deliveryAssigned && donation.delivery_contact">
                            <a
                                v-if="deliveryWhatsAppUrl"
                                :href="deliveryWhatsAppUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-500"
                            >
                                Enviar mensaje al delivery
                            </a>
                            <button
                                v-else
                                type="button"
                                disabled
                                class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm text-gray-500"
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
                                class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-500"
                            >
                                Enviar mensaje a quien recibe
                            </a>
                            <button
                                v-else
                                type="button"
                                disabled
                                class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm text-gray-500"
                            >
                                Falta un número de contacto válido de quien recibe
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="nextStatus" class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Avanzar a "{{ donationStatusLabel(nextStatus) }}"
                    </h3>

                    <form @submit.prevent="advance">
                        <div
                            v-for="field in missingFields"
                            :key="field"
                            class="mt-4"
                        >
                            <InputLabel
                                :for="field"
                                :value="fieldLabels[field] ?? field"
                            />
                            <TextInput
                                :id="field"
                                v-model="statusForm[field]"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="statusForm.errors[field]" />
                        </div>

                        <div
                            v-for="field in optionalFields"
                            :key="field"
                            class="mt-4"
                        >
                            <InputLabel
                                :for="field"
                                :value="fieldLabels[field] ?? field"
                            />
                            <TextInput
                                :id="field"
                                v-model="statusForm[field]"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="statusForm.errors[field]" />
                        </div>

                        <InputError class="mt-2" :message="statusForm.errors.status" />

                        <div class="mt-6">
                            <PrimaryButton
                                :disabled="!canAdvance || statusForm.processing"
                                :class="{ 'opacity-25': !canAdvance || statusForm.processing }"
                            >
                                Avanzar a {{ donationStatusLabel(nextStatus) }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Historial de estados
                    </h3>

                    <ol class="mt-4 space-y-3 border-l border-gray-200 pl-4">
                        <li v-for="log in statusLogs" :key="log.id" class="text-sm">
                            <div class="text-gray-500">{{ formatDate(log.changed_at) }}</div>
                            <div>
                                <span v-if="log.from_status" class="font-mono">{{ donationStatusLabel(log.from_status) }}</span>
                                <span v-else class="italic text-gray-500">inicio</span>
                                →
                                <span class="font-mono">{{ donationStatusLabel(log.to_status) }}</span>
                            </div>
                            <div class="text-gray-500">
                                por {{ log.changed_by?.name ?? '—' }}
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
