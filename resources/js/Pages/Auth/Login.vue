<script setup>
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
    <Head title="Iniciar sesión" />

    <div class="login-page">
        <div class="login-page__card">
            <div class="login-page__brand">
                <span class="login-page__brand-mark">
                    <span class="login-page__brand-icon">
                        <span class="login-page__brand-bar login-page__brand-bar--h"></span>
                        <span class="login-page__brand-bar login-page__brand-bar--v"></span>
                    </span>
                </span>
                <span class="login-page__brand-name">Control de Donaciones</span>
            </div>

            <h1 class="login-page__title">Iniciar sesión</h1>
            <p class="login-page__subtitle">Ingresa tus credenciales para continuar</p>

            <div v-if="status" class="alert alert--success login-page__status">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="stack">
                <div class="form-field">
                    <label for="email" class="form-field__label">Correo electrónico</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-field__input"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <p v-if="form.errors.email" class="form-field__error">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="form-field">
                    <label for="password" class="form-field__label">Contraseña</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-field__input"
                        required
                        autocomplete="current-password"
                    />
                    <p v-if="form.errors.password" class="form-field__error">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="login-page__row">
                    <label class="login-page__remember">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            name="remember"
                            class="login-page__checkbox"
                        />
                        <span>Recuérdame</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="login-page__forgot"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <button
                    type="submit"
                    class="btn btn--primary btn--full"
                    :class="{ 'is-busy': form.processing }"
                    :disabled="form.processing"
                >
                    Iniciar sesión
                </button>
            </form>
        </div>

        <p class="login-page__footer">
            Desarrollado por
            <a
                href="https://svrdatatech.lat/"
                target="_blank"
                rel="noopener noreferrer"
                class="login-page__footer-link"
            >
                SVRDataTech
            </a>
        </p>
    </div>
</template>

<style scoped>
.login-page {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1.5rem;
}

.login-page__card {
    width: 100%;
    max-width: 400px;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #fff;
    padding: 2rem;
    box-shadow: 0 20px 40px -24px rgba(15, 81, 50, 0.25);
}

.login-page__brand {
    margin-bottom: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.625rem;
    text-align: center;
}

.login-page__brand-mark {
    display: flex;
    height: 44px;
    width: 44px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background-color: #3ec37a;
}

.login-page__brand-icon {
    position: relative;
    display: block;
    height: 18px;
    width: 18px;
}

.login-page__brand-bar {
    position: absolute;
    border-radius: 2px;
    background-color: #0f5132;
}

.login-page__brand-bar--h {
    left: 0;
    top: 7px;
    height: 4px;
    width: 18px;
}

.login-page__brand-bar--v {
    left: 7px;
    top: 0;
    height: 18px;
    width: 4px;
}

.login-page__brand-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: #14322a;
}

.login-page__title {
    margin-bottom: 0.25rem;
    text-align: center;
    font-size: 1.375rem;
}

.login-page__subtitle {
    margin-bottom: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: #7a8b84;
}

.login-page__status {
    margin-bottom: 1rem;
}

.login-page__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.login-page__remember {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #5a686d;
}

.login-page__checkbox {
    height: 1rem;
    width: 1rem;
    border-radius: 0.25rem;
    accent-color: #148f5b;
}

.login-page__forgot {
    font-size: 0.875rem;
    font-weight: 600;
    color: #148f5b;
}

.login-page__forgot:hover {
    text-decoration: underline;
}

.login-page__footer {
    font-size: 0.8125rem;
    color: #7a8b84;
}

.login-page__footer-link {
    font-weight: 600;
    color: #148f5b;
}

.login-page__footer-link:hover {
    text-decoration: underline;
}
</style>
