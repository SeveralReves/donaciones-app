<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => `modal__panel--${props.maxWidth}`);
</script>

<template>
    <dialog class="modal__dialog" ref="dialog">
        <div class="modal__wrapper" scroll-region>
            <Transition
                enter-active-class="modal__overlay--enter-active"
                enter-from-class="modal__overlay--enter-from"
                enter-to-class="modal__overlay--enter-to"
                leave-active-class="modal__overlay--leave-active"
                leave-from-class="modal__overlay--leave-from"
                leave-to-class="modal__overlay--leave-to"
            >
                <div v-show="show" class="modal__overlay" @click="close">
                    <div class="modal__scrim" />
                </div>
            </Transition>

            <Transition
                enter-active-class="modal__panel--enter-active"
                enter-from-class="modal__panel--enter-from"
                enter-to-class="modal__panel--enter-to"
                leave-active-class="modal__panel--leave-active"
                leave-from-class="modal__panel--leave-from"
                leave-to-class="modal__panel--leave-to"
            >
                <div v-show="show" class="modal__panel" :class="maxWidthClass">
                    <slot v-if="showSlot" />
                </div>
            </Transition>
        </div>
    </dialog>
</template>
