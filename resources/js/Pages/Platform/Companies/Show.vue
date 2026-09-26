<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    subscription: Object,
    schema: Object,
});
const lock = useForm({ password: '' });
const login = useForm({ password: '' });
const remove = useForm({ password: '', confirm_text: '' });
</script>

<template>
    <Head :title="company.name" />
    <Layout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">{{ company.name }}</h1>
            <a :href="`/platform/companies/${company.id}/edit`" class="text-sm underline">Edit</a>
        </div>
        <p class="mt-1 text-sm"><a :href="host" class="underline">{{ host }}</a></p>
        <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
            <div>Status: {{ company.status }} / {{ company.provision_status }}</div>
            <div>Database: {{ company.database_name }}</div>
            <div>Admin email: {{ company.admin_email || '—' }}</div>
            <div>Hostname: {{ company.vhost_status || '—' }} · Certificate: {{ company.ssl_status || '—' }}</div>
            <div>Plan: {{ subscription?.plan?.name || 'None' }}</div>
        </dl>
        <p v-if="company.provision_error" class="mt-3 text-sm text-red-700">{{ company.provision_error }}</p>
        <p v-if="schema" class="mt-3 text-sm">Schema: {{ schema.status }} ({{ (schema.pending || []).length }} pending)</p>
        <div class="mt-6 flex flex-wrap gap-6">
            <form class="space-y-2" @submit.prevent="lock.post(`/platform/companies/${company.id}/lock`)">
                <p class="text-sm font-medium">{{ company.status === 'active' ? 'Lock' : 'Unlock' }}</p>
                <input v-model="lock.password" type="password" class="rounded border px-2 py-1" placeholder="Your password" required>
                <button class="block rounded border px-3 py-1 text-sm">Confirm</button>
            </form>
            <form class="space-y-2" @submit.prevent="login.post(`/platform/companies/${company.id}/login-as`)">
                <p class="text-sm font-medium">Open as pharmacy admin</p>
                <input v-model="login.password" type="password" class="rounded border px-2 py-1" placeholder="Your password" required>
                <button class="block rounded border px-3 py-1 text-sm">Sign in there</button>
            </form>
            <form method="post" :action="`/platform/companies/${company.id}/provision`">
                <input type="hidden" name="_token" :value="$page.props.csrf_token || ''">
            </form>
            <button
                class="self-end rounded bg-[#1f3d32] px-3 py-2 text-sm text-white"
                @click="$inertia.post(`/platform/companies/${company.id}/provision`)"
            >
                Provision again
            </button>
        </div>
        <form class="mt-8 max-w-sm space-y-2" @submit.prevent="remove.delete(`/platform/companies/${company.id}`)">
            <p class="text-sm font-medium">Delete pharmacy, database, and hostname</p>
            <input v-model="remove.confirm_text" class="w-full rounded border px-2 py-1" placeholder="Type DELETE" required>
            <input v-model="remove.password" type="password" class="w-full rounded border px-2 py-1" placeholder="Your password" required>
            <button class="rounded border border-red-700 px-3 py-1 text-sm text-red-800">Delete</button>
        </form>
    </Layout>
</template>
