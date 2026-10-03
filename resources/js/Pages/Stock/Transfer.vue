<template>
    <Head title="Branch transfer" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h4 class="mb-sm-0 font-size-18">Branch transfer</h4>
                <p class="text-muted font-size-13 mb-0 mt-1">Request, dispatch, then receive. The batch number stays the same.</p>
            </div>
        </template>

        <div class="transfer-page">
            <form class="med-panel med-form" @submit.prevent="save">
                <header class="med-panel-head">
                    <h6>Request transfer</h6>
                    <p>Send stock to another branch</p>
                </header>
                <div class="med-form-grid-inner">
                    <div class="med-field">
                        <label class="field-label" for="xfer-batch">Batch</label>
                        <select id="xfer-batch" v-model="form.stock_id" class="field" required>
                            <option value="">Select batch</option>
                            <option v-for="stock in stocks" :key="stock.id" :value="stock.id">
                                {{ stock.medicine?.name }} · {{ stock.batch_number }} · {{ stock.quantity }}
                            </option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="xfer-branch">Destination</label>
                        <select id="xfer-branch" v-model="form.to_branch_id" class="field" required>
                            <option value="">Select branch</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="xfer-qty">Quantity</label>
                        <input id="xfer-qty" v-model.number="form.quantity" type="number" min="1" class="field" required>
                    </div>
                    <div class="med-field med-field-action">
                        <label class="field-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Request transfer</button>
                    </div>
                </div>
            </form>

            <form class="med-panel med-form" @submit.prevent="adjust">
                <header class="med-panel-head">
                    <h6>Stock adjustment</h6>
                    <p>Opening, damage, or write-off</p>
                </header>
                <div class="med-form-grid-inner med-form-grid-inner-wide">
                    <div class="med-field">
                        <label class="field-label" for="adj-batch">Batch to adjust</label>
                        <select id="adj-batch" v-model="adj.stock_id" class="field" required>
                            <option value="">Select batch</option>
                            <option v-for="stock in stocks" :key="'a' + stock.id" :value="stock.id">
                                {{ stock.medicine?.name }} · {{ stock.batch_number }}
                            </option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="adj-type">Type</label>
                        <select id="adj-type" v-model="adj.type" class="field">
                            <option value="opening">Opening</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="damage">Damage</option>
                            <option value="write_off">Write-off</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="adj-delta">Quantity change</label>
                        <input
                            id="adj-delta"
                            v-model.number="adj.quantity_delta"
                            type="number"
                            class="field"
                            placeholder="Use minus to reduce"
                            required
                        >
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="adj-reason">Reason</label>
                        <input id="adj-reason" v-model="adj.reason" type="text" class="field" placeholder="Reason" required>
                    </div>
                    <div class="med-field med-field-span">
                        <button type="submit" class="btn btn-primary">Save adjustment</button>
                    </div>
                </div>
            </form>

            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Transfers</h6>
                    <p>Pending and in-flight requests</p>
                </header>

                <p v-if="!transfers.length" class="dt-empty text-danger text-center mb-0">No transfers yet</p>

                <ul v-else class="transfer-list">
                    <li v-for="row in transfers" :key="row.id" class="transfer-row">
                        <div class="transfer-row-main">
                            <div class="fw-semibold">{{ row.stock?.medicine?.name }} · {{ row.stock?.batch_number }}</div>
                            <div class="text-muted font-size-12">
                                {{ row.quantity }} from {{ row.from_branch?.name }} to {{ row.to_branch?.name }}
                            </div>
                        </div>
                        <div class="transfer-row-actions">
                            <span class="status-chip" :class="{ 'is-on': row.status === 'received', 'is-pending': row.status === 'requested' || row.status === 'dispatched' }">
                                <span class="status-dot" />
                                {{ row.status }}
                            </span>
                            <button
                                v-if="row.status === 'requested'"
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                @click="router.post(route('stock-transfers.dispatch', row.id))"
                            >
                                Dispatch
                            </button>
                            <button
                                v-if="row.status === 'dispatched'"
                                type="button"
                                class="btn btn-sm btn-primary"
                                @click="router.post(route('stock-transfers.receive', row.id))"
                            >
                                Receive
                            </button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({ transfers: Array, branches: Array, stocks: Array })

const form = reactive({ stock_id: '', to_branch_id: '', quantity: 1 })
const adj = reactive({ stock_id: '', quantity_delta: -1, type: 'adjustment', reason: '' })

const save = () => router.post(route('stock-transfers.store'), form)
const adjust = () => router.post(route('stocks.adjust'), adj)
</script>

<style scoped>
.transfer-page {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.65rem;
    padding-bottom: 0.45rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-form-grid-inner {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.55rem;
    align-items: end;
}

.med-form-grid-inner-wide {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.med-field-span {
    grid-column: 1 / -1;
}

.med-field {
    margin-bottom: 0;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.field {
    width: 100%;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0.32rem 0.55rem;
    font-size: 0.82rem;
    line-height: 1.3;
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.transfer-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.transfer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
    font-size: 0.85rem;
}

.transfer-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.transfer-row-main {
    min-width: 0;
}

.transfer-row-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
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
    text-transform: uppercase;
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-chip.is-pending {
    border-color: #cfe2ff;
    background: #eef4ff;
    color: #5156be;
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
    background: #5156be;
}

.dt-empty {
    padding: 1rem 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .med-form-grid-inner,
    .med-form-grid-inner-wide {
        grid-template-columns: 1fr;
    }
}
</style>
