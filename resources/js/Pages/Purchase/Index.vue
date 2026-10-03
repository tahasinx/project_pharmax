<template>
    <Head title="Purchases" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Purchases</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('purchases.create')" class="btn btn-primary btn-sm">New Purchase</Link>
            </div>
        </template>

        <LunaTable title="Purchases" :pagination="purchases" empty-text="No purchases found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Purchase #</th>
                        <th>Manufacturer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Grand total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="purchase in purchases.data" :key="purchase.id">
                        <td>
                            <div class="fw-semibold">{{ purchase.purchase_no }}</div>
                        </td>
                        <td>{{ purchase.manufacturer?.name || '—' }}</td>
                        <td>{{ formatDate(purchase.purchase_date) }}</td>
                        <td>{{ purchase.items_count || 0 }}</td>
                        <td>{{ money(purchase.grand_total) }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': purchase.status }">
                                <span class="status-dot" />
                                {{ purchase.status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('purchases.show', purchase.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('purchases.edit', purchase.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deletePurchase(purchase.id)"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({
    purchases: Object,
})

const page = usePage()

const money = (value) => {
    const amount = Number(value || 0).toFixed(2)
    const symbol = page.props.ui?.currency_symbol || ''
    return page.props.ui?.currency_position === 'after' ? `${amount}${symbol}` : `${symbol}${amount}`
}

const formatDate = (date) => (date ? new Date(date).toLocaleDateString() : '—')

const deletePurchase = (id) => {
    destroyRecord('purchases.destroy', id, 'Delete this purchase?', 'The purchase has been deleted.')
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
