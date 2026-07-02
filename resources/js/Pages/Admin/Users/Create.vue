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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nuevo usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div
                    class="bg-white p-6 shadow-sm sm:rounded-lg"
                >
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Nombre" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="email" value="Correo electrónico" />
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Contraseña" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="rol" value="Rol" />
                            <select
                                id="rol"
                                v-model="form.rol"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="admin">Admin</option>
                                <option value="medico">Médico</option>
                                <option value="odontologo">Odontólogo</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.rol" />
                        </div>

                        <div v-if="showMedicoFields" class="mt-4">
                            <InputLabel for="codigo_medico" value="Código médico" />
                            <TextInput
                                id="codigo_medico"
                                v-model="form.codigo_medico"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.codigo_medico"
                            />
                        </div>

                        <div v-if="showMedicoFields" class="mt-4">
                            <InputLabel for="servicio" value="Servicio" />
                            <TextInput
                                id="servicio"
                                v-model="form.servicio"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.servicio" />
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Crear usuario
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
