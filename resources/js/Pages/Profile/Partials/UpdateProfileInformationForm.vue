<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
});
const picked = ref('');
const picture = computed(() => picked.value || user.avatar_url || '');
const initials = computed(() => String(user.name || 'A').split(' ').slice(0, 2).map((part) => part[0]).join('').toUpperCase());

function choosePicture(event) {
    const file = event.target.files?.[0];
    form.avatar = file || null;
    picked.value = file ? URL.createObjectURL(file) : '';
}
</script>

<template>
    <section>
        <p class="text-muted">Update your name, email, and profile picture.</p>
        <form class="row g-3" @submit.prevent="form.patch(route('profile.update'), { forceFormData: true })">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3">
                    <img v-if="picture" :src="picture" alt="" class="rounded-circle" width="64" height="64" style="object-fit: cover;">
                    <span v-else class="avatar-title rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">{{ initials }}</span>
                    <div>
                        <label for="avatar" class="form-label mb-1">Profile picture</label>
                        <input id="avatar" type="file" accept="image/*" class="form-control form-control-sm" @change="choosePicture">
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input id="name" v-model="form.name" type="text" class="form-control" required autocomplete="name">
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input id="email" v-model="form.email" type="email" class="form-control" required autocomplete="username">
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="col-12">
                <p class="text-muted mb-1">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button" class="btn btn-link btn-sm p-0 align-baseline">Resend verification email</Link>
                </p>
                <p v-show="status === 'verification-link-sent'" class="text-success mb-0">A new verification link has been sent.</p>
            </div>
            <div class="col-12 d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Save</button>
                <span v-if="form.recentlySuccessful" class="text-success">Saved.</span>
            </div>
        </form>
    </section>
</template>
