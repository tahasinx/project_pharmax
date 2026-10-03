<template>
    <Head title="Stock Management" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Stock</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('stocks.create')" class="btn btn-primary btn-sm">Add Stock</Link>
                <Link :href="route('stocks.reports')" class="btn btn-success btn-sm">Reports</Link>
                <Link :href="route('stocks.alerts')" class="btn btn-danger btn-sm">
                    Alerts
                    <span v-if="alerts.total > 0" class="badge bg-light text-danger ms-1">{{ alerts.total }}</span>
                </Link>
            </div>
        </template>

        <LunaTable title="Stock" :pagination="stocks" empty-text="No stock entries found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Batch</th>
                        <th>Quantity</th>
                        <th>Expiry date</th>
                        <th>Location</th>
                        <th>Batch status</th>
                        <th>Weighted cost</th>
                        <th>MRP</th>
                        <th>Purchase price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="stock in stocks.data" :key="stock.id">
                        <td>
                            <div class="fw-semibold">{{ stock.medicine.name }}</div>
                            <div class="text-muted font-size-12">{{ stock.medicine.generic_name }}</div>
                            <div class="text-muted font-size-12">{{ stock.medicine.category?.name }}</div>
                        </td>
                        <td>{{ stock.batch_number || '—' }}</td>
                        <td>{{ stock.quantity }}</td>
                        <td>
                            <span v-if="stock.expiry_date">{{ formatDate(stock.expiry_date) }}</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>{{ stock.warehouse?.name || 'Unassigned' }}</td>
                        <td>{{ stock.status || 'available' }}{{ stock.recalled ? ' · recalled' : '' }}</td>
                        <td>{{ Number(weightedCosts[stock.medicine_id] || 0).toFixed(2) }}</td>
                        <td>{{ stock.mrp != null ? Number(stock.mrp).toFixed(2) : '—' }}</td>
                        <td>
                            {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(stock.purchase_price || 0).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                        </td>
                        <td>
                            <span class="status-chip" :class="statusChipClass(stock)">
                                <span class="status-dot" />
                                {{ getStatusText(stock) }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('stocks.show', stock.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteStock(stock.id)"><i class="bi bi-trash"></i></button>
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

defineProps({
    stocks: Object,
    alerts: Object,
    weightedCosts: { type: Object, default: () => ({}) },
})

const getStatusText = (stock) => {
    if (isExpired(stock)) return 'Expired'
    if (isExpiringSoon(stock)) return 'Expiring soon'
    if (isLowStock(stock)) return 'Low stock'
    return 'Good'
}

const statusChipClass = (stock) => {
    if (isExpired(stock)) return { 'is-warn': true }
    if (isExpiringSoon(stock)) return { 'is-caution': true }
    if (isLowStock(stock)) return { 'is-pending': true }
    return { 'is-on': true }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const deleteStock = (id) => {
    destroyRecord('stocks.destroy', id, 'Delete this stock entry?', 'The stock entry has been deleted.')
}

const isExpired = (stock) => {
    if (!stock.expiry_date) return false
    return new Date(stock.expiry_date) < new Date()
}

const isExpiringSoon = (stock) => {
    if (!stock.expiry_date) return false
    const expiryDate = new Date(stock.expiry_date)
    const now = new Date()
    const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000))
    return expiryDate >= now && expiryDate <= thirtyDaysFromNow
}

const isLowStock = (stock) => {
    return stock.quantity <= stock.min_stock_level
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

.status-chip.is-pending {
    border-color: #ffeaa7;
    background: #fff8e6;
    color: #d68910;
}

.status-chip.is-caution {
    border-color: #ffd8a8;
    background: #fff3e0;
    color: #e67e22;
}

.status-chip.is-warn {
    border-color: #f5c6cb;
    background: #fdecea;
    color: #c0392b;
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

.status-chip.is-pending .status-dot {
    background: #f1b44c;
}

.status-chip.is-caution .status-dot {
    background: #e67e22;
}

.status-chip.is-warn .status-dot {
    background: #f46a6a;
}
</style>
