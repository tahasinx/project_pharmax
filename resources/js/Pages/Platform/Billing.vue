<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({ invoices: Array, from: String, to: String, paid: Number, unpaid: Number });
const filter = reactive({ from: props.from || '', to: props.to || '' });
</script>

<template>
    <Head title="Billing" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-[#f4f4f5] px-5 py-4">
                <div>
                    <h1>Billing</h1>
                    <p class="mt-1 text-sm text-[#71717a]">Paid {{ paid }} · Unpaid {{ unpaid }}</p>
                </div>
                <form class="flex flex-wrap items-end gap-3" @submit.prevent="router.get('/platform/billing', filter)">
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">From</span><input v-model="filter.from" type="date"></label>
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">To</span><input v-model="filter.to" type="date"></label>
                    <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">Filter</button>
                </form>
            </div>
            <LunaTable title="Billing">
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Issued</th>
                        <th>Number</th>
                        <th>Pharmacy</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!invoices.length">
                        <td colspan="4" class="py-10 text-center text-sm text-[#71717a]">No invoices in this range.</td>
                    </tr>
                    <tr v-for="invoice in invoices" :key="invoice.id">
                        <td>{{ invoice.issued_on }}</td>
                        <td class="font-medium">{{ invoice.number }}</td>
                        <td>{{ invoice.company?.name }}</td>
                        <td>{{ invoice.amount }} · {{ invoice.status }}</td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
        </section>
    </Layout>
</template>
