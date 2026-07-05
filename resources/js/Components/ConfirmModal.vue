<script setup>
import Modal from '@/Components/Modal.vue';
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirmar',
    },
    cancelLabel: {
        type: String,
        default: 'Cancelar',
    },
    // Resalta el botón de confirmar como una acción sensible (rojo) en vez
    // del color primario de marca.
    danger: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    // 'brand' = .btn (verde, Panel/Donaciones/Inventario/Usuarios listado).
    // 'breeze' = .btn-breeze (heredado de Breeze, usado en Perfil y en los
    // formularios de crear/editar usuario). Ver CLAUDE.md: no mezclar ambas
    // familias en una misma pantalla.
    variant: {
        type: String,
        default: 'brand',
        validator: (value) => ['brand', 'breeze'].includes(value),
    },
});

const emit = defineEmits(['confirm', 'cancel']);

const cancelClass = computed(() =>
    props.variant === 'breeze' ? 'btn-breeze btn-breeze--secondary' : 'btn btn--secondary',
);

const confirmClass = computed(() => {
    if (props.variant === 'breeze') {
        return props.danger ? 'btn-breeze btn-breeze--danger' : 'btn-breeze btn-breeze--primary';
    }

    return props.danger ? 'btn btn--danger' : 'btn btn--primary';
});
</script>

<template>
    <Modal :show="show" @close="emit('cancel')">
        <div class="modal__content">
            <h2 class="form-section__title">{{ title }}</h2>
            <p v-if="message" class="form-section__description">{{ message }}</p>

            <div class="confirm-modal__actions">
                <button type="button" :class="cancelClass" @click="emit('cancel')">
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    :class="[confirmClass, { 'is-busy': processing }]"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.confirm-modal__actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>
