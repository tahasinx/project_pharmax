<template>
    <Head title="Stock Details" />
    <AuthenticatedLayout>
        <FormScreen :title="stock.medicine?.name || 'Stock details'" :close-href="route('stocks.index')">
            <template #header-actions>
                <span class="status-chip" :class="{ 'is-on': stock.is_active }">
                    <span class="status-dot" />
                    {{ stock.is_active ? 'Active' : 'Inactive' }}
                </span>
                <Link :href="route('stocks.edit', stock.id)" class="btn btn-primary btn-sm">Edit</Link>
                <button type="button" class="btn btn-outline-danger btn-sm" @click="deleteStock(stock.id)">Delete</button>
            </template>

            <div class="med-view">
                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Stock information</h6>
                            <p>Batch, quantity, and levels</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Medicine</dt>
                                <dd>
                                    {{ stock.medicine?.name || '—' }}
                                    <span v-if="stock.medicine?.generic_name" class="med-sub">{{ stock.medicine.generic_name }}</span>
                                </dd>
                            </div>
                            <div class="med-fact">
                                <dt>Batch number</dt>
                                <dd>{{ stock.batch_number || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Current quantity</dt>
                                <dd>{{ stock.quantity }} units</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Expiry date</dt>
                                <dd>
                                    <template v-if="stock.expiry_date">
                                        {{ formatDate(stock.expiry_date) }}
                                        <span v-if="isExpired(stock)" class="badge-warn danger">Expired</span>
                                        <span v-else-if="isExpiringSoon(stock)" class="badge-warn">Expiring soon</span>
                                    </template>
                                    <template v-else>—</template>
                                </dd>
                            </div>
                            <div class="med-fact">
                                <dt>Minimum level</dt>
                                <dd>{{ stock.min_stock_level }} units</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Maximum level</dt>
                                <dd>{{ stock.max_stock_level || '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Pricing & supplier</h6>
                            <p>Unit costs and source</p>
                        </header>
                        <div class="med-metrics">
                            <div class="med-metric">
                                <span class="med-metric-label">Purchase price</span>
                                <span class="med-metric-value">{{ money(stock.purchase_price) }} <small>/ unit</small></span>
                            </div>
                            <div class="med-metric">
                                <span class="med-metric-label">Selling price</span>
                                <span class="med-metric-value">{{ money(stock.selling_price) }} <small>/ unit</small></span>
                            </div>
                        </div>
                        <dl class="med-facts med-facts-tight">
                            <div class="med-fact">
                                <dt>Supplier</dt>
                                <dd>{{ stock.supplier || '—' }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Stock status</h6>
                            <p>Levels and expiry</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Stock level</dt>
                                <dd>
                                    <span class="status-chip" :class="{ 'is-on': !isLowStock(stock), 'is-warn': isLowStock(stock) }">
                                        <span class="status-dot" />
                                        {{ getStockStatusText() }}
                                    </span>
                                </dd>
                            </div>
                            <div class="med-fact">
                                <dt>Expiry status</dt>
                                <dd>
                                    <span class="status-chip" :class="expiryChipClass()">
                                        <span class="status-dot" />
                                        {{ getExpiryStatusText() }}
                                    </span>
                                </dd>
                            </div>
                            <div class="med-fact">
                                <dt>Days until expiry</dt>
                                <dd>{{ daysUntilExpiry(stock) ?? '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Record</h6>
                            <p>Lifecycle timestamps</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Created</dt>
                                <dd>{{ formatDate(stock.created_at) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Last updated</dt>
                                <dd>{{ formatDate(stock.updated_at) }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <section v-if="stock.notes" class="med-panel med-panel-notes">
                    <header class="med-panel-head">
                        <h6>Notes</h6>
                        <p>Additional information</p>
                    </header>
                    <p class="med-notes">{{ stock.notes }}</p>
                </section>
            </div>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

const props = defineProps({
    stock: Object,
})

const page = usePage()

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toFixed(2)
    const symbol = ui.currency_symbol || ''
    return ui.currency_position === 'after' ? `${amount}${symbol}` : `${symbol}${amount}`
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
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

const isLowStock = (stock) => stock.quantity <= stock.min_stock_level

const daysUntilExpiry = (stock) => {
    if (!stock.expiry_date) return null
    const expiryDate = new Date(stock.expiry_date)
    const now = new Date()
    const diffTime = expiryDate - now
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getStockStatusText = () => (isLowStock(props.stock) ? 'Low stock' : 'Good stock')

const getExpiryStatusText = () => {
    if (isExpired(props.stock)) return 'Expired'
    if (isExpiringSoon(props.stock)) return 'Expiring soon'
    return 'Good'
}

const expiryChipClass = () => {
    if (isExpired(props.stock)) return { 'is-warn': true }
    if (isExpiringSoon(props.stock)) return { 'is-caution': true }
    return { 'is-on': true }
}

const deleteStock = (id) => {
    destroyRecord('stocks.destroy', id, 'Delete this stock entry?', 'The stock entry has been deleted.')
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-notes {
    background: var(--shell-panel-surface, #fff);
}

.med-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem 1rem;
    margin: 0;
}

.med-facts-tight {
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px dashed var(--shell-panel-border, #e6e8ee);
}

.med-fact {
    min-width: 0;
}

.med-fact dt {
    margin: 0 0 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-fact dd {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    word-break: break-word;
}

.med-sub {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--shell-panel-muted, #74788d);
}

.med-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.med-metric {
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.5rem);
    padding: 0.75rem 0.85rem;
}

.med-metric-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-metric-value {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
}

.med-metric-value small {
    font-size: 0.78rem;
    font-weight: 600;
    opacity: 0.8;
}

.med-notes {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.55;
    color: var(--shell-panel-muted, #495057);
    white-space: pre-line;
}

.badge-warn {
    display: inline-block;
    margin-left: 0.35rem;
    font-size: 0.68rem;
    font-weight: 600;
    color: #f1b44c;
}

.badge-warn.danger {
    color: #f46a6a;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: 999px;
    padding: 0.28rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-chip.is-warn {
    border-color: #f5c6cb;
    background: #fdecea;
    color: #c0392b;
}

.status-chip.is-caution {
    border-color: #ffeaa7;
    background: #fff8e6;
    color: #d68910;
}

.status-dot {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 999px;
    background: var(--shell-panel-muted, #adb5bd);
}

.status-chip.is-on .status-dot {
    background: #34c38f;
}

.status-chip.is-warn .status-dot {
    background: #f46a6a;
}

.status-chip.is-caution .status-dot {
    background: #f1b44c;
}

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts,
    .med-metrics {
        grid-template-columns: 1fr;
    }
}
</style>
