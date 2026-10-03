<template>
    <AuthenticatedLayout>
        <FormScreen title="Create invoice" :close-href="route('invoices.index')">
            <form id="invoice-create-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-doc-layout">
                    <div class="med-doc-main">
                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Customer information</h6>
                                <p>Bill-to customer and payment type</p>
                            </header>
                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label">Customer <span class="req">*</span></label>
                                    <SearchableSelect
                                        v-model="form.customer_id"
                                        :options="customerOptions"
                                        placeholder="Search customer…"
                                        required
                                    />
                                    <p v-if="selectedCustomerMeta" class="field-hint">{{ selectedCustomerMeta }}</p>
                                </div>
                                <div class="med-field">
                                    <label class="field-label">Payment type <span class="req">*</span></label>
                                    <SearchableSelect
                                        v-model="form.payment_type"
                                        :options="paymentOptions"
                                        placeholder="Select payment type…"
                                        required
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Invoice details</h6>
                                <p>Number and date</p>
                            </header>
                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label" for="invoice-no">Invoice number</label>
                                    <input id="invoice-no" v-model="form.invoice_no" type="text" readonly class="field field-readonly">
                                </div>
                                <div class="med-field">
                                    <label class="field-label" for="invoice-date">Date <span class="req">*</span></label>
                                    <input id="invoice-date" v-model="form.date" type="date" class="field" required>
                                </div>
                            </div>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Add products</h6>
                                <p>Search medicines to add line items</p>
                            </header>
                            <div class="med-field">
                                <label class="field-label">Search medicines</label>
                                <SearchableSelect
                                    v-model="medicinePick"
                                    :options="medicineOptions"
                                    placeholder="Search medicines…"
                                    @change="onMedicinePick"
                                />
                            </div>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Invoice items</h6>
                                <p>Quantities and line totals</p>
                            </header>
                            <div v-if="cartItems.length === 0" class="text-muted font-size-13">No items added to invoice</div>
                            <div v-else class="invoice-items-list">
                                <div v-for="(item, index) in cartItems" :key="index" class="invoice-item-row">
                                    <div class="invoice-item-info">
                                        <div class="fw-semibold">{{ item.name }}</div>
                                        <div class="text-muted font-size-12">{{ item.generic_name }}</div>
                                    </div>
                                    <div class="invoice-item-controls">
                                        <div class="qty-controls">
                                            <button type="button" class="qty-btn" @click="decreaseQuantity(index)">−</button>
                                            <input
                                                v-model.number="item.quantity"
                                                type="number"
                                                min="1"
                                                class="field field-inline qty-input"
                                                @change="updateItemTotal(index)"
                                            >
                                            <button type="button" class="qty-btn" @click="increaseQuantity(index)">+</button>
                                        </div>
                                        <div class="invoice-item-total">{{ money(item.total) }}</div>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeItem(index)">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="med-doc-aside">
                        <section class="med-panel med-panel-sticky">
                            <header class="med-panel-head">
                                <h6>Invoice summary</h6>
                                <p>Totals and payment</p>
                            </header>
                            <dl class="summary-lines">
                                <div class="summary-line">
                                    <dt>Subtotal</dt>
                                    <dd>{{ money(subtotal) }}</dd>
                                </div>
                                <div class="summary-line">
                                    <dt>Tax ({{ props.ui.default_tax_rate || 10 }}%)</dt>
                                    <dd>{{ money(tax) }}</dd>
                                </div>
                                <div class="summary-line">
                                    <dt>Discount</dt>
                                    <dd>−{{ money(discount) }}</dd>
                                </div>
                                <div class="summary-line summary-line-total">
                                    <dt>Total</dt>
                                    <dd>{{ money(total) }}</dd>
                                </div>
                            </dl>

                            <div class="med-field mt-3">
                                <label class="field-label" for="invoice-discount">Discount amount</label>
                                <input
                                    id="invoice-discount"
                                    v-model.number="discountAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="field"
                                    placeholder="0.00"
                                    @input="updateDiscount"
                                >
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="invoice-paid">Paid amount</label>
                                <input id="invoice-paid" v-model.number="form.paid_amount" type="number" step="0.01" class="field">
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="invoice-due">Due amount</label>
                                <input id="invoice-due" v-model.number="form.due_amount" type="number" step="0.01" readonly class="field field-readonly">
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="invoice-notes">Notes</label>
                                <textarea id="invoice-notes" v-model="form.details" rows="3" class="field"></textarea>
                            </div>
                        </section>
                    </aside>
                </div>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button
                    type="submit"
                    form="invoice-create-form"
                    class="btn btn-primary"
                    :disabled="cartItems.length === 0 || !form.customer_id"
                >
                    Create invoice
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
    customers: Array,
    medicines: Array,
    invoiceNo: String,
    invoicePrefix: String,
    ui: Object,
})

const page = usePage()

const money = (value) => {
    const ui = props.ui || page.props.ui || {}
    const amount = Number(value ?? 0).toFixed(2)
    return ui.currency_position === 'after'
        ? `${amount}${ui.currency_symbol || ''}`
        : `${ui.currency_symbol || ''}${amount}`
}

const customerOptions = computed(() => [...(props.customers || [])]
    .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
    .map((item) => ({
        value: item.id,
        label: item.mobile ? `${item.name} — ${item.mobile}` : item.name,
    })))
const medicineOptions = computed(() => [...(props.medicines || [])]
    .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
    .map((item) => ({
        value: item.id,
        label: item.generic_name ? `${item.name} — ${item.generic_name}` : item.name,
    })))
