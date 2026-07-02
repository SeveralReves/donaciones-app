<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="delete-account">
        <header>
            <h2 class="form-section__title">Eliminar cuenta</h2>

            <p class="form-section__description">
                Una vez eliminada tu cuenta, todos sus recursos y datos se
                borrarán permanentemente. Antes de eliminar tu cuenta,
                descarga cualquier dato o información que quieras conservar.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Eliminar cuenta</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="modal__content">
                <h2 class="form-section__title">
                    ¿Estás seguro de que quieres eliminar tu cuenta?
                </h2>

                <p class="form-section__description">
                    Una vez eliminada tu cuenta, todos sus recursos y datos se
                    borrarán permanentemente. Ingresa tu contraseña para
                    confirmar que quieres eliminar tu cuenta de forma
                    permanente.
                </p>

                <div class="delete-account__field">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="field__input--three-quarters"
                        placeholder="Contraseña"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="delete-account__actions">
                    <SecondaryButton @click="closeModal">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'is-busy': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Eliminar cuenta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style scoped>
.delete-account {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.delete-account__field {
    margin-top: 1.5rem;
}

.delete-account__actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>
