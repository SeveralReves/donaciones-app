<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar contraseña" />

        <div class="auth-hint">
            ¿Olvidaste tu contraseña? No hay problema. Solo dinos tu correo
            electrónico y te enviaremos un enlace para restablecerla y elegir
            una nueva.
        </div>

        <div v-if="status" class="alert alert--success auth-hint">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="auth-form__actions">
                <PrimaryButton
                    :class="{ 'is-busy': form.processing }"
                    :disabled="form.processing"
                >
                    Enviar enlace de recuperación
                </PrimaryButton>
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

.auth-form__actions {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
</style>
