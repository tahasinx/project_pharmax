<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({ invoices: Array, from: String, to: String, paid: Number, unpaid: Number });
const filter = reactive({ from: props.from || '', to: props.to || '' });
</script>

<template>
    <Head title="Billing" />
    <Layout>
        <h1 class="text-2xl font-semibold">Billing report</h1>
        <form class="mt-4 flex gap-2" @submit.prevent="router.get('/platform/billing', filter)">
            <input v-model="filter.from" type="date" class="rounded border px-2 py-1">
            <input v-model="filter.to" type="date" class="rounded border px-2 py-1">
            <button class="rounded bg-[#1f3d32] px-3 py-1 text-sm text-white">Filter</button>
        </form>
        <p class="mt-4 text-sm">Paid {{ paid }} · Unpaid {{ unpaid }}</p>
        <table class="mt-4 w-full bg-white text-sm">
            <tr v-for="invoice in invoices" :key="invoice.id" class="border-t">
                <td class="p-2">{{ invoice.issued_on }}</td>
                <td class="p-2">{{ invoice.number }}</td>
                <td class="p-2">{{ invoice.company?.name }}</td>
                <td class="p-2">{{ invoice.amount }} {{ invoice.status }}</td>
            </tr>
        </table>
    </Layout>
</template>
