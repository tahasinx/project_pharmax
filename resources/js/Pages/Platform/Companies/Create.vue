<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '../Layout.vue';

const props = defineProps({
    prefix: String,
    baseDomain: String,
    hostEnabled: Boolean,
});
const form = useForm({
    name: '',
    slug: '',
    database_name: '',
    email: '',
    phone: '',
    address: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
});
const check = ref('');
const checkOk = ref(null);

async function validateDatabase() {
    check.value = 'Checking…';
    checkOk.value = null;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch('/platform/companies/validate-database', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ slug: form.slug, database_name: form.database_name }),
    });
    const data = await response.json();
    checkOk.value = !!data.ok;
    check.value = data.message || 'Could not check that database.';
    if (data.ok && data.database_name && !form.database_name) {
        form.database_name = data.database_name;
    }
}
</script>

<template>
    <Head title="New pharmacy" />
    <Layout>
        <form class="mx-auto max-w-3xl space-y-4" @submit.prevent="form.post('/platform/companies')">
            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>New pharmacy</h1>
                    <p class="mt-1 text-sm text-[#71717a]">
                        Host <span class="font-medium text-[#18181b]">{{ form.slug || 'name' }}.{{ baseDomain }}</span>.
                        This creates the tenant database, pharmacy admin, menus, and
                        {{ hostEnabled ? 'the server hostname and certificate.' : 'asks the server for a hostname when the host script is installed.' }}
                    </p>
                </header>
                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Pharmacy name</span>
                        <input v-model="form.name" class="w-full" required>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Slug</span>
                        <input v-model="form.slug" class="w-full" required placeholder="city-care" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                        <span class="mt-1 block text-xs text-[#71717a]">Lowercase. This becomes the subdomain.</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Contact email</span>
                        <input v-model="form.email" type="email" class="w-full" required autocomplete="off">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Phone</span>
                        <input v-model="form.phone" class="w-full">
                    </label>
                    <label class="block text-sm sm:col-span-2">
                        <span class="mb-1 block text-[#3f3f46]">Address</span>
                        <textarea v-model="form.address" class="w-full" rows="2" />
                    </label>
                    <p v-if="form.errors.slug" class="text-sm text-red-700 sm:col-span-2">{{ form.errors.slug }}</p>
                </div>
            </section>

            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>Tenant database</h1>
                    <p class="mt-1 text-sm text-[#71717a]">Leave blank to use {{ prefix }}{{ (form.slug || 'name').replaceAll('-', '_') }}.</p>
                </header>
                <div class="grid items-end gap-3 p-5 sm:grid-cols-2">
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Database name</span>
                        <input v-model="form.database_name" class="w-full" :placeholder="prefix + (form.slug || 'name').replaceAll('-', '_')">
                    </label>
                    <div>
                        <button type="button" class="rounded-lg border border-[#e4e4e7] px-3 py-2 text-sm" @click="validateDatabase">Check availability</button>
                        <p v-if="check" class="mt-2 text-sm" :class="checkOk ? 'text-[#2f6f4e]' : 'text-red-700'">{{ check }}</p>
                        <p v-if="form.errors.database_name" class="mt-2 text-sm text-red-700">{{ form.errors.database_name }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>Pharmacy admin</h1>
                    <p class="mt-1 text-sm text-[#71717a]">This login is created inside the new pharmacy database. It is not the platform admin.</p>
                </header>
                <div class="grid gap-3 p-5 sm:grid-cols-3">
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Admin name</span>
                        <input v-model="form.admin_name" class="w-full" required autocomplete="off">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Admin email</span>
                        <input v-model="form.admin_email" type="email" class="w-full" required autocomplete="off">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Admin password</span>
                        <input v-model="form.admin_password" type="password" class="w-full" required minlength="8" autocomplete="new-password">
                    </label>
                </div>
            </section>

            <div class="flex justify-end gap-2">
                <Link href="/platform/companies" class="rounded-lg border border-[#e4e4e7] bg-white px-4 py-2 text-sm">Cancel</Link>
                <button class="pf-accent rounded-lg px-4 py-2 text-sm" :disabled="form.processing">Start provisioning</button>
            </div>
        </form>
    </Layout>
</template>
