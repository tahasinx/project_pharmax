<template>
    <AuthenticatedLayout>
        <FormScreen :title="`Edit purchase #${purchase.purchase_no}`" :close-href="route('purchases.index')">
            <template #header-actions>
                <Link :href="route('purchases.show', purchase.id)" class="btn btn-soft-primary btn-sm">View</Link>
            </template>

            <form id="purchase-edit-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-doc-layout">
                    <div class="med-doc-main">
                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Purchase information</h6>
                                <p>Supplier, date, and payment</p>
                            </header>

                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label">Manufacturer <span class="req">*</span></label>
                                    <div class="entity-search">
                                        <input
                                            :value="selectedManufacturer ? selectedManufacturer.name : manufacturerSearch"
                                            type="text"
                                            class="field"
                                            :placeholder="selectedManufacturer ? selectedManufacturer.name : 'Search manufacturers…'"
                                            autocomplete="off"
                                            @input="handleManufacturerInput"
                                            @focus="showManufacturerResults = true"
                                        >
                                        <div
                                            v-if="showManufacturerResults && manufacturerSearchResults.length > 0"
                                            class="entity-search-dropdown"
                                        >
                                            <button
                                                v-for="manufacturer in manufacturerSearchResults"
                                                :key="manufacturer.id"
                                                type="button"
                                                class="entity-search-item"
                                                @mousedown.prevent="selectManufacturer(manufacturer)"
                                            >
                                                <div class="entity-search-name">{{ manufacturer.name }}</div>
                                                <div v-if="manufacturer.email" class="entity-search-meta">{{ manufacturer.email }}</div>
                                                <div v-if="manufacturer.mobile" class="entity-search-meta">{{ manufacturer.mobile }}</div>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="selectedManufacturer" class="selected-chip">
                                        <div>
                                            <div class="selected-chip-name">{{ selectedManufacturer.name }}</div>
                                            <div v-if="selectedManufacturer.email" class="entity-search-meta">{{ selectedManufacturer.email }}</div>
                                        </div>
                                        <button type="button" class="selected-chip-clear" @click="clearManufacturer">×</button>
                                    </div>
                                    <p v-if="errors.manufacturer || (!selectedManufacturer && manufacturerSearch.length > 0)" class="field-error">
                                        {{ errors.manufacturer || 'Please select a manufacturer from the dropdown' }}
                                    </p>
                                </div>
                                <div class="med-field">
                                    <label class="field-label" for="purchase-date">Purchase date <span class="req">*</span></label>
                                    <input id="purchase-date" v-model="form.purchase_date" type="date" class="field" required>
                                </div>
                            </div>

                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label" for="purchase-chalan">Chalan no</label>
                                    <input id="purchase-chalan" v-model="form.chalan_no" type="text" class="field" placeholder="Auto-generated if empty">
                                </div>
                                <div class="med-field">
                                    <label class="field-label" for="purchase-payment">Payment type <span class="req">*</span></label>
                                    <select id="purchase-payment" v-model="form.payment_type" class="field" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="med-row med-row-2">
                                <div class="med-field">
                                    <label class="field-label" for="purchase-paid">Paid amount</label>
                                    <input id="purchase-paid" v-model="form.paid_amount" type="number" step="0.01" min="0" class="field" placeholder="0.00">
                                </div>
                                <div class="med-field">
                                    <label class="field-label" for="purchase-due">Due amount</label>
                                    <input id="purchase-due" :value="dueAmount.toFixed(2)" type="number" step="0.01" readonly class="field field-readonly">
                                </div>
                            </div>

                            <div class="med-field">
                                <label class="field-label" for="purchase-details">Details</label>
                                <textarea id="purchase-details" v-model="form.details" rows="3" class="field" placeholder="Enter purchase details…"></textarea>
                            </div>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Add products</h6>
                                <p>Search medicines to add line items</p>
                            </header>
                            <div class="med-field">
                                <label class="field-label" for="purchase-product-search">Search medicines</label>
                                <div class="entity-search">
                                    <input
                                        id="purchase-product-search"
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
                                <h6>Purchase items</h6>
                                <p>Quantities and rates</p>
                            </header>
                            <p v-if="errors.items" class="field-error">{{ errors.items }}</p>
                            <div v-if="cartItems.length > 0" class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0 line-items-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Rate</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in cartItems" :key="index">
                                            <td>
                                                <div class="fw-semibold">{{ item.name }}</div>
                                                <div class="text-muted font-size-12">{{ item.generic_name }}</div>
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="item.quantity"
                                                    type="number"
                                                    min="1"
                                                    class="field field-inline"
                                                    @input="updateItemTotal(index)"
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    v-model.number="item.rate"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="field field-inline field-inline-wide"
                                                    @input="updateItemTotal(index)"
                                                >
                                            </td>
                                            <td class="fw-semibold">{{ money(item.total) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeItem(index)">Remove</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <aside class="med-doc-aside">
                        <section class="med-panel med-panel-sticky">
                            <header class="med-panel-head">
                                <h6>Purchase summary</h6>
                                <p>Totals before saving</p>
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
                                    <dd>{{ money(discount) }}</dd>
                                </div>
                                <div class="summary-line summary-line-total">
                                    <dt>Total</dt>
                                    <dd>{{ money(total) }}</dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="purchase-edit-form" class="btn btn-primary" :disabled="cartItems.length === 0">
                    Update purchase
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    purchase: Object,
    manufacturers: Array,
    medicines: Array,
})

