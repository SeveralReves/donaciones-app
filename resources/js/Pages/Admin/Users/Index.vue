<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { userRoleLabel } from '@/utils/labels';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const search = ref('');

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.users;
    }
    return props.users.filter(
        (user) =>
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term),
    );
});

const initial = (name) => name.trim().charAt(0).toUpperCase();

const roleBadgeClass = (rol) =>
    rol === 'admin' ? 'bg-[#f0f4f2] text-[#6a787d]' : 'bg-[#e7f3ec] text-[#148f5b]';
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6">
            <div class="mb-[18px] flex flex-wrap items-center justify-between gap-3.5">
                <div>
                    <h1 class="mb-0.5">Usuarios</h1>
                    <p class="text-sm font-normal text-[#7a8b84]">Administradores y personal médico</p>
                </div>
                <Link :href="route('admin.users.create')" class="btn btn--primary">
                    <span class="text-[17px] leading-none">+</span> Nuevo usuario
                </Link>
            </div>

            <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre o correo…"
                class="form-field__input mb-4 max-w-[360px]"
            />

            <div class="overflow-x-auto rounded-2xl border border-[#e6ede9] bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-[#eef2f1] bg-[#f6f9f8]">
                        <tr class="text-xs font-semibold uppercase tracking-wide text-[#6a787d]">
                            <th class="px-5 py-3.5 sm:px-[22px]">Nombre</th>
                            <th class="hidden px-3 py-3.5 md:table-cell">Correo</th>
                            <th class="px-3 py-3.5">Rol</th>
                            <th class="hidden px-3 py-3.5 md:table-cell">Cód. médico</th>
                            <th class="hidden px-3 py-3.5 md:table-cell">Servicio</th>
                            <th class="px-5 py-3.5 sm:px-[22px]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="border-b border-[#f1f4f3] last:border-0"
                        >
                            <td class="px-5 py-3.5 sm:px-[22px]">
                                <div class="flex min-w-0 items-center gap-[11px]">
                                    <span class="avatar">{{ initial(user.name) }}</span>
                                    <span class="truncate font-semibold">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="hidden truncate px-3 py-3.5 text-[#6a787d] md:table-cell">
                                {{ user.email }}
                            </td>
                            <td class="px-3 py-3.5">
                                <span
                                    class="inline-block whitespace-nowrap rounded-lg px-[11px] py-[5px] text-xs font-semibold"
                                    :class="roleBadgeClass(user.rol)"
                                >
                                    {{ userRoleLabel(user.rol) }}
                                </span>
                            </td>
                            <td class="hidden px-3 py-3.5 text-[#6a787d] md:table-cell">
                                {{ user.codigo_medico ?? '—' }}
                            </td>
                            <td class="hidden px-3 py-3.5 text-[#6a787d] md:table-cell">
                                {{ user.servicio ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right sm:px-[22px]">
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="font-semibold text-[#148f5b] hover:underline"
                                >
                                    Editar
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="filteredUsers.length === 0" class="p-11 text-center text-sm font-medium text-[#8a969a]">
                    No hay usuarios que coincidan con la búsqueda.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
