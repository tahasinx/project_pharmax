<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformLayout from '@/Pages/Platform/Layout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const page = usePage();
const layout = computed(() => (page.props.platform?.central ? PlatformLayout : AuthenticatedLayout));
const user = computed(() => page.props.auth.user);
const tab = ref('profile');
const initials = computed(() => String(user.value?.name || 'A')
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase());

const tabs = [
    { id: 'profile', label: 'Profile', icon: 'bi-person' },
    { id: 'password', label: 'Password', icon: 'bi-shield-lock' },
    { id: 'account', label: 'Account', icon: 'bi-gear' },
];
</script>

<template>
    <Head title="Profile" />

    <component :is="layout">
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Profile</h4>
                <p class="text-muted mb-0 font-size-13">Manage your account details and security.</p>
            </div>
        </template>

        <div class="profile-page">
            <section class="profile-shell">
                <header class="profile-identity">
                    <div class="profile-avatar">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="profile-avatar-img">
                        <span v-else class="profile-avatar-fallback">{{ initials }}</span>
                    </div>
                    <div class="profile-identity-copy min-w-0">
                        <h5 class="profile-name">{{ user?.name }}</h5>
                        <p class="profile-email">{{ user?.email }}</p>
                    </div>
                </header>

                <nav class="profile-tabs" aria-label="Profile sections">
                    <button
                        v-for="item in tabs"
                        :key="item.id"
                        type="button"
                        class="profile-tab"
                        :class="{ active: tab === item.id }"
                        @click="tab = item.id"
                    >
                        <i :class="['bi', item.icon]" />
                        <span>{{ item.label }}</span>
                    </button>
                </nav>

                <div class="profile-body">
                    <UpdateProfileInformationForm
                        v-if="tab === 'profile'"
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                    <UpdatePasswordForm v-else-if="tab === 'password'" />
                    <DeleteUserForm v-else />
                </div>
            </section>
        </div>
    </component>
</template>

<style scoped>
.profile-page {
    max-width: 760px;
}

.profile-shell {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.75rem;
    background: var(--shell-panel-surface, #fff);
    overflow: hidden;
}

.profile-identity {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.35rem;
    background:
        linear-gradient(180deg, rgba(81, 86, 190, 0.06), transparent 70%),
        var(--shell-panel-bg, #f8f9fc);
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.profile-avatar {
    flex-shrink: 0;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 0.35rem 0.9rem rgba(16, 24, 40, 0.08);
}

.profile-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.profile-avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-primary, #5156be);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.profile-name {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-email {
    margin: 0.15rem 0 0;
    font-size: 0.84rem;
    color: var(--shell-panel-muted, #74788d);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-tabs {
    display: flex;
    gap: 0.35rem;
    padding: 0.7rem 1rem 0;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-surface, #fff);
}

.profile-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.84rem;
    font-weight: 600;
    padding: 0.65rem 0.85rem;
    margin-bottom: -1px;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.profile-tab i {
    font-size: 0.95rem;
}

.profile-tab:hover {
    color: var(--shell-panel-text, #343747);
}

.profile-tab.active {
    color: var(--bs-primary, #5156be);
    border-bottom-color: var(--bs-primary, #5156be);
}

.profile-body {
    padding: 1.25rem 1.35rem 1.4rem;
}

@media (max-width: 575.98px) {
    .profile-identity {
        padding: 1rem;
    }

    .profile-tabs {
        padding-left: 0.65rem;
        padding-right: 0.65rem;
        overflow-x: auto;
    }

    .profile-tab {
        white-space: nowrap;
        padding-inline: 0.7rem;
    }

    .profile-body {
        padding: 1rem;
    }
}
</style>
