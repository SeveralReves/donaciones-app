<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    rol: 'medico',
    codigo_medico: '',
    servicio: '',
});

const showMedicoFields = computed(() =>
    ['medico', 'odontologo'].includes(form.rol),
);

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="Nuevo usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-header__title">Nuevo usuario</h2>
        </template>

        <div class="page page--form page--roomy">
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
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput id="password" type="password" v-model="form.password" required />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="rol" value="Rol" />
                        <select id="rol" v-model="form.rol" class="field__select" required>
                            <option value="admin">Admin</option>
                            <option value="medico">Médico</option>
                            <option value="odontologo">Odontólogo</option>
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

                    <div class="admin-user-form__actions">
                        <PrimaryButton
                            :class="{ 'is-busy': form.processing }"
                            :disabled="form.processing"
                        >
                            Crear usuario
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
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
</style>
