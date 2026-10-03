<template>
    <Head title="Invoices" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Invoices</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('invoices.create')" class="btn btn-primary btn-sm">Create Invoice</Link>
                <Link :href="route('pos')" class="btn btn-success btn-sm">POS Sales</Link>
            </div>
        </template>

        <LunaTable title="Invoices" :pagination="invoices" empty-text="No invoices found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                        <td>
                            <div class="fw-semibold">{{ invoice.invoice_no }}</div>
                        </td>
                        <td>{{ invoice.customer?.name || '—' }}</td>
                        <td>{{ formatDate(invoice.date) }}</td>
                        <td>{{ money(invoice.total_amount) }}</td>
                        <td>{{ money(invoice.paid_amount) }}</td>
                        <td :class="{ 'text-danger': Number(invoice.due_amount) > 0 }">{{ money(invoice.due_amount) }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': Number(invoice.due_amount) <= 0 }">
                                <span class="status-dot" />
                                {{ Number(invoice.due_amount) > 0 ? 'Pending' : 'Paid' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('invoices.show', invoice.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('invoices.edit', invoice.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <Link :href="route('invoices.print', invoice.id)" class="btn btn-sm btn-icon btn-soft-success" title="Print"><i class="bi bi-printer"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteInvoice(invoice.id)"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    invoices: Object,
    ui: Object,
})

const money = (value) => {
    const amount = Number(value || 0).toFixed(2)
    const symbol = props.ui?.currency_symbol || ''
    return props.ui?.currency_position === 'after' ? `${amount}${symbol}` : `${symbol}${amount}`
}

const formatDate = (date) => (date ? new Date(date).toLocaleDateString() : '—')

const deleteInvoice = (id) => {
    destroyRecord('invoices.destroy', id, 'Delete this invoice?', 'The invoice has been deleted.')
}
</script>

<style scoped>
.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: var(--pf-radius, 999px);
    padding: 0.22rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 999px;
    background: var(--shell-panel-muted, #adb5bd);
}

.status-chip.is-on .status-dot {
    background: #34c38f;
}
</style>
