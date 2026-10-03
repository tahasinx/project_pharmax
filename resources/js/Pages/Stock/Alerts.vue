<template>
    <Head title="Stock Alerts" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Stock alerts</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('stocks.index')" class="btn btn-outline-secondary btn-sm">Back to stock</Link>
            </div>
        </template>

        <div class="alerts-page">
            <LunaTable title="Low stock alerts" empty-text="No low stock alerts">
                <table class="table table-striped table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Current stock</th>
                            <th>Min level</th>
                            <th>Shortage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stock in lowStock" :key="stock.id">
                            <td>
                                <div class="fw-semibold">{{ stock.medicine.name }}</div>
                                <div class="text-muted font-size-12">{{ stock.medicine.generic_name }}</div>
                                <div class="text-muted font-size-12">{{ stock.medicine.category?.name }}</div>
                            </td>
                            <td><span class="text-danger fw-semibold">{{ stock.quantity }}</span></td>
                            <td>{{ stock.min_stock_level }}</td>
                            <td><span class="text-danger fw-semibold">{{ stock.min_stock_level - stock.quantity }} units</span></td>
                            <td>
                                <div class="dt-actions">
                                    <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-soft-primary" title="Update stock">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>

            <LunaTable title="Expiring soon (next 30 days)" empty-text="No medicines expiring soon">
                <table class="table table-striped table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Expiry date</th>
                            <th>Days left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stock in expiringSoon" :key="stock.id">
                            <td>
                                <div class="fw-semibold">{{ stock.medicine.name }}</div>
                                <div class="text-muted font-size-12">{{ stock.medicine.generic_name }}</div>
                            </td>
                            <td>{{ stock.batch_number || '—' }}</td>
                            <td>{{ stock.quantity }}</td>
                            <td>{{ formatDate(stock.expiry_date) }}</td>
                            <td>
                                <span class="status-chip" :class="daysChipClass(daysUntilExpiry(stock.expiry_date))">
                                    <span class="status-dot" />
                                    {{ daysUntilExpiry(stock.expiry_date) }} days
                                </span>
                            </td>
                            <td>
                                <div class="dt-actions">
                                    <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-soft-primary" title="Update stock">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>

            <LunaTable title="Expired stock" empty-text="No expired stock">
                <table class="table table-striped table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Expiry date</th>
                            <th>Days overdue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stock in expired" :key="stock.id">
                            <td>
                                <div class="fw-semibold">{{ stock.medicine.name }}</div>
                                <div class="text-muted font-size-12">{{ stock.medicine.generic_name }}</div>
                            </td>
                            <td>{{ stock.batch_number || '—' }}</td>
                            <td>{{ stock.quantity }}</td>
                            <td>{{ formatDate(stock.expiry_date) }}</td>
                            <td>
                                <span class="status-chip is-warn">
                                    <span class="status-dot" />
                                    {{ Math.abs(daysUntilExpiry(stock.expiry_date)) }} days
                                </span>
                            </td>
                            <td>
                                <div class="dt-actions">
                                    <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-soft-primary" title="Update stock">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({
    lowStock: Array,
    expired: Array,
    expiringSoon: Array,
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const daysUntilExpiry = (expiryDate) => {
    if (!expiryDate) return 0
    const today = new Date()
    const expiry = new Date(expiryDate)
    const diffTime = expiry - today
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const daysChipClass = (days) => {
    if (days <= 7) return { 'is-warn': true }
    if (days <= 15) return { 'is-caution': true }
    return { 'is-pending': true }
}
</script>

<style scoped>
.alerts-page {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

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
