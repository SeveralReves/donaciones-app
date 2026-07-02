<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
});

const showMedicoFields = computed(() =>
    ['medico', 'odontologo'].includes(form.rol),
);

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};

const resetPassword = () => {
    if (! confirm('¿Generar una nueva contraseña para este usuario?')) {
        return;
    }

    router.post(route('admin.users.reset-password', props.user.id));
};
</script>

<template>
    <Head title="Editar usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash.generatedPassword"
                    class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800"
                >
                    Nueva contraseña generada (guárdala, no se volverá a
                    mostrar):
                    <strong class="font-mono">{{
                        $page.props.flash.generatedPassword
                    }}</strong>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
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
                                Guardar cambios
                            </PrimaryButton>
                        </div>
                    </form>

                    <div class="mt-6 border-t pt-6">
                        <DangerButton type="button" @click="resetPassword">
                            Resetear contraseña
                        </DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
