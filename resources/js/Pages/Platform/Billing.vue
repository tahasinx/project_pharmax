<script setup>
import LunaTable from '@/Components/LunaTable.vue';
import BillingNav from '@/Components/Platform/BillingNav.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({ invoices: Array, from: String, to: String, paid: Number, unpaid: Number });
const filter = reactive({ from: props.from || '', to: props.to || '' });
</script>

<template>
    <Head title="Billing" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Billing overview</h4>
                <p class="text-muted mb-0 font-size-13">Cashflow across issued pharmacy invoices.</p>
            </div>
        </template>

        <BillingNav />

        <div class="pf-metric-grid mb-3">
            <div class="pf-metric">
                <span class="pf-metric-label">Paid</span>
                <span class="pf-metric-value">{{ paid }}</span>
                <span class="pf-metric-hint">Settled in range</span>
            </div>
            <div class="pf-metric">
                <span class="pf-metric-label">Unpaid</span>
                <span class="pf-metric-value">{{ unpaid }}</span>
                <span class="pf-metric-hint">Open balance</span>
            </div>
            <div class="pf-metric">
                <span class="pf-metric-label">Documents</span>
                <span class="pf-metric-value">{{ invoices.length }}</span>
                <span class="pf-metric-hint">Invoices shown</span>
            </div>
        </div>

        <form class="pf-toolbar" @submit.prevent="router.get('/platform/billing', filter)">
            <label class="pf-field">
                <span>From</span>
                <input v-model="filter.from" type="date">
            </label>
            <label class="pf-field">
                <span>To</span>
                <input v-model="filter.to" type="date">
            </label>
            <button type="submit" class="btn btn-primary btn-sm">Apply range</button>
        </form>

        <section class="pf-card">
            <LunaTable title="Invoice activity">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Issued</th>
                            <th>Number</th>
                            <th>Pharmacy</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!invoices.length">
                            <td colspan="5">
                                <div class="pf-empty">
                                    <i class="bi bi-graph-up" />
                                    <strong>No activity in this range</strong>
                                    <span>Adjust the dates or issue an invoice first.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="invoice in invoices" :key="invoice.id">
                            <td class="small text-muted">{{ invoice.issued_on }}</td>
                            <td class="fw-semibold">{{ invoice.number }}</td>
                            <td>{{ invoice.company?.name }}</td>
                            <td><span class="pf-money">{{ invoice.amount }}</span></td>
                            <td><StatusBadge :status="invoice.status" /></td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
