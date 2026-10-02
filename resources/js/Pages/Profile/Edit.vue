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
const initials = computed(() => String(user.value?.name || 'A').split(' ').slice(0, 2).map((part) => part[0]).join('').toUpperCase());
</script>

<template>
    <Head title="Profile" />

    <component :is="layout">
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Profile</h4>
        </template>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="rounded-circle mb-3" width="88" height="88" style="object-fit: cover;">
                        <span v-else class="avatar-title rounded-circle bg-primary text-white font-size-20 d-inline-flex align-items-center justify-content-center mb-3" style="width: 88px; height: 88px;">{{ initials }}</span>
                        <h5 class="font-size-16 mb-1">{{ user?.name }}</h5>
                        <p class="text-muted mb-0">{{ user?.email }}</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs-custom card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: tab === 'profile' }" href="#profile-pane" role="tab" @click.prevent="tab = 'profile'">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: tab === 'password' }" href="#password-pane" role="tab" @click.prevent="tab = 'password'">Password</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: tab === 'account' }" href="#account-pane" role="tab" @click.prevent="tab = 'account'">Account</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane" :class="{ active: tab === 'profile' }" role="tabpanel">
                                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                            </div>
                            <div class="tab-pane" :class="{ active: tab === 'password' }" role="tabpanel">
                                <UpdatePasswordForm />
                            </div>
                            <div class="tab-pane" :class="{ active: tab === 'account' }" role="tabpanel">
                                <DeleteUserForm />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
