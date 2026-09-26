<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineProps({ invoices: Array, subscriptions: Array, currency: String });
const form = useForm({ platform_subscription_id: '', amount: '', issued_on: '', notes: '' });
</script>

<template>
    <Head title="Invoices" />
    <Layout>
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <form class="mt-4 flex flex-wrap gap-2" @submit.prevent="form.post('/platform/invoices')">
            <select v-model="form.platform_subscription_id" class="rounded border px-2 py-1" required>
                <option value="">Subscription</option>
                <option v-for="row in subscriptions" :key="row.id" :value="row.id">{{ row.company?.name }} #{{ row.id }}</option>
            </select>
            <input v-model="form.amount" type="number" step="0.01" class="w-28 rounded border px-2 py-1" placeholder="Amount" required>
            <input v-model="form.issued_on" type="date" class="rounded border px-2 py-1" required>
            <button class="rounded bg-[#1f3d32] px-3 py-1 text-sm text-white">Create</button>
        </form>
        <table class="mt-4 w-full bg-white text-sm">
            <tr v-for="invoice in invoices" :key="invoice.id" class="border-t">
                <td class="p-2">{{ invoice.number }}</td>
                <td class="p-2">{{ invoice.company?.name }}</td>
                <td class="p-2">{{ invoice.amount }} {{ invoice.currency || currency }}</td>
                <td class="p-2">{{ invoice.status }}</td>
                <td class="p-2">
                    <button class="underline" @click="$inertia.post(`/platform/invoices/${invoice.id}/toggle`)">Toggle paid</button>
                    <button class="ml-2 underline" @click="$inertia.delete(`/platform/invoices/${invoice.id}`)">Delete</button>
                </td>
            </tr>
        </table>
    </Layout>
</template>
