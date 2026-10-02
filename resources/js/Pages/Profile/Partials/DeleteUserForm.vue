<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
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

    form.reset();
};
</script>

<template>
    <section>
        <p class="text-muted">Deleting the account removes the login and cannot be undone.</p>
        <button type="button" class="btn btn-danger" @click="confirmUserDeletion">Delete account</button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-4">
                <h5 class="mb-2">Delete this account?</h5>
                <p class="text-muted">Enter the current password to confirm.</p>
                <label for="delete_password" class="form-label">Password</label>
                <input id="delete_password" ref="passwordInput" v-model="form.password" type="password" class="form-control" placeholder="Password" @keyup.enter="deleteUser">
                <InputError :message="form.errors.password" class="mt-2" />
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light" @click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" :disabled="form.processing" @click="deleteUser">Delete account</button>
                </div>
            </div>
        </Modal>
    </section>
</template>
