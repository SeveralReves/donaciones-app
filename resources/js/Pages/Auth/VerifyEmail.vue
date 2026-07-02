<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificación de correo" />

        <div class="auth-hint">
            ¡Gracias por registrarte! Antes de empezar, ¿puedes verificar tu
            correo electrónico haciendo clic en el enlace que te acabamos de
            enviar? Si no recibiste el correo, con gusto te enviamos otro.
        </div>

        <div v-if="verificationLinkSent" class="alert alert--success auth-hint">
            Se ha enviado un nuevo enlace de verificación al correo
            electrónico que registraste.
        </div>

        <form @submit.prevent="submit">
            <div class="verify-email__actions">
                <PrimaryButton
                    :class="{ 'is-busy': form.processing }"
                    :disabled="form.processing"
                >
                    Reenviar correo de verificación
                </PrimaryButton>

                <Link :href="route('logout')" method="post" as="button" class="link--muted">
                    Cerrar sesión
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-hint {
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.verify-email__actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
