<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="guest">
        <div>
            <Link href="/">
                <ApplicationLogo class="guest__logo" />
            </Link>
        </div>

        <div class="guest__panel">
            <slot />
        </div>
    </div>
</template>
