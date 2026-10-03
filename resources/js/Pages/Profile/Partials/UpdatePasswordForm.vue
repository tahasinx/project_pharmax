<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="profile-section">
        <header class="profile-section-head">
            <h6>Password</h6>
            <p>Use a strong password you do not reuse elsewhere.</p>
        </header>

        <form class="profile-form" @submit.prevent="updatePassword">
            <div class="profile-field">
                <label class="profile-label" for="current_password">Current password</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="form-control"
                    autocomplete="current-password"
                >
                <InputError class="mt-1" :message="form.errors.current_password" />
            </div>

            <div class="profile-grid">
                <div class="profile-field">
                    <label class="profile-label" for="password">New password</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        autocomplete="new-password"
                    >
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>
                <div class="profile-field">
                    <label class="profile-label" for="password_confirmation">Confirm new password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control"
                        autocomplete="new-password"
                    >
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="profile-actions">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Updating…' : 'Update password' }}
                </button>
            </div>
        </form>
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

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.profile-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.9rem;
}

.profile-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.25rem;
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
}

@media (max-width: 575.98px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }

    .profile-actions {
        justify-content: stretch;
    }

    .profile-actions .btn {
        width: 100%;
    }
}
</style>
