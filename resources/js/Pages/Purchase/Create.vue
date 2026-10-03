<template>
    <AuthenticatedLayout>
        <FormScreen title="Add purchase" :close-href="route('purchases.index')">
            <form id="purchase-create-form" class="med-form" @submit.prevent="submitForm">
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
                                    <SearchableSelect
                                        v-model="form.manufacturer_id"
                                        :options="manufacturerOptions"
                                        placeholder="Search manufacturer…"
                                        required
                                    />
                                    <p v-if="selectedManufacturerMeta" class="field-hint">{{ selectedManufacturerMeta }}</p>
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
                                    <label class="field-label">Payment type <span class="req">*</span></label>
                                    <SearchableSelect
                                        v-model="form.payment_type"
                                        :options="paymentOptions"
                                        placeholder="Select payment type…"
                                        required
                                    />
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
                                <label class="field-label">Search medicines</label>
                                <SearchableSelect
                                    v-model="medicinePick"
                                    :options="medicineOptions"
                                    placeholder="Search medicines…"
                                    @change="onMedicinePick"
                                />
                            </div>
                        </section>

                        <section v-if="cartItems.length > 0" class="med-panel">
                            <header class="med-panel-head">
                                <h6>Purchase items</h6>
                                <p>Quantities and rates</p>
                            </header>
                            <div class="table-responsive">
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
                <button type="submit" form="purchase-create-form" class="btn btn-primary" :disabled="cartItems.length === 0">
                    Create purchase
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
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

const mapOptions = (items, valueKey = 'id', labelKey = 'name') => [...(items || [])]
    .sort((a, b) => String(a[labelKey] || '').localeCompare(String(b[labelKey] || '')))
    .map((item) => ({ value: item[valueKey] ?? item.id, label: item[labelKey] }))

const manufacturerOptions = computed(() => mapOptions(props.manufacturers, 'id'))
const medicineOptions = computed(() => [...(props.medicines || [])]
    .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
    .map((item) => ({
        value: item.id,
        label: item.generic_name ? `${item.name} — ${item.generic_name}` : item.name,
    })))
const paymentOptions = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank Transfer' },
    { value: 'credit', label: 'Credit' },
]

const medicinePick = ref('')
const cartItems = ref([])

const form = ref({
    manufacturer_id: '',
    purchase_date: new Date().toISOString().split('T')[0],
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

const selectedManufacturerMeta = computed(() => {
    const manufacturer = (props.manufacturers || []).find(
        (row) => String(row.id) === String(form.value.manufacturer_id),
    )
    if (!manufacturer) return ''
    return [manufacturer.mobile, manufacturer.email].filter(Boolean).join(' · ')
})

const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + item.total, 0))
const tax = computed(() => subtotal.value * 0.1)
const discount = computed(() => 0)
const total = computed(() => subtotal.value + tax.value - discount.value)
const dueAmount = computed(() => total.value - form.value.paid_amount)

const addProduct = (product) => {
    const medicineId = product.id
    const existingItem = cartItems.value.find((item) => String(item.medicine_id) === String(medicineId))

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
        return
    }

    const rate = Number(product.manufacturer_price || product.price) || 0
    cartItems.value.push({
        medicine_id: medicineId,
        name: product.name,
        generic_name: product.generic_name,
        quantity: 1,
        rate,
        total: rate,
    })
}

const onMedicinePick = (option) => {
    const product = (props.medicines || []).find((row) => String(row.id) === String(option.value))
    if (product) addProduct(product)
    nextTick(() => { medicinePick.value = '' })
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = Number(item.quantity) * Number(item.rate)
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const submitForm = () => {
    if (!form.value.manufacturer_id) {
        alert('Please select a manufacturer.')
        return
    }

    if (cartItems.value.length === 0) {
        alert('Please add at least one product to the purchase.')
        return
    }

    form.value.grand_total = total.value
    form.value.total_tax = tax.value
    form.value.total_discount = discount.value
    form.value.due_amount = dueAmount.value
    form.value.items = cartItems.value

    router.post(route('purchases.store'), form.value)
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
