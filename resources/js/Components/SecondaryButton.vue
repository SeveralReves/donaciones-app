<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="btn-breeze btn-breeze--secondary">
        <slot />
    </button>
</template>
