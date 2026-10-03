<template>
    <AuthenticatedLayout>
        <FormScreen :title="`Purchase #${purchase.purchase_no}`" :close-href="route('purchases.index')">
            <template #header-actions>
                <span class="status-chip" :class="{ 'is-on': purchase.status }">
                    <span class="status-dot" />
                    {{ purchase.status ? 'Active' : 'Inactive' }}
                </span>
                <Link :href="route('purchases.edit', purchase.id)" class="btn btn-primary btn-sm">Edit</Link>
            </template>

            <div class="med-view">
                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Purchase information</h6>
                            <p>Identifiers and dates</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Purchase ID</dt>
                                <dd>{{ purchase.purchase_id }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Purchase number</dt>
                                <dd>{{ purchase.purchase_no }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Purchase date</dt>
                                <dd>{{ formatDate(purchase.purchase_date) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Created</dt>
                                <dd>{{ formatDate(purchase.created_at) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Supplier & audit</h6>
                            <p>Manufacturer and user</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Manufacturer</dt>
                                <dd>{{ purchase.manufacturer?.name || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Created by</dt>
                                <dd>{{ purchase.user?.name || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Status</dt>
                                <dd>
                                    <span class="status-chip" :class="{ 'is-on': purchase.status }">
                                        <span class="status-dot" />
                                        {{ purchase.status ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                        <div v-if="purchase.details" class="med-fact med-fact-full mt-2">
                            <dt class="field-label mb-1">Details</dt>
                            <dd class="details-block">{{ purchase.details }}</dd>
                        </div>
                    </section>
                </div>

                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Purchase items</h6>
                        <p>Line items on this purchase</p>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch ID</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in purchase.items" :key="item.id">
                                    <td>
                                        <div class="fw-semibold">{{ item.medicine?.name || 'Unknown' }}</div>
                                        <div class="text-muted font-size-12">{{ item.medicine?.generic_name || '' }}</div>
                                    </td>
                                    <td>{{ item.batch_id }}</td>
                                    <td>{{ item.quantity }}</td>
                                    <td>{{ money(item.rate) }}</td>
                                    <td>{{ money(item.discount || 0) }}</td>
                                    <td>{{ money(item.total_amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Financial summary</h6>
                            <p>Totals and tax</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Subtotal</dt>
                                <dd>{{ money(parseFloat(purchase.grand_total) - parseFloat(purchase.total_tax || 0)) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Tax</dt>
                                <dd>{{ money(purchase.total_tax || 0) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Discount</dt>
                                <dd>{{ money(purchase.total_discount || 0) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Grand total</dt>
                                <dd>{{ money(purchase.grand_total) }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </div>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
                <Link :href="route('purchases.edit', purchase.id)" class="btn btn-primary">Edit</Link>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

defineProps({
    purchase: Object,
})

const page = usePage()

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toFixed(2)
    return ui.currency_position === 'after'
        ? `${amount}${ui.currency_symbol || ''}`
        : `${ui.currency_symbol || ''}${amount}`
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
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

.med-fact {
    min-width: 0;
}

.med-fact-full {
    grid-column: 1 / -1;
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

.details-block {
    font-weight: 500;
    white-space: pre-line;
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

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts {
        grid-template-columns: 1fr;
    }
}
</style>
