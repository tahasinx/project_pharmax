<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    subscription: Object,
    schema: Object,
    files: Array,
});
const lock = useForm({ password: '' });
const login = useForm({ password: '' });
const remove = useForm({ password: '', confirm_text: '' });
const upgrade = useForm({ password: '', company_id: props.company.id });
</script>

<template>
    <Head :title="company.name" />
    <Layout>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1>{{ company.name }}</h1>
                <p class="mt-1 text-sm text-[#71717a]">{{ company.status }} · {{ company.provision_status }}</p>
            </div>
            <div class="flex gap-3 text-sm">
                <Link href="/platform/companies" class="text-[#71717a]">All pharmacies</Link>
                <Link :href="`/platform/companies/${company.id}/edit`" class="font-medium text-[#17342b]">Edit</Link>
            </div>
        </div>

        <div class="grid gap-3 lg:grid-cols-3">
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5 lg:col-span-2">
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="text-[#71717a]">Slug</dt><dd class="font-mono">{{ company.slug }}</dd></div>
                    <div><dt class="text-[#71717a]">Database</dt><dd class="font-mono">{{ company.database_name }}</dd></div>
                    <div><dt class="text-[#71717a]">Email</dt><dd>{{ company.email || '—' }}</dd></div>
                    <div><dt class="text-[#71717a]">Admin login</dt><dd>{{ company.admin_email || '—' }}</dd></div>
                    <div><dt class="text-[#71717a]">Phone</dt><dd>{{ company.phone || '—' }}</dd></div>
                    <div><dt class="text-[#71717a]">Hostname</dt><dd>{{ company.vhost_status || '—' }} · certificate {{ company.ssl_status || '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[#71717a]">Address</dt><dd>{{ company.address || '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[#71717a]">URL</dt><dd><a :href="host" class="text-[#17342b]">{{ host }}</a></dd></div>
                </dl>
                <p v-if="company.provision_error" class="mt-3 text-sm text-red-700">{{ company.provision_error }}</p>
            </section>
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5">
                <h2 class="text-sm font-semibold">Subscription</h2>
                <p class="mt-2 text-sm">Plan: {{ subscription?.plan?.name || 'None' }}</p>
                <p class="text-sm">Status: {{ subscription?.status || '—' }}</p>
                <p class="text-sm">Ends: {{ subscription?.ends_on || '—' }}</p>
                <Link :href="`/platform/subscriptions?company=${company.id}`" class="mt-3 inline-block text-sm font-medium text-[#17342b]">Manage subscription</Link>
            </section>
        </div>

        <div class="mt-3 grid gap-3 lg:grid-cols-2">
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5">
                <h2 class="text-sm font-semibold">{{ company.status === 'active' ? 'Lock pharmacy' : 'Unlock pharmacy' }}</h2>
                <p class="mt-1 text-sm text-[#71717a]">{{ company.status === 'active' ? 'Locking blocks sign-in on this host.' : 'Unlocking allows sign-in again.' }}</p>
                <form class="mt-3 flex flex-wrap items-end gap-2" @submit.prevent="lock.post(`/platform/companies/${company.id}/lock`)">
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">Your password</span><input v-model="lock.password" type="password" required></label>
                    <button class="rounded-md border px-3 py-2 text-sm">Confirm</button>
                </form>
                <p v-if="lock.errors.password" class="mt-2 text-sm text-red-700">{{ lock.errors.password }}</p>
            </section>
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5">
                <h2 class="text-sm font-semibold">Open as pharmacy admin</h2>
                <form class="mt-3 flex flex-wrap items-end gap-2" @submit.prevent="login.post(`/platform/companies/${company.id}/login-as`)">
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">Your password</span><input v-model="login.password" type="password" required></label>
                    <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm text-white" :disabled="company.status !== 'active'">Sign in there</button>
                </form>
                <p v-if="login.errors.password" class="mt-2 text-sm text-red-700">{{ login.errors.password }}</p>
            </section>
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5">
                <h2 class="text-sm font-semibold">Schema</h2>
                <p class="mt-1 text-sm">{{ schema?.status || 'Unknown' }} · {{ (schema?.pending || []).length }} pending</p>
                <form class="mt-3 flex flex-wrap items-end gap-2" @submit.prevent="upgrade.post('/platform/schema')">
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">Your password</span><input v-model="upgrade.password" type="password" required></label>
                    <button class="rounded-md border px-3 py-2 text-sm">Migrate this pharmacy</button>
                </form>
                <p v-if="upgrade.errors.password" class="mt-2 text-sm text-red-700">{{ upgrade.errors.password }}</p>
            </section>
            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <div class="flex items-center justify-between border-b border-[#f4f4f5] px-5 py-4">
                    <h2 class="text-sm font-semibold">Backups</h2>
                    <button class="text-sm font-medium text-[#17342b]" @click="$inertia.post(`/platform/backups/${company.id}`)">Backup now</button>
                </div>
                <ul class="divide-y divide-[#f4f4f5] text-sm">
                    <li v-if="!files.length" class="px-5 py-4 text-[#71717a]">No dumps yet.</li>
                    <li v-for="file in files" :key="file.name" class="flex items-center justify-between gap-3 px-5 py-3">
                        <span class="font-mono text-[13px]">{{ file.name }}</span>
                        <Link :href="`/platform/backups/${company.id}/${file.name}`" class="font-medium text-[#17342b]">Download</Link>
                    </li>
                </ul>
            </section>
        </div>

        <form class="mt-3 max-w-md space-y-2 rounded-xl border border-[#e4e4e7] bg-white p-5" @submit.prevent="remove.delete(`/platform/companies/${company.id}`)">
            <h2 class="text-sm font-semibold">Delete pharmacy, database, and hostname</h2>
            <input v-model="remove.confirm_text" class="w-full" placeholder="Type DELETE" required>
            <input v-model="remove.password" type="password" class="w-full" placeholder="Your password" required>
            <button class="rounded-md border border-red-300 px-3 py-2 text-sm text-[#b91c1c]">Delete</button>
            <p v-if="remove.errors.password" class="text-sm text-red-700">{{ remove.errors.password }}</p>
        </form>
    </Layout>
</template>
