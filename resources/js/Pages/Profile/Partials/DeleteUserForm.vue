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
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="profile-section">
        <header class="profile-section-head">
            <h6>Account</h6>
            <p>Permanent actions for this login.</p>
        </header>

        <div class="danger-zone">
            <div class="danger-copy">
                <h6>Delete account</h6>
                <p>Removes this login permanently. This cannot be undone.</p>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm" @click="confirmUserDeletion">
                Delete account
            </button>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-4">
                <h5 class="mb-2">Delete this account?</h5>
                <p class="text-muted mb-3">Enter your current password to confirm.</p>
                <label for="delete_password" class="form-label">Password</label>
                <input
                    id="delete_password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="form-control"
                    placeholder="Current password"
                    @keyup.enter="deleteUser"
                >
                <InputError :message="form.errors.password" class="mt-2" />
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light" @click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" :disabled="form.processing" @click="deleteUser">
                        {{ form.processing ? 'Deleting…' : 'Delete account' }}
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style scoped>
.profile-section-head {
    margin-bottom: 1.1rem;
}

.profile-section-head h6 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
}

.profile-section-head p {
    margin: 0.25rem 0 0;
    font-size: 0.82rem;
    color: var(--shell-panel-muted, #74788d);
}

.danger-zone {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border: 1px solid rgba(244, 106, 106, 0.28);
    border-radius: 0.65rem;
    background: rgba(244, 106, 106, 0.05);
}

.danger-copy h6 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: #c0392b;
}

.danger-copy p {
    margin: 0.2rem 0 0;
    font-size: 0.8rem;
    color: var(--shell-panel-muted, #74788d);
}

@media (max-width: 575.98px) {
    .danger-zone {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