const page = usePage()

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toFixed(2)
    return ui.currency_position === 'after'
        ? `${amount}${ui.currency_symbol || ''}`
        : `${ui.currency_symbol || ''}${amount}`
}

const productSearch = ref('')
const productSearchResults = ref([])
const cartItems = ref([])

const manufacturerSearch = ref('')
const manufacturerSearchResults = ref([])
const selectedManufacturer = ref(null)
const showManufacturerResults = ref(false)

const form = ref({
    manufacturer_id: '',
    purchase_date: '',
    chalan_no: '',
    payment_type: 'cash',
    details: '',
    grand_total: 0,
    total_tax: 0,
    total_discount: 0,
    paid_amount: 0,
    due_amount: 0,
    total_vat: 0,
    bank_id: null,
    items: [],
})

const errors = ref({ manufacturer: '', items: '' })

const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + item.total, 0))

const tax = computed(() => subtotal.value * 0.1)

const discount = computed(() => 0)

const total = computed(() => subtotal.value + tax.value - discount.value)

const dueAmount = computed(() => total.value - form.value.paid_amount)

onMounted(() => {
    form.value = {
        manufacturer_id: props.purchase.manufacturer_id,
        purchase_date: props.purchase.purchase_date ? new Date(props.purchase.purchase_date).toISOString().split('T')[0] : '',
        chalan_no: props.purchase.chalan_no || '',
        payment_type: props.purchase.payment_type || 'cash',
        details: props.purchase.details || '',
        grand_total: props.purchase.grand_total,
        total_tax: props.purchase.total_tax || 0,
        total_discount: props.purchase.total_discount || 0,
        paid_amount: props.purchase.paid_amount || 0,
        due_amount: props.purchase.due_amount || 0,
        total_vat: props.purchase.total_vat || 0,
        bank_id: props.purchase.bank_id || null,
        items: [],
    }

    if (props.purchase.manufacturer) {
        selectedManufacturer.value = props.purchase.manufacturer
    }

    cartItems.value = props.purchase.items.map(item => ({
        medicine_id: item.medicine_id,
        name: item.medicine?.name || 'Unknown',
        generic_name: item.medicine?.generic_name || '',
        quantity: Number(item.quantity),
        rate: Number(item.rate),
        total: Number(item.total_amount),
    }))
})

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
        productSearchResults.value = []
    }
}

const handleManufacturerInput = (event) => {
    manufacturerSearch.value = event.target.value
    if (selectedManufacturer.value && event.target.value !== selectedManufacturer.value.name) {
        selectedManufacturer.value = null
        form.value.manufacturer_id = ''
    }
    searchManufacturers()
}

const searchManufacturers = async () => {
    if (manufacturerSearch.value.length < 2) {
        manufacturerSearchResults.value = []
        return
    }

    try {
        const response = await fetch(`/api/manufacturers/search?q=${manufacturerSearch.value}`)
        manufacturerSearchResults.value = await response.json()
    } catch (error) {
        console.error('Error searching manufacturers:', error)
        manufacturerSearchResults.value = []
    }
}

const selectManufacturer = (manufacturer) => {
    selectedManufacturer.value = manufacturer
    form.value.manufacturer_id = manufacturer.id
    manufacturerSearch.value = ''
    manufacturerSearchResults.value = []
    showManufacturerResults.value = false
}

const clearManufacturer = () => {
    selectedManufacturer.value = null
    form.value.manufacturer_id = ''
    manufacturerSearch.value = ''
    manufacturerSearchResults.value = []
    showManufacturerResults.value = false
}

const addProduct = (product) => {
    const existingItem = cartItems.value.find(item => item.medicine_id === product.id)

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
    } else {
        const rate = Number(product.manufacturer_price || product.price) || 0
        cartItems.value.push({
            medicine_id: product.id,
            name: product.name,
            generic_name: product.generic_name,
            quantity: 1,
            rate: rate,
            total: rate,
        })
    }

    productSearch.value = ''
    productSearchResults.value = []
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = Number(item.quantity) * Number(item.rate)
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const submitForm = () => {
    errors.value.manufacturer = ''
    errors.value.items = ''

    if (!selectedManufacturer.value) {
        errors.value.manufacturer = 'Please select a manufacturer from the dropdown'
        return
    }

    if (cartItems.value.length === 0) {
        errors.value.items = 'Please add at least one product to the purchase'
        return
    }

    form.value.grand_total = total.value
    form.value.total_tax = tax.value
    form.value.total_discount = discount.value
    form.value.due_amount = dueAmount.value
    form.value.items = cartItems.value

    router.put(route('purchases.update', props.purchase.id), form.value)
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

.field-inline-wide {
    width: 6rem;
    min-width: 6rem;
}

.field-error {
    margin: 0.2rem 0 0;
    font-size: 0.7rem;
    color: #f46a6a;
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
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
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

.selected-chip-clear {
    border: none;
    background: transparent;
    font-size: 1.1rem;
    line-height: 1;
    color: var(--shell-panel-muted, #74788d);
    cursor: pointer;
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
