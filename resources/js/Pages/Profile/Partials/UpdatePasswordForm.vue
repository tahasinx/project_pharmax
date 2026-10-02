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
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <p class="text-muted">Use a long password that you do not reuse on other sites.</p>
        <form class="row g-3" @submit.prevent="updatePassword">
            <div class="col-md-6">
                <label for="current_password" class="form-label">Current password</label>
                <input id="current_password" ref="currentPasswordInput" v-model="form.current_password" type="password" class="form-control" autocomplete="current-password">
                <InputError class="mt-2" :message="form.errors.current_password" />
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <label for="password" class="form-label">New password</label>
                <input id="password" ref="passwordInput" v-model="form.password" type="password" class="form-control" autocomplete="new-password">
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-control" autocomplete="new-password">
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>
            <div class="col-12 d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Update password</button>
                <span v-if="form.recentlySuccessful" class="text-success">Saved.</span>
            </div>
        </form>
    </section>
</template>
