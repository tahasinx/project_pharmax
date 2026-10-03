<template>
    <Head title="Edit Stock" />
    <AuthenticatedLayout>
        <FormScreen :title="`Edit stock: ${stock.medicine?.name || ''}`" :close-href="route('stocks.show', stock.id)">
            <template #header-actions>
                <button
                    type="button"
                    class="status-chip"
                    :class="{ 'is-on': form.is_active }"
                    role="switch"
                    :aria-checked="form.is_active"
                    @click="form.is_active = !form.is_active"
                >
                    <span class="status-dot" />
                    {{ form.is_active ? 'Active' : 'Inactive' }}
                </button>
            </template>

            <form id="stock-edit-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Stock information</h6>
                            <p>Batch, quantity, and levels</p>
                        </header>

                        <div class="med-field">
                            <label class="field-label" for="stock-medicine">Medicine <span class="req">*</span></label>
                            <div class="medicine-search">
                                <input
                                    id="stock-medicine"
                                    v-model="medicineSearch"
                                    type="text"
                                    class="field"
                                    placeholder="Search medicine…"
                                    autocomplete="off"
                                    required
                                    @input="searchMedicines"
                                    @focus="medicineSearchFocused = true"
                                    @blur="handleBlur"
                                >
                                <div
                                    v-if="medicineSearchFocused && medicineSearchResults.length > 0"
                                    class="medicine-search-dropdown"
                                >
                                    <button
                                        v-for="medicine in medicineSearchResults"
                                        :key="medicine.id"
                                        type="button"
                                        class="medicine-search-item"
                                        @mousedown.prevent="selectMedicine(medicine)"
                                    >
                                        <div class="medicine-search-name">{{ medicine.name }}</div>
                                        <div class="medicine-search-meta">{{ medicine.generic_name }}</div>
                                        <div class="medicine-search-meta muted">{{ medicine.category?.name || 'No category' }}</div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="stock-batch">Batch number</label>
                            <input id="stock-batch" v-model="form.batch_number" type="text" class="field" placeholder="e.g. BATCH001">
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="stock-expiry">Expiry date <span class="req">*</span></label>
                                <input id="stock-expiry" v-model="form.expiry_date" type="date" class="field" required>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="stock-qty">Quantity <span class="req">*</span></label>
                                <input id="stock-qty" v-model.number="form.quantity" type="number" min="0" class="field" required>
                            </div>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="stock-min">Minimum stock level <span class="req">*</span></label>
                                <input id="stock-min" v-model.number="form.min_stock_level" type="number" min="0" class="field" required>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="stock-max">Maximum stock level</label>
                                <input id="stock-max" v-model.number="form.max_stock_level" type="number" min="0" class="field">
                            </div>
                        </div>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Pricing & supplier</h6>
                            <p>Costs and sourcing notes</p>
                        </header>

                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="stock-purchase">Purchase price (per unit)</label>
                                <input id="stock-purchase" v-model.number="form.purchase_price" type="number" step="0.01" min="0" class="field">
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="stock-selling">Selling price (per unit)</label>
                                <input id="stock-selling" v-model.number="form.selling_price" type="number" step="0.01" min="0" class="field">
                            </div>
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="stock-supplier">Supplier</label>
                            <input id="stock-supplier" v-model="form.supplier" type="text" class="field" placeholder="Supplier name">
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="stock-notes">Notes</label>
                            <textarea id="stock-notes" v-model="form.notes" rows="3" class="field" placeholder="Additional notes…"></textarea>
                        </div>
                    </section>
                </div>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="stock-edit-form" class="btn btn-primary">Update stock</button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    stock: Object,
    medicines: Array,
})

const formatDateForInput = (date) => {
    if (!date) return ''
    const d = new Date(date)
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const medicineSearch = ref('')
const medicineSearchFocused = ref(false)
const medicineSearchResults = ref([])

const searchMedicines = () => {
    if (medicineSearch.value.length < 2) {
        medicineSearchResults.value = []
        return
    }

    const searchTerm = medicineSearch.value.toLowerCase()
    medicineSearchResults.value = props.medicines.filter(medicine =>
        medicine.name.toLowerCase().includes(searchTerm) ||
        medicine.generic_name.toLowerCase().includes(searchTerm)
    )
}

const selectMedicine = (medicine) => {
    form.value.medicine_id = medicine.id
    medicineSearch.value = `${medicine.name} - ${medicine.generic_name}`
    medicineSearchFocused.value = false
    medicineSearchResults.value = []
}

const handleBlur = () => {
    setTimeout(() => {
        medicineSearchFocused.value = false
    }, 200)
}

const form = ref({
    medicine_id: props.stock.medicine_id,
    batch_number: props.stock.batch_number || '',
    expiry_date: formatDateForInput(props.stock.expiry_date),
    quantity: props.stock.quantity,
    min_stock_level: props.stock.min_stock_level,
    max_stock_level: props.stock.max_stock_level || '',
    purchase_price: props.stock.purchase_price || '',
    selling_price: props.stock.selling_price || '',
    supplier: props.stock.supplier || '',
    notes: props.stock.notes || '',
    is_active: props.stock.is_active,
})

onMounted(() => {
    if (props.stock.medicine) {
        medicineSearch.value = `${props.stock.medicine.name} - ${props.stock.medicine.generic_name}`
    }
})

const submitForm = () => {
    router.put(route('stocks.update', props.stock.id), form.value)
}
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-form-grid {
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

.medicine-search {
    position: relative;
}

.medicine-search-dropdown {
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

.medicine-search-item {
    display: block;
    width: 100%;
    padding: 0.45rem 0.55rem;
    border: none;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
    background: transparent;
    text-align: left;
    cursor: pointer;
}

.medicine-search-item:last-child {
    border-bottom: none;
}

.medicine-search-item:hover {
    background: var(--shell-panel-bg, #f8f9fc);
}

.medicine-search-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
}

.medicine-search-meta {
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.medicine-search-meta.muted {
    font-size: 0.68rem;
    opacity: 0.85;
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
    .med-form-grid,
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
