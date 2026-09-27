<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineProps({ invoices: Array, subscriptions: Array, currency: String });
const form = useForm({ platform_subscription_id: '', amount: '', issued_on: '', notes: '' });
</script>

<template>
    <Head title="Invoices" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Invoices</h1>
                <p class="mt-1 text-sm text-[#71717a]">{{ invoices.length }} invoice{{ invoices.length === 1 ? '' : 's' }}</p>
            </div>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="form.post('/platform/invoices')">
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Subscription</span>
                    <select v-model="form.platform_subscription_id" class="min-w-48" required>
                        <option value="">Select</option>
                        <option v-for="row in subscriptions" :key="row.id" :value="row.id">{{ row.company?.name }} #{{ row.id }}</option>
                    </select>
                </label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Amount</span><input v-model="form.amount" type="number" step="0.01" class="w-28" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Issued</span><input v-model="form.issued_on" type="date" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Notes</span><input v-model="form.notes" class="w-48"></label>
                <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">Create</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Pharmacy</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!invoices.length">
                        <td colspan="5" class="py-10 text-center text-sm text-[#71717a]">No invoices yet.</td>
                    </tr>
                    <tr v-for="invoice in invoices" :key="invoice.id">
                        <td class="font-medium">{{ invoice.number }}</td>
                        <td>{{ invoice.company?.name }}</td>
                        <td>{{ invoice.amount }} {{ invoice.currency || currency }}</td>
                        <td>{{ invoice.status }}</td>
                        <td class="text-right">
                            <button class="text-sm font-medium text-[#17342b]" @click="$inertia.post(`/platform/invoices/${invoice.id}/toggle`)">{{ invoice.status === 'paid' ? 'Mark unpaid' : 'Mark paid' }}</button>
                            <button class="ml-3 text-sm text-[#b91c1c]" @click="$inertia.delete(`/platform/invoices/${invoice.id}`)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </Layout>
</template>
