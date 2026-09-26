<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from './Layout.vue';

const props = defineProps({ settings: Object, tenancy: Object });
const form = useForm({ ...props.settings });
</script>

<template>
    <Head title="Platform settings" />
    <Layout>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <form class="mt-4 max-w-xl space-y-2" @submit.prevent="form.put('/platform/settings')">
            <input v-model="form.name" class="w-full rounded border px-2 py-1" required>
            <input v-model="form.tagline" class="w-full rounded border px-2 py-1" placeholder="Tagline">
            <input v-model="form.support_email" class="w-full rounded border px-2 py-1" placeholder="Support email">
            <input v-model="form.support_phone" class="w-full rounded border px-2 py-1" placeholder="Support phone">
            <textarea v-model="form.address" class="w-full rounded border px-2 py-1" placeholder="Address" />
            <input v-model="form.default_currency" class="w-full rounded border px-2 py-1" required>
            <textarea v-model="form.invoice_footer" class="w-full rounded border px-2 py-1" placeholder="Invoice footer" />
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white">Save</button>
        </form>
        <pre class="mt-6 overflow-auto rounded bg-white p-3 text-xs">{{ tenancy }}</pre>
    </Layout>
</template>
