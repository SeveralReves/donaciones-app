<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { userRoleLabel } from '@/utils/labels';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
});

// No cambia durante la vida del componente, no hace falta que sea reactivo.
const currentUserId = usePage().props.auth.user.id;

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

const roleBadgeClass = (rol) => {
    if (rol === 'super_admin') {
        return 'users-index__role--super-admin';
    }

    if (rol === 'admin') {
        return 'users-index__role--admin';
    }

    if (rol === 'voluntario') {
        return 'users-index__role--volunteer';
    }

    return 'users-index__role--medical';
};

const confirmingToggle = ref(null);
const togglingActive = ref(false);

const askToggleActive = (user) => {
    confirmingToggle.value = user;
};

const cancelToggleActive = () => {
    confirmingToggle.value = null;
};

const confirmToggleActive = () => {
    togglingActive.value = true;

    router.patch(
        route('admin.users.toggle-active', confirmingToggle.value.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                togglingActive.value = false;
                confirmingToggle.value = null;
            },
        },
    );
};
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
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            :class="{ 'users-index__row--inactive': !user.active }"
                        >
                            <td>
                                <div class="users-index__name">
                                    <span class="avatar">{{ initial(user.name) }}</span>
                                    <span class="users-index__name-text">{{ user.name }}</span>
                                    <span v-if="!user.active" class="users-index__inactive-tag">Inactivo</span>
                                </div>
                            </td>
                            <td class="users-index__col--md users-index__email">
                                {{ user.email }}
                            </td>
                            <td>
                                <span
                                    class="users-index__role"
                                    :class="roleBadgeClass(user.rol)"
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
                                <span v-if="user.rol === 'super_admin'" class="users-index__protected">
                                    Protegido
                                </span>
                                <div v-else class="users-index__actions-group">
                                    <Link :href="route('admin.users.edit', user.id)" class="users-index__edit">
                                        Editar
                                    </Link>
                                    <button
                                        v-if="user.id !== currentUserId"
                                        type="button"
                                        class="users-index__toggle"
                                        :class="{ 'users-index__toggle--activate': !user.active }"
                                        @click="askToggleActive(user)"
                                    >
                                        {{ user.active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="filteredUsers.length === 0" class="users-index__empty">
                    No hay usuarios que coincidan con la búsqueda.
                </p>
            </div>
        </div>

        <ConfirmModal
            :show="confirmingToggle !== null"
            :title="confirmingToggle?.active ? 'Desactivar usuario' : 'Activar usuario'"
            :message="
                confirmingToggle
                    ? `¿${confirmingToggle.active ? 'Desactivar' : 'Activar'} a ${confirmingToggle.name}?`
                    : ''
            "
            :confirm-label="confirmingToggle?.active ? 'Desactivar' : 'Activar'"
            :danger="confirmingToggle?.active"
            :processing="togglingActive"
            @confirm="confirmToggleActive"
            @cancel="cancelToggleActive"
        />
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

.users-index__row--inactive {
    opacity: 0.55;
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

.users-index__inactive-tag {
    flex: none;
    display: inline-block;
    border-radius: 999px;
    background-color: #f1f4f3;
    padding: 2px 8px;
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #8a969a;
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

.users-index__role--volunteer {
    background-color: #efeef8;
    color: #6d67b0;
}

.users-index__role--super-admin {
    background-color: #14322a;
    color: #fff;
}

.users-index__actions {
    text-align: right;
}

.users-index__actions-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 14px;
}

.users-index__edit {
    font-weight: 600;
    color: #148f5b;
}

.users-index__edit:hover {
    text-decoration: underline;
}

.users-index__toggle {
    border: none;
    background: none;
    padding: 0;
    font-family: inherit;
    font-size: 0.875rem;
    font-weight: 600;
    color: #d9534f;
    cursor: pointer;
}

.users-index__toggle:hover {
    text-decoration: underline;
}

.users-index__toggle--activate {
    color: #148f5b;
}

.users-index__protected {
    font-size: 0.8125rem;
    color: #8a969a;
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
