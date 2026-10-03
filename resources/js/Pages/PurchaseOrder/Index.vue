<template>
    <Head title="Purchase orders" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Purchase orders</h4>
        </template>

        <div class="med-form">
            <form class="med-panel" @submit.prevent="save">
                <header class="med-panel-head">
                    <h6>New order</h6>
                    <p>Order, receive a batch, then post the supplier invoice.</p>
                </header>
                <div class="med-row med-row-2">
                    <div class="med-field">
                        <label class="field-label" for="po-supplier">Supplier <span class="req">*</span></label>
                        <select id="po-supplier" v-model="form.supplier_id" class="field" required>
                            <option value="">Select supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="po-date">Order date <span class="req">*</span></label>
                        <input id="po-date" v-model="form.order_date" type="date" class="field" required>
                    </div>
                </div>
                <div v-for="(item, index) in form.items" :key="index" class="line-row mt-2">
                    <div class="med-field flex-grow-1">
                        <label v-if="index === 0" class="field-label">Medicine</label>
                        <select v-model="item.medicine_id" class="field" required>
                            <option value="">Medicine</option>
                            <option v-for="medicine in medicines" :key="medicine.id" :value="medicine.id">{{ medicine.name }}</option>
                        </select>
                    </div>
                    <div class="med-field line-qty">
                        <label v-if="index === 0" class="field-label">Quantity</label>
                        <input v-model.number="item.quantity" type="number" min="1" class="field" placeholder="Qty">
                    </div>
                    <div class="med-field line-qty">
                        <label v-if="index === 0" class="field-label">Rate</label>
                        <input v-model.number="item.rate" type="number" min="0" step="0.01" class="field" placeholder="Rate">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-soft-primary btn-sm" @click="form.items.push({ medicine_id: '', quantity: 1, rate: 0 })">
                        Add line
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">Create order</button>
                </div>
            </form>

            <section v-if="openReceipts.length" class="med-panel">
                <header class="med-panel-head">
                    <h6>Waiting for purchase invoice</h6>
                    <p>Goods received but not yet invoiced</p>
                </header>
                <LunaTable title="Open receipts" empty-text="No open receipts">
                    <table class="table table-striped table-hover mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Received</th>
                                <th>Lines</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="receipt in openReceipts" :key="receipt.id">
                                <td class="fw-semibold">{{ receipt.supplier?.name }}</td>
                                <td>{{ receipt.received_date }}</td>
                                <td class="text-muted font-size-12">{{ lineSummary(receipt) }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" @click="invoiceReceipt(receipt)">Post invoice</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </LunaTable>
            </section>

            <form class="med-panel" @submit.prevent="purchaseReturn">
                <header class="med-panel-head">
                    <h6>Return batch to supplier</h6>
                    <p>Post a supplier credit for returned stock</p>
                </header>
                <div class="med-row med-row-2">
                    <div class="med-field">
                        <label class="field-label">Supplier <span class="req">*</span></label>
                        <select v-model="ret.supplier_id" class="field" required>
                            <option value="">Supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Batch <span class="req">*</span></label>
                        <select v-model="ret.stock_id" class="field" required>
                            <option value="">Batch</option>
                            <option v-for="batch in batches" :key="batch.id" :value="batch.id">
                                {{ batch.medicine?.name }} · {{ batch.batch_number }} · {{ batch.quantity }} left
                            </option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Quantity <span class="req">*</span></label>
                        <input v-model.number="ret.quantity" type="number" min="1" class="field" required>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Condition</label>
                        <select v-model="ret.condition" class="field">
                            <option value="near_expiry">Near expiry</option>
                            <option value="damaged">Damaged</option>
                            <option value="wrong">Wrong product</option>
                            <option value="short">Short</option>
                            <option value="recall">Recall</option>
                            <option value="quality">Quality</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning btn-sm">Post supplier credit</button>
                </div>
            </form>

            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Open purchase orders</h6>
                    <p>Receive lines into stock by batch</p>
                </header>
                <div v-if="!orders.length" class="text-muted font-size-13 py-2">No purchase orders</div>
                <article v-for="order in orders" :key="order.id" class="order-block">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">{{ order.number }}</div>
                            <div class="text-muted font-size-12">{{ order.supplier?.name }} · {{ order.order_date }}</div>
                        </div>
                        <span class="status-chip" :class="{ 'is-on': order.status === 'open' || order.status === 'partial' }">
                            <span class="status-dot" />
                            {{ order.status }}
                        </span>
                    </div>
                    <form
                        v-for="item in order.items"
                        :key="item.id"
                        class="receive-row"
                        @submit.prevent="receive(order, item)"
                    >
                        <div class="receive-info">
                            <div class="fw-semibold font-size-13">{{ item.medicine?.name || 'Medicine' }}</div>
                            <div class="text-muted font-size-12">
                                Open {{ item.quantity - item.received_quantity }} of {{ item.quantity }} · rate {{ item.rate }}
                            </div>
                        </div>
                        <input v-model="lines[item.id].batch_number" class="field" placeholder="Batch" required>
                        <input v-model="lines[item.id].expiry_date" type="date" class="field">
                        <input v-model.number="lines[item.id].quantity" type="number" min="1" class="field">
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                            :disabled="item.received_quantity >= item.quantity"
                        >
                            Receive
                        </button>
                    </form>
                </article>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    orders: Array,
    suppliers: Array,
    medicines: Array,
    receipts: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
})

