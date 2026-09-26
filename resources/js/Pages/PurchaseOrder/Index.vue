<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Purchase orders</h2>
                <p class="text-sm text-gray-500">Order, receive a batch, then post the supplier invoice.</p>
            </div>
        </template>
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6">
            <form class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm" @submit.prevent="save">
                <div class="mb-4 text-sm font-medium text-gray-900">New order</div>
                <div class="grid gap-3 md:grid-cols-2">
                    <select v-model="form.supplier_id" class="rounded-lg border-gray-200 text-sm" required>
                        <option value="">Supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                    <input v-model="form.order_date" type="date" class="rounded-lg border-gray-200 text-sm" required>
                </div>
                <div v-for="(item, index) in form.items" :key="index" class="mt-3 grid gap-3 md:grid-cols-4">
                    <select v-model="item.medicine_id" class="rounded-lg border-gray-200 text-sm md:col-span-2" required>
                        <option value="">Medicine</option>
                        <option v-for="medicine in medicines" :key="medicine.id" :value="medicine.id">{{ medicine.name }}</option>
                    </select>
                    <input v-model.number="item.quantity" type="number" min="1" class="rounded-lg border-gray-200 text-sm" placeholder="Quantity">
                    <input v-model.number="item.rate" type="number" min="0" step="0.01" class="rounded-lg border-gray-200 text-sm" placeholder="Rate">
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="button" class="text-sm text-emerald-700" @click="form.items.push({ medicine_id: '', quantity: 1, rate: 0 })">Add line</button>
                    <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Create order</button>
                </div>
            </form>

            <section v-if="openReceipts.length" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-medium text-gray-900">Waiting for a purchase invoice</h3>
                <div v-for="receipt in openReceipts" :key="receipt.id" class="flex items-center justify-between border-t py-3 text-sm first:border-t-0">
                    <div>
                        <div class="font-medium">{{ receipt.supplier?.name }}</div>
                        <div class="text-gray-500">{{ receipt.received_date }} · {{ lineSummary(receipt) }}</div>
                    </div>
                    <button class="rounded-lg bg-emerald-700 px-3 py-1.5 text-white" @click="invoiceReceipt(receipt)">Post invoice</button>
                </div>
            </section>

            <form class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm" @submit.prevent="purchaseReturn">
                <h3 class="mb-3 text-sm font-medium text-gray-900">Return a batch to the supplier</h3>
                <div class="grid gap-3 md:grid-cols-2">
                    <select v-model="ret.supplier_id" class="rounded-lg border-gray-200 text-sm" required>
                        <option value="">Supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                    <select v-model="ret.stock_id" class="rounded-lg border-gray-200 text-sm" required>
                        <option value="">Batch</option>
                        <option v-for="batch in batches" :key="batch.id" :value="batch.id">{{ batch.medicine?.name }} · {{ batch.batch_number }} · {{ batch.quantity }} left</option>
                    </select>
                    <input v-model.number="ret.quantity" type="number" min="1" class="rounded-lg border-gray-200 text-sm" placeholder="Quantity" required>
                    <select v-model="ret.condition" class="rounded-lg border-gray-200 text-sm">
                        <option value="near_expiry">Near expiry</option>
                        <option value="damaged">Damaged</option>
                        <option value="wrong">Wrong product</option>
                        <option value="short">Short</option>
                        <option value="recall">Recall</option>
                        <option value="quality">Quality</option>
                    </select>
                </div>
                <button class="mt-4 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white">Post supplier credit</button>
            </form>

            <article v-for="order in orders" :key="order.id" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">{{ order.number }}</div>
                        <div class="text-sm text-gray-500">{{ order.supplier?.name }} · {{ order.order_date }}</div>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium uppercase tracking-wide text-gray-600">{{ order.status }}</span>
                </div>
                <form v-for="item in order.items" :key="item.id" class="grid items-end gap-2 border-t py-3 md:grid-cols-6" @submit.prevent="receive(order, item)">
                    <div class="md:col-span-2 text-sm">
                        <div class="font-medium">{{ item.medicine?.name || 'Medicine' }}</div>
                        <div class="text-gray-500">Open {{ item.quantity - item.received_quantity }} of {{ item.quantity }} · rate {{ item.rate }}</div>
                    </div>
                    <input v-model="lines[item.id].batch_number" class="rounded-lg border-gray-200 text-sm" placeholder="Batch" required>
                    <input v-model="lines[item.id].expiry_date" type="date" class="rounded-lg border-gray-200 text-sm">
                    <input v-model.number="lines[item.id].quantity" type="number" min="1" class="rounded-lg border-gray-200 text-sm">
                    <button class="rounded-lg bg-emerald-700 px-3 py-2 text-sm text-white" :disabled="item.received_quantity >= item.quantity">Receive</button>
                </form>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

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
