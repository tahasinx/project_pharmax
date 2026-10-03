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
const fileInput = ref(null);

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
});
const picked = ref('');
const picture = computed(() => picked.value || user.avatar_url || '');
const initials = computed(() => String(user.name || 'A')
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase());
const fileLabel = computed(() => (form.avatar?.name ? form.avatar.name : 'JPG, PNG up to 2 MB'));

function choosePicture(event) {
    const file = event.target.files?.[0];
    form.avatar = file || null;
    picked.value = file ? URL.createObjectURL(file) : '';
}

function submit() {
    // Multipart PATCH bodies are not parsed by PHP; POST + method spoof keeps name/email/avatar intact.
    form.transform((data) => ({
        ...data,
        _method: 'patch',
    })).post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            picked.value = '';
            form.avatar = null;
            form.clearErrors();
            if (fileInput.value) fileInput.value.value = '';
        },
        onFinish: () => form.transform((data) => data),
    });
}
</script>

<template>
    <section class="profile-section">
        <header class="profile-section-head">
            <h6>Personal details</h6>
            <p>Update your name, email, and profile picture.</p>
        </header>

        <form class="profile-form" @submit.prevent="submit">
            <div class="profile-photo-row">
                <div class="profile-photo">
                    <img v-if="picture" :src="picture" alt="" class="profile-photo-img">
                    <span v-else class="profile-photo-fallback">{{ initials }}</span>
                </div>
                <div class="profile-photo-meta">
                    <label class="profile-label" for="avatar">Profile picture</label>
                    <div class="profile-file">
                        <button type="button" class="btn btn-sm btn-soft-primary" @click="fileInput?.click()">
                            Choose photo
                        </button>
                        <span class="profile-file-name">{{ fileLabel }}</span>
                        <input
                            id="avatar"
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="d-none"
                            @change="choosePicture"
                        >
                    </div>
                    <InputError class="mt-1" :message="form.errors.avatar" />
                </div>
            </div>

            <div class="profile-grid">
                <div class="profile-field">
                    <label class="profile-label" for="name">Full name</label>
                    <input id="name" v-model="form.name" type="text" class="form-control" required autocomplete="name">
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div class="profile-field">
                    <label class="profile-label" for="email">Email address</label>
                    <input id="email" v-model="form.email" type="email" class="form-control" required autocomplete="username">
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="profile-verify">
                <p class="mb-1">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button" class="btn btn-link btn-sm p-0 align-baseline">
                        Resend verification email
                    </Link>
                </p>
                <p v-show="status === 'verification-link-sent'" class="text-success mb-0">A new verification link has been sent.</p>
            </div>

            <div class="profile-actions">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
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

.profile-photo-row {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.profile-photo {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
}

.profile-photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.profile-photo-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(81, 86, 190, 0.12);
    color: var(--bs-primary, #5156be);
    font-weight: 700;
    font-size: 0.95rem;
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

.profile-file {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.55rem;
}

.profile-file-name {
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.9rem;
}

.profile-verify {
    padding: 0.75rem 0.9rem;
    border-radius: 0.5rem;
    background: rgba(241, 180, 76, 0.12);
    border: 1px solid rgba(241, 180, 76, 0.28);
    font-size: 0.84rem;
    color: #8a6410;
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
