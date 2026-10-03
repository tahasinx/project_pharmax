<template>
    <Head title="Stock Reports" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Stock reports & analytics</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('stocks.index')" class="btn btn-outline-secondary btn-sm">Back to stock</Link>
            </div>
        </template>

        <div class="row g-3 mb-4">
            <div v-for="stat in statCards" :key="stat.label" class="col-xl col-md-4 col-sm-6">
                <div class="card card-h-100 mb-0">
                    <div class="card-body">
                        <p class="text-muted mb-1 font-size-13">{{ stat.label }}</p>
                        <h4 class="mb-0">{{ stat.value }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Stock distribution by category</h5>
                <div v-if="categoryRows.length === 0" class="text-muted mb-0">No category data yet.</div>
                <div v-for="row in categoryRows" :key="row.category" class="d-flex align-items-center gap-3 mb-3">
                    <div class="text-truncate font-size-13" style="width: 8rem;">{{ row.category }}</div>
                    <div class="flex-grow-1">
                        <div class="progress" style="height: 0.65rem;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                :style="{ width: row.percent + '%' }"
                                :aria-valuenow="row.percent"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            />
                        </div>
                    </div>
                    <div class="text-end font-size-13 text-nowrap" style="width: 5rem;">{{ row.quantity }} units</div>
                </div>
            </div>
        </div>

        <LunaTable title="Medicines expiring soon (next 30 days)" empty-text="No medicines expiring soon">
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
                    <tr v-for="stock in expiringMedicines" :key="stock.id">
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
                                <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Update stock">
                                    <i class="bi bi-pencil"></i>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, Head, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    stats: Object,
    stockByCategory: Object,
    expiringMedicines: Array,
})

const page = usePage()

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toFixed(2)
}

const stockValueDisplay = computed(() => {
    const ui = page.props.ui
    const amount = formatCurrency(props.stats.total_stock_value)
    if (ui.currency_position === 'before') {
        return `${ui.currency_symbol}${amount}`
    }
    return `${amount}${ui.currency_symbol}`
})

const statCards = computed(() => [
    { label: 'Low stock', value: props.stats.low_stock_count },
    { label: 'Expiring soon', value: props.stats.expiring_soon_count },
    { label: 'Expired', value: props.stats.expired_count },
    { label: 'Total medicines', value: props.stats.total_medicines },
    { label: 'Stock value', value: stockValueDisplay.value },
])

const categoryRows = computed(() => {
    const entries = Object.entries(props.stockByCategory || {})
    const max = Math.max(...entries.map(([, quantity]) => quantity), 0)
    return entries.map(([category, quantity]) => ({
        category,
        quantity,
        percent: max > 0 ? (quantity / max) * 100 : 0,
    }))
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const daysChipClass = (days) => {
    if (days <= 7) return { 'is-warn': true }
    if (days <= 15) return { 'is-caution': true }
    return { 'is-pending': true }
}

const daysUntilExpiry = (expiryDate) => {
    if (!expiryDate) return 0
    const today = new Date()
    const expiry = new Date(expiryDate)
    const diffTime = expiry - today
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
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
