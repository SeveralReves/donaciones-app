<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { donationTypeLabel } from '@/utils/labels';
import { isAdmin } from '@/utils/permissions';

const props = defineProps({
    stockItems: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Espejo de la Gate 'manage-stock': un voluntario solo puede ver el listado
// y el historial (ya lo permite la Gate 'view-stock' en las rutas), nunca
// crear ni modificar insumos.
const canManageStock = isAdmin(usePage().props.auth.user);

const donationTypes = ['insumos_medicos', 'medicinas', 'higiene', 'alimentos', 'miscelaneos', 'otros'];

const filters = reactive({
    donation_type: props.filters.donation_type ?? '',
});

const applyFilters = () => {
    router.get(route('admin.stock-items.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const adjustingItem = ref(null);

const adjustForm = useForm({
    quantity_change: '',
    reason: '',
});

const openAdjustModal = (item) => {
    adjustingItem.value = item;
    adjustForm.reset();
    adjustForm.clearErrors();
};

const closeAdjustModal = () => {
    adjustingItem.value = null;
};

const submitAdjustment = () => {
    adjustForm.patch(route('admin.stock-items.adjust', adjustingItem.value.id), {
        preserveScroll: true,
        onSuccess: () => closeAdjustModal(),
    });
};

const isBelowThreshold = (item) =>
    item.minimum_threshold !== null && Number(item.quantity_available) < Number(item.minimum_threshold);

const editingThresholdItem = ref(null);

const thresholdForm = useForm({
    minimum_threshold: '',
});

const openThresholdModal = (item) => {
    editingThresholdItem.value = item;
    thresholdForm.reset();
    thresholdForm.clearErrors();
    thresholdForm.minimum_threshold = item.minimum_threshold ?? '';
};

const closeThresholdModal = () => {
    editingThresholdItem.value = null;
};

const submitThreshold = () => {
    thresholdForm.patch(route('admin.stock-items.update-threshold', editingThresholdItem.value.id), {
        preserveScroll: true,
        onSuccess: () => closeThresholdModal(),
    });
};

const deactivatingItem = ref(null);

const deactivateForm = useForm({
    reason: '',
});

const openDeactivateModal = (item) => {
    deactivatingItem.value = item;
    deactivateForm.reset();
    deactivateForm.clearErrors();
};

const closeDeactivateModal = () => {
    deactivatingItem.value = null;
};

const submitDeactivate = () => {
    deactivateForm.patch(route('admin.stock-items.deactivate', deactivatingItem.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeactivateModal(),
    });
};

const reactivatingItem = ref(null);
const reactivating = ref(false);

const askReactivate = (item) => {
    reactivatingItem.value = item;
};

const cancelReactivate = () => {
    reactivatingItem.value = null;
};

const confirmReactivate = () => {
    reactivating.value = true;

    router.patch(
        route('admin.stock-items.reactivate', reactivatingItem.value.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                reactivating.value = false;
                reactivatingItem.value = null;
            },
        },
    );
};

const reactivateMessage = computed(() =>
    reactivatingItem.value
        ? `¿Reactivar "${reactivatingItem.value.name}"? Volverá a ofrecerse en donaciones y necesidades.`
        : '',
);

const deactivationTooltip = (item) => {
    if (! item.deactivated_at) {
        return '';
    }

    const date = new Date(item.deactivated_at).toLocaleString();
    const who = item.deactivated_by?.name ?? '—';

    return `Desactivado por ${who} el ${date}. Motivo: ${item.deactivation_reason ?? '—'}`;
};
</script>

<template>
    <Head title="Inventario" />

    <AuthenticatedLayout>
        <div class="page stock-index">
            <div class="stock-index__header">
                <div>
                    <h1 class="stock-index__title">Inventario</h1>
                    <p class="stock-index__subtitle">Catálogo de insumos y cantidad disponible</p>
                </div>
                <Link
                    v-if="canManageStock"
                    :href="route('admin.stock-items.create')"
                    class="btn btn--primary"
                >
                    <span class="stock-index__cta-icon">+</span> Nuevo insumo
                </Link>
            </div>

            <div class="surface stock-index__filters">
                <div class="form-field stock-index__filter-field">
                    <label class="form-field__label">Tipo de donación</label>
                    <select
                        v-model="filters.donation_type"
                        class="form-field__select"
                        @change="applyFilters"
                    >
                        <option value="">Todos</option>
                        <option v-for="type in donationTypes" :key="type" :value="type">
                            {{ donationTypeLabel(type) }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="stock-index__table-wrap">
                <table class="stock-index__table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Unidad</th>
                            <th class="stock-index__col--md">Tipo</th>
                            <th>Disponible</th>
                            <th class="stock-index__col--md">Umbral mínimo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in stockItems"
                            :key="item.id"
                            :class="{
                                'stock-index__row--low': isBelowThreshold(item),
                                'stock-index__row--inactive': !item.active,
                            }"
                        >
                            <td class="stock-index__name">
                                {{ item.name }}
                                <span
                                    v-if="!item.active"
                                    class="stock-index__inactive-tag"
                                    :title="deactivationTooltip(item)"
                                >
                                    Inactivo
                                </span>
                            </td>
                            <td>{{ item.unit }}</td>
                            <td class="stock-index__col--md">
                                {{ donationTypeLabel(item.donation_type) }}
                            </td>
                            <td
                                class="stock-index__quantity"
                                :class="{ 'stock-index__quantity--low': isBelowThreshold(item) }"
                            >
                                {{ item.quantity_available }}
                                <span v-if="isBelowThreshold(item)" class="stock-index__low-badge">
                                    Bajo el mínimo
                                </span>
                            </td>
                            <td class="stock-index__col--md">
                                <span v-if="item.minimum_threshold !== null">
                                    {{ item.minimum_threshold }} {{ item.unit }}
                                </span>
                                <span v-else class="stock-index__no-threshold">Sin definir</span>
                                <button
                                    v-if="canManageStock"
                                    type="button"
                                    class="stock-index__threshold-edit"
                                    @click="openThresholdModal(item)"
                                >
                                    Editar
                                </button>
                            </td>
                            <td class="stock-index__actions">
                                <button
                                    v-if="canManageStock"
                                    type="button"
                                    class="btn btn--secondary stock-index__adjust-btn"
                                    @click="openAdjustModal(item)"
                                >
                                    Ajustar stock
                                </button>
                                <Link
                                    :href="route('admin.stock-items.adjustments', item.id)"
                                    class="stock-index__history-link"
                                >
                                    Historial
                                </Link>
                                <template v-if="canManageStock">
                                    <button
                                        v-if="item.active"
                                        type="button"
                                        class="stock-index__deactivate-btn"
                                        @click="openDeactivateModal(item)"
                                    >
                                        Desactivar
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        class="stock-index__reactivate-btn"
                                        @click="askReactivate(item)"
                                    >
                                        Reactivar
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="stockItems.length === 0" class="stock-index__empty">
                    No hay insumos en el catálogo que coincidan con el filtro.
                </p>
            </div>
        </div>

        <Modal :show="adjustingItem !== null" @close="closeAdjustModal">
            <div class="modal__content" v-if="adjustingItem">
                <h2 class="form-section__title">Ajustar stock — {{ adjustingItem.name }}</h2>
                <p class="form-section__description">
                    Cantidad actual: {{ adjustingItem.quantity_available }} {{ adjustingItem.unit }}
                </p>

                <form @submit.prevent="submitAdjustment" class="stock-adjust-form">
                    <div class="form-field">
                        <label for="quantity_change" class="form-field__label">
                            Cantidad a agregar o restar
                        </label>
                        <input
                            id="quantity_change"
                            v-model="adjustForm.quantity_change"
                            type="number"
                            step="0.01"
                            placeholder="Ej. 10 o -5"
                            class="form-field__input"
                            required
                        />
                        <p class="stock-adjust-form__hint">
                            Usa un número positivo para agregar stock y uno negativo para restar.
                        </p>
                        <p v-if="adjustForm.errors.quantity_change" class="form-field__error">
                            {{ adjustForm.errors.quantity_change }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="reason" class="form-field__label">Motivo</label>
                        <input
                            id="reason"
                            v-model="adjustForm.reason"
                            type="text"
                            placeholder="Ej. conteo físico, donación recibida, corrección de error"
                            class="form-field__input"
                            required
                        />
                        <p v-if="adjustForm.errors.reason" class="form-field__error">
                            {{ adjustForm.errors.reason }}
                        </p>
                    </div>

                    <div class="stock-adjust-form__actions">
                        <button type="button" class="btn btn--secondary" @click="closeAdjustModal">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :class="{ 'is-busy': adjustForm.processing }"
                            :disabled="adjustForm.processing"
                        >
                            Guardar ajuste
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="editingThresholdItem !== null" @close="closeThresholdModal">
            <div class="modal__content" v-if="editingThresholdItem">
                <h2 class="form-section__title">Umbral mínimo — {{ editingThresholdItem.name }}</h2>
                <p class="form-section__description">
                    Disponible actual: {{ editingThresholdItem.quantity_available }}
                    {{ editingThresholdItem.unit }}
                </p>

                <form @submit.prevent="submitThreshold" class="stock-adjust-form">
                    <div class="form-field">
                        <label for="minimum_threshold" class="form-field__label">
                            Umbral mínimo
                        </label>
                        <input
                            id="minimum_threshold"
                            v-model="thresholdForm.minimum_threshold"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="Sin definir"
                            class="form-field__input"
                        />
                        <p class="stock-adjust-form__hint">
                            Déjalo vacío para dejar de monitorear este insumo, incluso si su
                            stock llega a cero.
                        </p>
                        <p v-if="thresholdForm.errors.minimum_threshold" class="form-field__error">
                            {{ thresholdForm.errors.minimum_threshold }}
                        </p>
                    </div>

                    <div class="stock-adjust-form__actions">
                        <button type="button" class="btn btn--secondary" @click="closeThresholdModal">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn btn--primary"
                            :class="{ 'is-busy': thresholdForm.processing }"
                            :disabled="thresholdForm.processing"
                        >
                            Guardar umbral
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="deactivatingItem !== null" @close="closeDeactivateModal">
            <div class="modal__content" v-if="deactivatingItem">
                <h2 class="form-section__title">Desactivar — {{ deactivatingItem.name }}</h2>
                <p class="form-section__description">
                    Deja de ofrecerse en donaciones nuevas y en la página de necesidades, pero
                    sigue visible en el historial de donaciones que ya lo usaron.
                </p>

                <form @submit.prevent="submitDeactivate" class="stock-adjust-form">
                    <div class="form-field">
                        <label for="deactivation_reason" class="form-field__label">Motivo</label>
                        <input
                            id="deactivation_reason"
                            v-model="deactivateForm.reason"
                            type="text"
                            placeholder="Ej. descontinuado por el proveedor, vencido"
                            class="form-field__input"
                            required
                        />
                        <p v-if="deactivateForm.errors.reason" class="form-field__error">
                            {{ deactivateForm.errors.reason }}
                        </p>
                    </div>

                    <div class="stock-adjust-form__actions">
                        <button type="button" class="btn btn--secondary" @click="closeDeactivateModal">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn btn--danger"
                            :class="{ 'is-busy': deactivateForm.processing }"
                            :disabled="deactivateForm.processing"
                        >
                            Desactivar insumo
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <ConfirmModal
            :show="reactivatingItem !== null"
            title="Reactivar insumo"
            :message="reactivateMessage"
            confirm-label="Reactivar"
            :processing="reactivating"
            @confirm="confirmReactivate"
            @cancel="cancelReactivate"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.stock-index__header {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.stock-index__title {
    margin-bottom: 2px;
}

.stock-index__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.stock-index__cta-icon {
    font-size: 17px;
    line-height: 1;
}

.stock-index__filters {
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
}

.stock-index__filter-field {
    margin-bottom: 0;
    width: auto;
    flex: 1;
    min-width: 200px;
}

.stock-index__table-wrap {
    overflow-x: auto;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #fff;
}

.stock-index__table {
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    border-collapse: collapse;
}

.stock-index__table thead {
    border-bottom: 1px solid #eef2f1;
    background-color: #f6f9f8;
}

.stock-index__table thead tr {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6a787d;
}

.stock-index__table th,
.stock-index__table td {
    padding: 14px 12px;
}

.stock-index__table th:first-child,
.stock-index__table td:first-child {
    padding-inline: 1.25rem;
}

.stock-index__table th:last-child,
.stock-index__table td:last-child {
    padding-inline: 1.25rem;
}

.stock-index__table tbody tr {
    border-bottom: 1px solid #f1f4f3;
}

.stock-index__table tbody tr:last-child {
    border-bottom: none;
}

.stock-index__row--inactive {
    opacity: 0.55;
}

.stock-index__col--md {
    display: none;
}

.stock-index__name {
    font-weight: 600;
}

.stock-index__inactive-tag {
    margin-left: 8px;
    display: inline-block;
    border-radius: 999px;
    background-color: #f1f4f3;
    padding: 2px 8px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
    cursor: help;
}

.stock-index__quantity {
    font-weight: 600;
    color: #148f5b;
}

/* #d97706 = $color-warning (resources/sass/abstracts/_variables.scss) —
   los estilos scoped de un .vue no pueden @use parciales de Sass, así que
   se repite el valor tal cual en vez de la variable. */
.stock-index__row--low {
    background-color: rgba(217, 119, 6, 0.06);
}

.stock-index__quantity--low {
    color: #d97706;
}

.stock-index__low-badge {
    margin-left: 0.5rem;
    display: inline-block;
    white-space: nowrap;
    border-radius: 999px;
    background-color: rgba(217, 119, 6, 0.14);
    padding: 2px 9px;
    font-size: 0.6875rem;
    font-weight: 700;
    color: #d97706;
    vertical-align: middle;
}

.stock-index__no-threshold {
    color: #8a969a;
}

.stock-index__threshold-edit {
    margin-left: 0.5rem;
    border: none;
    background: none;
    padding: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #148f5b;
    cursor: pointer;
}

.stock-index__threshold-edit:hover {
    text-decoration: underline;
}

.stock-index__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    text-align: right;
}

.stock-index__adjust-btn {
    min-height: 36px;
    padding: 6px 14px;
    font-size: 0.8125rem;
}

.stock-index__history-link {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #148f5b;
    white-space: nowrap;
}

.stock-index__history-link:hover {
    text-decoration: underline;
}

.stock-index__deactivate-btn,
.stock-index__reactivate-btn {
    border: none;
    background: none;
    padding: 0;
    font-family: inherit;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
}

.stock-index__deactivate-btn {
    color: #d9534f;
}

.stock-index__reactivate-btn {
    color: #148f5b;
}

.stock-index__deactivate-btn:hover,
.stock-index__reactivate-btn:hover {
    text-decoration: underline;
}

.stock-index__empty {
    padding: 44px;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: #8a969a;
}

.stock-adjust-form {
    margin-top: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stock-adjust-form__hint {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: #8a969a;
}

.stock-adjust-form__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

@media (min-width: 768px) {
    .stock-index__col--md {
        display: table-cell;
    }
}
</style>