const openReceipts = props.receipts
const form = reactive({
    supplier_id: '',
    order_date: new Date().toISOString().slice(0, 10),
    items: [{ medicine_id: '', quantity: 1, rate: 0 }],
})
const lines = reactive({})
props.orders.forEach(order => order.items.forEach(item => {
    lines[item.id] = {
        batch_number: '',
        expiry_date: '',
        quantity: Math.max(item.quantity - item.received_quantity, 1),
        purchase_price: item.rate,
        free_quantity: 0,
    }
}))
const ret = reactive({ supplier_id: '', stock_id: '', quantity: 1, condition: 'near_expiry' })
const lineSummary = (receipt) => (receipt.items || []).map(item => item.medicine?.name).filter(Boolean).join(', ')
const save = () => router.post(route('purchase-orders.store'), form)
const purchaseReturn = () => router.post(route('purchase-returns.store'), { ...ret, return_date: new Date().toISOString().slice(0, 10) })
const invoiceReceipt = (receipt) => router.post(route('purchase-invoices.store'), {
    goods_receipt_id: receipt.id,
    invoice_date: new Date().toISOString().slice(0, 10),
})
const receive = (order, item) => router.post(route('goods-receipts.store'), {
    purchase_order_id: order.id,
    received_date: new Date().toISOString().slice(0, 10),
    items: [{
        purchase_order_item_id: item.id,
        quantity: lines[item.id].quantity,
        free_quantity: lines[item.id].free_quantity || 0,
        batch_number: lines[item.id].batch_number,
        expiry_date: lines[item.id].expiry_date || null,
        purchase_price: lines[item.id].purchase_price,
        mrp: lines[item.id].purchase_price,
    }],
})
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
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

.med-field {
    margin-bottom: 0.55rem;
}

.med-row {
    display: grid;
    gap: 0.55rem;
}

.med-row-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.line-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    align-items: flex-end;
}

.line-qty {
    width: 7rem;
    flex-shrink: 0;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.req {
    color: #f46a6a;
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

.order-block {
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
    padding-top: 0.85rem;
    margin-top: 0.85rem;
}

.order-block:first-of-type {
    border-top: none;
    margin-top: 0;
    padding-top: 0;
}

.receive-row {
    display: grid;
    grid-template-columns: minmax(8rem, 1.5fr) repeat(3, minmax(5rem, 1fr)) auto;
    gap: 0.5rem;
    align-items: end;
    padding: 0.65rem 0;
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
}

.receive-info {
    min-width: 0;
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
    text-transform: capitalize;
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
    .med-row-2 {
        grid-template-columns: 1fr;
    }

    .receive-row {
        grid-template-columns: 1fr;
    }
}
</style>
