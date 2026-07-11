<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    rol: props.user.rol,
    codigo_medico: props.user.codigo_medico ?? '',
    servicio: props.user.servicio ?? '',
    can_access_children_module: props.user.can_access_children_module ?? false,
});

const showMedicoFields = computed(() =>
    ['medico', 'odontologo'].includes(form.rol),
);

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};

const showResetConfirm = ref(false);
const resettingPassword = ref(false);

const askResetPassword = () => {
    showResetConfirm.value = true;
};

const cancelResetPassword = () => {
    showResetConfirm.value = false;
};

const confirmResetPassword = () => {
    resettingPassword.value = true;

    router.post(
        route('admin.users.reset-password', props.user.id),
        {},
        {
            onFinish: () => {
                resettingPassword.value = false;
                showResetConfirm.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Editar usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-header__title">Editar usuario</h2>
        </template>

        <div class="page page--form page--roomy">
            <div
                v-if="$page.props.flash.generatedPassword"
                class="alert alert--warning admin-user-edit__flash"
            >
                Nueva contraseña generada (guárdala, no se volverá a mostrar):
                <strong class="admin-user-edit__flash-password">{{
                    $page.props.flash.generatedPassword
                }}</strong>
            </div>

            <div class="panel">
                <form @submit.prevent="submit" class="admin-user-form">
                    <div>
                        <InputLabel for="name" value="Nombre" />
                        <TextInput id="name" v-model="form.name" required autofocus />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo electrónico" />
                        <TextInput id="email" type="email" v-model="form.email" required />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="rol" value="Rol" />
                        <select id="rol" v-model="form.rol" class="field__select" required>
                            <option value="admin">Admin</option>
                            <option value="medico">Médico</option>
                            <option value="odontologo">Odontólogo</option>
                            <option value="voluntario">Voluntario</option>
                        </select>
                        <InputError :message="form.errors.rol" />
                    </div>

                    <div v-if="showMedicoFields">
                        <InputLabel for="codigo_medico" value="Código médico" />
                        <TextInput id="codigo_medico" v-model="form.codigo_medico" />
                        <InputError :message="form.errors.codigo_medico" />
                    </div>

                    <div v-if="showMedicoFields">
                        <InputLabel for="servicio" value="Servicio" />
                        <TextInput id="servicio" v-model="form.servicio" />
                        <InputError :message="form.errors.servicio" />
                    </div>

                    <div class="admin-user-form__checkbox">
                        <label class="admin-user-form__checkbox-label">
                            <Checkbox v-model:checked="form.can_access_children_module" />
                            Acceso al módulo de niños
                        </label>
                        <InputError :message="form.errors.can_access_children_module" />
                    </div>

                    <div class="admin-user-form__actions">
                        <PrimaryButton
                            :class="{ 'is-busy': form.processing }"
                            :disabled="form.processing"
                        >
                            Guardar cambios
                        </PrimaryButton>
                    </div>
                </form>

                <div class="admin-user-edit__danger">
                    <DangerButton type="button" @click="askResetPassword">
                        Resetear contraseña
                    </DangerButton>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showResetConfirm"
            title="Resetear contraseña"
            message="¿Generar una nueva contraseña para este usuario? La actual dejará de funcionar."
            confirm-label="Generar nueva contraseña"
            danger
            variant="breeze"
            :processing="resettingPassword"
            @confirm="confirmResetPassword"
            @cancel="cancelResetPassword"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.admin-user-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.admin-user-form__actions {
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.admin-user-form__checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.admin-user-edit__flash {
    margin-bottom: 1rem;
}

.admin-user-edit__flash-password {
    font-family: monospace;
}

.admin-user-edit__danger {
    margin-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1.5rem;
}
</style>
