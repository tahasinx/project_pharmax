<template>
    <AuthenticatedLayout>
        <FormScreen :title="`Edit invoice #${invoice.invoice_no}`" :close-href="route('invoices.index')">
            <template #header-actions>
                <Link :href="route('invoices.show', invoice.id)" class="btn btn-soft-primary btn-sm">View</Link>
            </template>

            <form id="invoice-edit-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-doc-layout">
                    <div class="med-doc-main">
                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Customer information</h6>
                                <p>Bill-to customer and payment type</p>
                            </header>
                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label" for="invoice-customer">Customer <span class="req">*</span></label>
                                    <div class="entity-search customer-search-container">
                                        <input
                                            id="invoice-customer"
                                            v-model="customerSearch"
                                            type="text"
                                            class="field"
                                            placeholder="Search customer…"
                                            autocomplete="off"
                                            required
                                            @input="searchCustomers"
                                            @focus="customerSearchFocused = true"
                                        >
                                        <div
                                            v-if="customerSearchFocused && customerSearchResults.length > 0"
                                            class="entity-search-dropdown"
                                        >
                                            <button
                                                v-for="customer in customerSearchResults"
                                                :key="customer.id"
                                                type="button"
                                                class="entity-search-item"
                                                @mousedown.prevent="selectCustomer(customer)"
                                            >
                                                {{ customer.name }} — {{ customer.mobile }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="med-field">
                                    <label class="field-label" for="invoice-payment">Payment type <span class="req">*</span></label>
                                    <select id="invoice-payment" v-model="form.payment_type" class="field" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </div>
                            </div>
                            <div v-if="selectedCustomer" class="selected-chip">
                                <div>
                                    <div class="selected-chip-name">{{ selectedCustomer.name }}</div>
                                    <div class="entity-search-meta">Mobile: {{ selectedCustomer.mobile }}</div>
                                    <div class="entity-search-meta">Email: {{ selectedCustomer.email || 'N/A' }}</div>
                                    <div class="entity-search-meta">Address: {{ selectedCustomer.address || 'N/A' }}</div>
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
                                <label class="field-label" for="invoice-product-search">Search medicines</label>
                                <div class="entity-search">
                                    <input
                                        id="invoice-product-search"
                                        v-model="productSearch"
                                        type="text"
                                        class="field"
                                        placeholder="Search medicines…"
                                        autocomplete="off"
                                        @input="searchProducts"
                                    >
                                    <div v-if="productSearchResults.length > 0" class="entity-search-dropdown">
                                        <button
                                            v-for="product in productSearchResults"
                                            :key="product.id"
                                            type="button"
                                            class="entity-search-item"
                                            @mousedown.prevent="addProduct(product)"
                                        >
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <div class="entity-search-name">{{ product.name }}</div>
                                                    <div class="entity-search-meta">{{ product.generic_name }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="entity-search-name">{{ money(product.price) }}</div>
                                                    <div class="entity-search-meta">{{ product.category?.name }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
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
                                    <dt>Tax (10%)</dt>
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
                                    :max="subtotal"
                                    class="field"
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
                    form="invoice-edit-form"
                    class="btn btn-primary"
                    :disabled="cartItems.length === 0 || !form.customer_id"
                >
                    Update invoice
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    invoice: Object,
    customers: Array,
    medicines: Array,
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

const formatDateForInput = (value) => {
    if (!value) return ''
    const dt = new Date(value)
    if (Number.isNaN(dt.getTime())) return ''
    const pad = (n) => String(n).padStart(2, '0')
    return `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}`
}

const productSearch = ref('')
const productSearchResults = ref([])
const cartItems = ref([])
const selectedCustomer = ref(null)
const customerSearch = ref('')
const customerSearchResults = ref([])
const customerSearchFocused = ref(false)
const discountAmount = ref(0)

const form = ref({
    customer_id: props.invoice.customer_id,
    payment_type: props.invoice.payment_type,
    paid_amount: props.invoice.paid_amount,
    due_amount: props.invoice.due_amount,
    invoice_no: props.invoice.invoice_no,
    date: formatDateForInput(props.invoice.date),
    details: props.invoice.details,
    items: [],
})

const subtotal = computed(() => {
    const sum = cartItems.value.reduce((sum, item) => Number(sum) + Number(item.total || 0), 0)
    return Math.round(sum * 100) / 100
})

const tax = computed(() => {
    const taxAmount = subtotal.value * 0.1
    return Math.round(taxAmount * 100) / 100
})

const discount = computed(() => Number(discountAmount.value) || 0)

const total = computed(() => {
    const totalAmount = subtotal.value + tax.value - discount.value
    return Math.round(totalAmount * 100) / 100
})

const recomputeDue = () => {
    const paid = Number(form.value.paid_amount) || 0
    const dueAmount = total.value - paid
    form.value.due_amount = Math.round(dueAmount * 100) / 100
}

watch([() => form.value.paid_amount, () => discountAmount.value, total], () => {
    recomputeDue()
})

onMounted(() => {
    cartItems.value = props.invoice.items.map(item => {
        const priceNum = Number(item.rate) || 0
        const qtyNum = Number(item.quantity) || 1
        const totalNum = Number(item.total_amount)
        return {
            id: item.medicine_id,
            medicine_id: item.medicine_id,
            name: item.medicine?.name || 'Unknown',
            generic_name: item.medicine?.generic_name || '',
            price: priceNum,
            quantity: qtyNum,
            total: Number.isFinite(totalNum) ? totalNum : priceNum * qtyNum,
        }
    })

    selectedCustomer.value = props.customers.find(c => c.id == form.value.customer_id)
    if (selectedCustomer.value) {
        customerSearch.value = selectedCustomer.value.name
    }

    discountAmount.value = Number(props.invoice.invoice_discount) || 0
})

const searchCustomers = async () => {
    if (customerSearch.value.length < 2) {
        customerSearchResults.value = []
        return
    }

    try {
        const response = await fetch(`/api/customers/search?q=${customerSearch.value}`)
        customerSearchResults.value = await response.json()
    } catch (error) {
        console.error('Error searching customers:', error)
    }
}

const selectCustomer = (customer) => {
    selectedCustomer.value = customer
    form.value.customer_id = customer.id
    customerSearch.value = `${customer.name}`
    customerSearchResults.value = []
    customerSearchFocused.value = false
}

const updateDiscount = () => {
    if (discountAmount.value > subtotal.value) {
        discountAmount.value = subtotal.value
    }
    if (discountAmount.value < 0) {
        discountAmount.value = 0
    }
    recomputeDue()
}

const searchProducts = async () => {
    if (productSearch.value.length < 2) {
        productSearchResults.value = []
        return
    }

    try {
        const response = await fetch(`/api/medicines/search?q=${productSearch.value}`)
        productSearchResults.value = await response.json()
    } catch (error) {
        console.error('Error searching products:', error)
    }
}

const addProduct = (product) => {
    const existingItem = cartItems.value.find(item => item.id === product.id)

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
    } else {
        cartItems.value.push({
            id: product.id,
            medicine_id: product.id,
            name: product.name,
            generic_name: product.generic_name,
            price: Number(product.price) || 0,
            quantity: 1,
            total: Number(product.price) || 0,
        })
    }

    productSearch.value = ''
    productSearchResults.value = []
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
    item.total = (Number(item.price) || 0) * (Number(item.quantity) || 0)
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
    form.value.invoice_discount = discount.value

    router.put(route('invoices.update', props.invoice.id), form.value)
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

.entity-search {
    position: relative;
}

.entity-search-dropdown {
    position: absolute;
    z-index: 50;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 0.25rem;
    max-height: 15rem;
    overflow-y: auto;
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #ced4da);
    border-radius: var(--pf-radius, 0.35rem);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.entity-search-item {
    display: block;
    width: 100%;
    padding: 0.45rem 0.55rem;
    border: none;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
    background: transparent;
    text-align: left;
    cursor: pointer;
    font-size: 0.82rem;
}

.entity-search-item:last-child {
    border-bottom: none;
}

.entity-search-item:hover {
    background: var(--shell-panel-bg, #f8f9fc);
}

.entity-search-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
}

.entity-search-meta {
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.selected-chip {
    margin-top: 0.45rem;
    padding: 0.45rem 0.55rem;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
}

.selected-chip-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--shell-panel-accent-text, #1e8f68);
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
