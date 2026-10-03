<script setup>
import { computed } from 'vue';
import LunaTable from '@/Components/LunaTable.vue';
import BillingNav from '@/Components/Platform/BillingNav.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({
    invoices: Array,
    subscriptions: Array,
    currency: String,
    invoiceFooter: { type: String, default: '' },
});
const form = useForm({ platform_subscription_id: '', amount: '', issued_on: '', notes: '' });

const subscriptionOptions = computed(() => [
    { value: '', label: 'Select subscription' },
    ...(props.subscriptions || []).map((row) => ({
        value: row.id,
        label: `${row.company?.name || 'Pharmacy'} #${row.id}`,
    })),
]);
</script>

<template>
    <Head title="Invoices" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Invoices</h4>
                <p class="text-muted mb-0 font-size-13">{{ invoices.length }} issued document{{ invoices.length === 1 ? '' : 's' }}</p>
            </div>
        </template>

        <BillingNav />

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <div>
                    <h2>Issue invoice</h2>
                    <p>Create a charge against an existing subscription.</p>
                </div>
            </div>
            <div class="pf-card-body">
                <form @submit.prevent="form.post('/platform/invoices')">
                    <div class="pf-composer">
                        <label class="pf-field pf-span-5">
                            <span>Subscription</span>
                            <SearchableSelect
                                v-model="form.platform_subscription_id"
                                :options="subscriptionOptions"
                                placeholder="Select subscription…"
                                required
                            />
                        </label>
                        <label class="pf-field pf-span-2">
                            <span>Amount</span>
                            <input v-model="form.amount" type="number" step="0.01" min="0" required>
                        </label>
                        <label class="pf-field pf-span-2">
                            <span>Issued</span>
                            <input v-model="form.issued_on" type="date" required>
                        </label>
                        <label class="pf-field pf-span-3">
                            <span>Notes</span>
                            <input v-model="form.notes" placeholder="Optional">
                        </label>
                    </div>
                    <p v-if="invoiceFooter" class="small text-muted mt-2 mb-0">
                        Invoice footer from Settings is appended automatically:
                        <span class="fst-italic">{{ invoiceFooter }}</span>
                    </p>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                            {{ form.processing ? 'Creating…' : 'Create invoice' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="pf-card">
            <LunaTable title="Invoices">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Pharmacy</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!invoices.length">
                            <td colspan="6">
                                <div class="pf-empty">
                                    <i class="bi bi-receipt" />
                                    <strong>No invoices yet</strong>
                                    <span>Issue the first invoice from a pharmacy subscription.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="invoice in invoices" :key="invoice.id">
                            <td class="fw-semibold">{{ invoice.number }}</td>
                            <td>{{ invoice.company?.name }}</td>
                            <td>
                                <span class="pf-money">{{ invoice.amount }}</span>
                                <span class="text-muted small ms-1">{{ invoice.currency || currency }}</span>
                            </td>
                            <td class="small text-muted" style="max-width: 14rem; white-space: pre-line;">{{ invoice.notes || '—' }}</td>
                            <td><StatusBadge :status="invoice.status" /></td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-soft-primary" @click="$inertia.post(`/platform/invoices/${invoice.id}/toggle`)">
                                    {{ invoice.status === 'paid' ? 'Mark unpaid' : 'Mark paid' }}
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger ms-1" @click="$inertia.delete(`/platform/invoices/${invoice.id}`)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
