<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="form-section__title">Información del perfil</h2>

            <p class="form-section__description">
                Actualiza la información de tu perfil y tu correo electrónico.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="form-section__body">
            <div>
                <InputLabel for="name" value="Nombre" />

                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="update-profile__unverified">
                    Tu correo electrónico no está verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="link--muted"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="alert alert--success update-profile__sent">
                    Se ha enviado un nuevo enlace de verificación a tu correo
                    electrónico.
                </div>
            </div>

            <div class="form-actions">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>

                <Transition
                    enter-active-class="form-actions__message--enter-active"
                    enter-from-class="form-actions__message--enter-from"
                    leave-active-class="form-actions__message--leave-active"
                    leave-to-class="form-actions__message--leave-to"
                >
                    <p v-if="form.recentlySuccessful" class="form-actions__message">
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.update-profile__unverified {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #1f2937;
}

.update-profile__sent {
    margin-top: 0.5rem;
}
</style>
