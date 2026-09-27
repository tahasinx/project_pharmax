<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ company: Object });
const form = useForm({
    name: props.company.name,
    email: props.company.email || '',
    admin_email: props.company.admin_email || '',
    phone: props.company.phone || '',
    address: props.company.address || '',
    status: props.company.status,
});
</script>

<template>
    <Head title="Edit pharmacy" />
    <Layout>
        <form class="mx-auto max-w-2xl rounded-xl border border-[#e4e4e7] bg-white" @submit.prevent="form.put(`/platform/companies/${company.id}`)">
            <header class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Edit {{ company.name }}</h1>
                <p class="mt-1 text-sm text-[#71717a]">Slug <span class="font-mono">{{ company.slug }}</span> and database <span class="font-mono">{{ company.database_name }}</span> stay fixed after provisioning.</p>
            </header>
            <div class="grid gap-3 p-5 sm:grid-cols-2">
                <label class="block text-sm sm:col-span-2"><span class="mb-1 block text-[#3f3f46]">Pharmacy name</span><input v-model="form.name" class="w-full" required></label>
                <label class="block text-sm"><span class="mb-1 block text-[#3f3f46]">Contact email</span><input v-model="form.email" type="email" class="w-full"></label>
                <label class="block text-sm"><span class="mb-1 block text-[#3f3f46]">Admin email</span><input v-model="form.admin_email" type="email" class="w-full"></label>
                <label class="block text-sm"><span class="mb-1 block text-[#3f3f46]">Phone</span><input v-model="form.phone" class="w-full"></label>
                <label class="block text-sm"><span class="mb-1 block text-[#3f3f46]">Status</span>
                    <select v-model="form.status" class="w-full">
                        <option value="active">Active</option>
                        <option value="locked">Locked</option>
                    </select>
                </label>
                <label class="block text-sm sm:col-span-2"><span class="mb-1 block text-[#3f3f46]">Address</span><textarea v-model="form.address" class="w-full" rows="2" /></label>
            </div>
            <div class="flex justify-end gap-2 border-t border-[#f4f4f5] px-5 py-4">
                <Link :href="`/platform/companies/${company.id}`" class="rounded-md border px-3 py-2 text-sm">Cancel</Link>
                <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm text-white" :disabled="form.processing">Save</button>
            </div>
        </form>
    </Layout>
</template>
