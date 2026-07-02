<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const open = ref(false);
</script>

<template>
    <div class="dropdown">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="open" class="dropdown__overlay" @click="open = false"></div>

        <Transition
            enter-active-class="dropdown__panel--enter-active"
            enter-from-class="dropdown__panel--enter-from"
            enter-to-class="dropdown__panel--enter-to"
            leave-active-class="dropdown__panel--leave-active"
            leave-from-class="dropdown__panel--leave-from"
            leave-to-class="dropdown__panel--leave-to"
        >
            <div
                v-show="open"
                class="dropdown__panel"
                :class="align === 'left' ? 'dropdown__panel--left' : 'dropdown__panel--right'"
                style="display: none"
                @click="open = false"
            >
                <div class="dropdown__content">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
