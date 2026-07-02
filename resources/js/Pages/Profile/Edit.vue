<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-header__title">Perfil</h2>
        </template>

        <div class="page page--roomy profile-edit">
            <div class="panel panel--roomy">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="panel panel--roomy">
                <UpdatePasswordForm />
            </div>

            <div class="panel panel--roomy">
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-edit {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
</style>
