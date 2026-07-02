<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <div v-if="status" class="alert alert--success login__status">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="stack">
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

            <div>
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div>
                <label class="login__remember">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="login__remember-label">Recuérdame</span>
                </label>
            </div>

            <div class="login__actions">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="link--muted"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton
                    :class="{ 'is-busy': form.processing }"
                    :disabled="form.processing"
                >
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.login__status {
    margin-bottom: 1rem;
}

.login__remember {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.login__remember-label {
    margin-left: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.login__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1rem;
}
</style>
