<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    users: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex justify-end">
                    <Link
                        :href="route('admin.users.create')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                    >
                        Nuevo usuario
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Nombre</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Rol</th>
                                <th class="px-6 py-3">Código médico</th>
                                <th class="px-6 py-3">Servicio</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-6 py-3">{{ user.name }}</td>
                                <td class="px-6 py-3">{{ user.email }}</td>
                                <td class="px-6 py-3">{{ user.rol }}</td>
                                <td class="px-6 py-3">
                                    {{ user.codigo_medico ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ user.servicio ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link
                                        :href="
                                            route('admin.users.edit', user.id)
                                        "
                                        class="text-indigo-600 hover:underline"
                                    >
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
