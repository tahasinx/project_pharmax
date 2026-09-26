<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({
    prefix: String,
    baseDomain: String,
});
const form = useForm({ name: '', slug: '', email: '', phone: '' });
</script>

<template>
    <Head title="New pharmacy" />
    <Layout>
        <h1 class="text-2xl font-semibold">New pharmacy</h1>
        <p class="mt-1 text-sm text-[#5c6b63]">This creates {{ prefix }}{{ form.slug || 'name' }} and opens https://{{ form.slug || 'name' }}.{{ baseDomain }} after DNS and a certificate exist.</p>
        <form class="mt-4 max-w-lg space-y-3" @submit.prevent="form.post('/platform/companies')">
            <input v-model="form.name" class="w-full rounded border px-3 py-2" placeholder="Pharmacy name" required>
            <input v-model="form.slug" class="w-full rounded border px-3 py-2" placeholder="slug" required>
            <input v-model="form.email" class="w-full rounded border px-3 py-2" placeholder="Contact email">
            <input v-model="form.phone" class="w-full rounded border px-3 py-2" placeholder="Phone">
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white" :disabled="form.processing">Create and provision</button>
        </form>
    </Layout>
</template>