const paymentOptions = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank' },
    { value: 'credit', label: 'Credit' },
]

const medicinePick = ref('')
const cartItems = ref([])

const form = ref({
    customer_id: '',
    payment_type: 'cash',
    paid_amount: 0,
    due_amount: 0,
    invoice_no: `${props.invoicePrefix || 'INV'}-${props.invoiceNo}`,
    date: new Date().toISOString().split('T')[0],
    details: '',
    items: [],
})

const selectedCustomerMeta = computed(() => {
    const customer = (props.customers || []).find(
        (row) => String(row.id) === String(form.value.customer_id),
    )
    if (!customer) return ''
    return [customer.mobile, customer.email, customer.address].filter(Boolean).join(' · ')
})

const subtotal = computed(() => {
    const sum = cartItems.value.reduce((sum, item) => sum + Number(item.total ?? 0), 0)
    return Math.round(sum * 100) / 100
})

const tax = computed(() => {
    const taxRate = props.ui?.default_tax_rate || page.props.ui?.default_tax_rate || 10
    const taxAmount = subtotal.value * (taxRate / 100)
    return Math.round(taxAmount * 100) / 100
})

const discountAmount = ref(0)

const discount = computed(() => {
    const maxDiscount = subtotal.value + tax.value
    const d = Number(discountAmount.value) || 0
    const clampedDiscount = Math.min(Math.max(d, 0), maxDiscount)
    return Math.round(clampedDiscount * 100) / 100
})

const total = computed(() => {
    const totalAmount = (Number(subtotal.value) || 0) + (Number(tax.value) || 0) - (Number(discount.value) || 0)
    return Math.round(totalAmount * 100) / 100
})

watch([() => form.value.paid_amount, () => discountAmount.value], () => {
    const dueAmount = total.value - (Number(form.value.paid_amount) || 0)
    form.value.due_amount = Math.round(dueAmount * 100) / 100
})

const updateDiscount = () => {
    form.value.due_amount = total.value - (Number(form.value.paid_amount) || 0)
}

const addProduct = (product) => {
    const existingItem = cartItems.value.find((item) => String(item.id) === String(product.id))

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
        return
    }

    cartItems.value.push({
        id: product.id,
        medicine_id: product.id,
        name: product.name,
        generic_name: product.generic_name,
        price: Number(product.price ?? 0),
        quantity: 1,
        total: Number(product.price ?? 0),
    })
}

const onMedicinePick = (option) => {
    const product = (props.medicines || []).find((row) => String(row.id) === String(option.value))
    if (product) addProduct(product)
    nextTick(() => { medicinePick.value = '' })
}

const increaseQuantity = (index) => {
    cartItems.value[index].quantity += 1
    updateItemTotal(index)
}

const decreaseQuantity = (index) => {
    if (cartItems.value[index].quantity > 1) {
        cartItems.value[index].quantity -= 1
        updateItemTotal(index)
    }
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.price = Number(item.price ?? 0)
    item.quantity = Number(item.quantity ?? 0)
    item.total = item.price * item.quantity
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const submitForm = () => {
    form.value.items = cartItems.value.map(item => ({
        medicine_id: item.medicine_id,
        quantity: item.quantity,
        rate: item.price,
        discount: 0,
        batch_id: 'BATCH001',
    }))

    form.value.total_amount = total.value
    form.value.total_tax = tax.value
    form.value.total_discount = discount.value

    router.post(route('invoices.store'), form.value)
}
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-doc-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(220px, 280px);
    gap: 0.85rem;
    align-items: start;
}

.med-doc-main {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    min-width: 0;
}

.med-doc-aside {
    min-width: 0;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-sticky {
    position: sticky;
    top: 0.5rem;
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

.med-field:last-child {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
    margin-bottom: 0.55rem;
}

.med-row:last-child {
    margin-bottom: 0;
}

.med-row-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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

.field-readonly {
    background: var(--shell-panel-bg, #f8f9fc);
    color: var(--shell-panel-muted, #74788d);
}

.field-inline {
    width: 4.5rem;
    min-width: 4.5rem;
}

.field-hint {
    margin: 0.2rem 0 0;
    font-size: 0.7rem;
    color: var(--shell-panel-muted, #74788d);
}

.summary-lines {
    margin: 0;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.45rem;
}

.summary-line dt {
    margin: 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.summary-line dd {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
}

.summary-line-total {
    margin-top: 0.35rem;
    padding-top: 0.45rem;
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
}

.summary-line-total dt,
.summary-line-total dd {
    font-size: 0.92rem;
    font-weight: 700;
}

.invoice-items-list {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.invoice-item-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.55rem 0.65rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.35rem);
    background: var(--shell-panel-surface, #fff);
}

.invoice-item-controls {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.qty-controls {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.qty-btn {
    width: 1.75rem;
    height: 1.75rem;
    border: 1px solid var(--shell-panel-border, #ced4da);
    border-radius: var(--pf-radius, 999px);
    background: var(--shell-panel-bg, #f8f9fc);
    font-size: 0.9rem;
    line-height: 1;
    cursor: pointer;
}

.qty-input {
    text-align: center;
}

.invoice-item-total {
    min-width: 4.5rem;
    font-weight: 600;
    font-size: 0.82rem;
}

@media (max-width: 991.98px) {
    .med-doc-layout,
    .med-row-2 {
        grid-template-columns: 1fr;
    }

    .med-panel-sticky {
        position: static;
    }
}
</style>
