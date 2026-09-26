<script setup>
import { Head, useForm } from '@inertiajs/vue3';
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
        <h1 class="text-2xl font-semibold">Edit {{ company.name }}</h1>
        <form class="mt-4 max-w-lg space-y-2" @submit.prevent="form.put(`/platform/companies/${company.id}`)">
            <input v-model="form.name" class="w-full rounded border px-2 py-1" required>
            <input v-model="form.email" class="w-full rounded border px-2 py-1" placeholder="Contact email">
            <input v-model="form.admin_email" class="w-full rounded border px-2 py-1" placeholder="Admin email">
            <input v-model="form.phone" class="w-full rounded border px-2 py-1" placeholder="Phone">
            <textarea v-model="form.address" class="w-full rounded border px-2 py-1" placeholder="Address" />
            <select v-model="form.status" class="w-full rounded border px-2 py-1">
                <option value="active">Active</option>
                <option value="locked">Locked</option>
            </select>
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white">Save</button>
        </form>
    </Layout>
</template>
