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
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <div class="page users-index">
            <div class="users-index__header">
                <div>
                    <h1 class="users-index__title">Usuarios</h1>
                    <p class="users-index__subtitle">Administradores y personal médico</p>
                </div>
                <Link :href="route('admin.users.create')" class="btn btn--primary">
                    <span class="users-index__cta-icon">+</span> Nuevo usuario
                </Link>
            </div>

            <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre o correo…"
                class="form-field__input users-index__search"
            />

            <div class="users-index__table-wrap">
                <table class="users-index__table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="users-index__col--md">Correo</th>
                            <th>Rol</th>
                            <th class="users-index__col--md">Cód. médico</th>
                            <th class="users-index__col--md">Servicio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td>
                                <div class="users-index__name">
                                    <span class="avatar">{{ initial(user.name) }}</span>
                                    <span class="users-index__name-text">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="users-index__col--md users-index__email">
                                {{ user.email }}
                            </td>
                            <td>
                                <span
                                    class="users-index__role"
                                    :class="user.rol === 'admin' ? 'users-index__role--admin' : 'users-index__role--medical'"
                                >
                                    {{ userRoleLabel(user.rol) }}
                                </span>
                            </td>
                            <td class="users-index__col--md">
                                {{ user.codigo_medico ?? '—' }}
                            </td>
                            <td class="users-index__col--md">
                                {{ user.servicio ?? '—' }}
                            </td>
                            <td class="users-index__actions">
                                <Link :href="route('admin.users.edit', user.id)" class="users-index__edit">
                                    Editar
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="filteredUsers.length === 0" class="users-index__empty">
                    No hay usuarios que coincidan con la búsqueda.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.users-index__header {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.users-index__title {
    margin-bottom: 2px;
}

.users-index__subtitle {
    font-size: 0.875rem;
    font-weight: 400;
    color: #7a8b84;
}

.users-index__cta-icon {
    font-size: 17px;
    line-height: 1;
}

.users-index__search {
    margin-bottom: 1rem;
    max-width: 360px;
}

.users-index__table-wrap {
    overflow-x: auto;
    border-radius: 1rem;
    border: 1px solid #e6ede9;
    background-color: #fff;
}

.users-index__table {
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    border-collapse: collapse;
}

.users-index__table thead {
    border-bottom: 1px solid #eef2f1;
    background-color: #f6f9f8;
}

.users-index__table thead tr {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6a787d;
}

.users-index__table th,
.users-index__table td {
    padding: 14px 12px;
}

.users-index__table th:first-child,
.users-index__table td:first-child {
    padding-inline: 1.25rem;
}

.users-index__table th:last-child,
.users-index__table td:last-child {
    padding-inline: 1.25rem;
}

.users-index__table tbody tr {
    border-bottom: 1px solid #f1f4f3;
}

.users-index__table tbody tr:last-child {
    border-bottom: none;
}

.users-index__col--md {
    display: none;
}

.users-index__name {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 11px;
}

.users-index__name-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
}

.users-index__email {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #6a787d;
}

.users-index__role {
    display: inline-block;
    white-space: nowrap;
    border-radius: 0.5rem;
    padding: 5px 11px;
    font-size: 0.75rem;
    font-weight: 600;
}

.users-index__role--admin {
    background-color: #f0f4f2;
    color: #6a787d;
}

.users-index__role--medical {
    background-color: #e7f3ec;
    color: #148f5b;
}

.users-index__actions {
    text-align: right;
}

.users-index__edit {
    font-weight: 600;
    color: #148f5b;
}

.users-index__edit:hover {
    text-decoration: underline;
}

.users-index__empty {
    padding: 44px;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: #8a969a;
}

@media (min-width: 768px) {
    .users-index__col--md {
        display: table-cell;
    }
}
</style>
