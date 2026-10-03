<template>
    <AuthenticatedLayout>
        <FormScreen title="Edit medicine" :close-href="route('medicines.index')">
            <template #header-actions>
                <button
                    type="button"
                    class="status-chip"
                    :class="{ 'is-on': form.status }"
                    role="switch"
                    :aria-checked="form.status"
                    @click="form.status = !form.status"
                >
                    <span class="status-dot" />
                    {{ form.status ? 'Active' : 'Inactive' }}
                </button>
            </template>

            <div v-if="hasErrors" class="med-alert">
                <strong>Fix the highlighted fields</strong>
                <ul>
                    <li v-for="(message, key) in flatErrors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <form id="medicine-edit-form" class="med-form" novalidate @submit.prevent="submitForm">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Product identity</h6>
                            <p>Catalog identity and classification</p>
                        </header>

                        <div class="med-field" :class="{ 'has-error': fieldError('name') }">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <label class="field-label mb-0" for="med-name">Medicine name <span class="req">*</span></label>
                                <Link
                                    :href="route('medicines.index', { open_api: 1 })"
                                    class="btn btn-sm btn-primary api-lookup-btn"
                                >
                                    <i class="bi bi-cloud-download me-1" />
                                    Reference Catalog
                                </Link>
                            </div>
                            <input
                                id="med-name"
                                v-model="form.name"
                                type="text"
                                class="field"
                                placeholder="Enter medicine / brand name"
                                autocomplete="off"
                                @input="clearError('name')"
                            >
                            <p v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</p>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field" :class="{ 'has-error': fieldError('generic_id') }">
                                <label class="field-label">Generic name <span class="req">*</span></label>
                                <SearchableSelect
                                    v-model="form.generic_id"
                                    :options="genericOptions"
                                    placeholder="Search generic…"
                                    required
                                    :invalid="!!fieldError('generic_id')"
                                    @change="clearError('generic_id')"
                                />
                                <p v-if="fieldError('generic_id')" class="field-error">{{ fieldError('generic_id') }}</p>
                            </div>
                            <div class="med-field" :class="{ 'has-error': fieldError('strength') }">
                                <label class="field-label" for="med-strength">Strength <span class="req">*</span></label>
                                <input id="med-strength" v-model="form.strength" type="text" class="field" placeholder="e.g. 500 mg" @input="clearError('strength')">
                                <p v-if="fieldError('strength')" class="field-error">{{ fieldError('strength') }}</p>
                            </div>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field" :class="{ 'has-error': fieldError('category_id') }">
                                <label class="field-label">Category</label>
                                <SearchableSelect
                                    v-model="form.category_id"
                                    :options="categoryOptions"
                                    placeholder="Search category…"
                                    :invalid="!!fieldError('category_id')"
                                    @change="clearError('category_id')"
                                />
                                <p v-if="fieldError('category_id')" class="field-error">{{ fieldError('category_id') }}</p>
                            </div>
                            <div class="med-field" :class="{ 'has-error': fieldError('medicine_type_id') }">
                                <label class="field-label">Segment <span class="req">*</span></label>
                                <SearchableSelect
                                    v-model="form.medicine_type_id"
                                    :options="typeOptions"
                                    placeholder="Allopathic / Herbal / Device…"
                                    required
                                    :invalid="!!fieldError('medicine_type_id')"
                                    @change="clearError('medicine_type_id')"
                                />
                                <p v-if="fieldError('medicine_type_id')" class="field-error">{{ fieldError('medicine_type_id') }}</p>
                            </div>
                        </div>

                        <div class="med-field" :class="{ 'has-error': fieldError('manufacturer_id') }">
                            <label class="field-label">Manufacturer</label>
                            <SearchableSelect
                                v-model="form.manufacturer_id"
                                :options="manufacturerOptions"
                                placeholder="Search manufacturer…"
                                :invalid="!!fieldError('manufacturer_id')"
                                @change="clearError('manufacturer_id')"
                            />
                            <p v-if="fieldError('manufacturer_id')" class="field-error">{{ fieldError('manufacturer_id') }}</p>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field" :class="{ 'has-error': fieldError('unit') }">
                                <label class="field-label">Unit <span class="req">*</span></label>
                                <SearchableSelect
                                    v-model="form.unit"
                                    :options="unitOptions"
                                    placeholder="Search unit…"
                                    required
                                    :invalid="!!fieldError('unit')"
                                    @change="clearError('unit')"
                                />
                                <p v-if="fieldError('unit')" class="field-error">{{ fieldError('unit') }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label">Dosage form</label>
                                <SearchableSelect
                                    v-model="form.dosage_form_id"
                                    :options="dosageFormOptions"
                                    placeholder="Tablet, syrup…"
                                />
                            </div>
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="med-barcode">Barcode</label>
                            <input id="med-barcode" v-model="form.barcode" type="text" class="field" placeholder="Auto if blank">
                        </div>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Pricing & stock</h6>
                            <p>Live unit price follows MRP, box size, and discount</p>
                        </header>

                        <div class="med-row med-row-3">
                            <div class="med-field" :class="{ 'has-error': fieldError('manufacturer_price') }">
                                <label class="field-label" for="med-tp">Box TP <span class="req">*</span></label>
                                <input id="med-tp" v-model.number="form.manufacturer_price" type="number" step="0.01" min="0" class="field" @input="clearError('manufacturer_price')">
                                <p v-if="fieldError('manufacturer_price')" class="field-error">{{ fieldError('manufacturer_price') }}</p>
                            </div>
                            <div class="med-field" :class="{ 'has-error': fieldError('price') }">
                                <label class="field-label" for="med-mrp">Box MRP <span class="req">*</span></label>
                                <input id="med-mrp" v-model.number="form.price" type="number" step="0.01" min="0" class="field" @input="clearError('price')">
                                <p v-if="fieldError('price')" class="field-error">{{ fieldError('price') }}</p>
                            </div>
                            <div class="med-field" :class="{ 'has-error': fieldError('box_size') }">
                                <label class="field-label" for="med-box">Box size <span class="req">*</span></label>
                                <input id="med-box" v-model.number="form.box_size" type="number" min="1" step="1" class="field" @input="clearError('box_size')">
                                <p v-if="fieldError('box_size')" class="field-error">{{ fieldError('box_size') }}</p>
                            </div>
                        </div>

                        <div class="med-metrics">
                            <div class="med-metric">
                                <span class="med-metric-label">Unit price</span>
                                <strong>{{ unitPrice }}</strong>
                            </div>
                            <div class="med-metric" :class="{ accent: profitAmount > 0 }">
                                <span class="med-metric-label">Box profit</span>
                                <strong>{{ profitLabel }}</strong>
                            </div>
                        </div>

                        <div class="med-toggle-field">
                            <div class="med-field" :class="{ 'has-error': fieldError('discount_percent') }">
                                <div class="med-label-row">
                                    <label class="field-label" for="med-discount">Discount %</label>
                                    <button
                                        type="button"
                                        class="switch"
                                        :class="{ 'is-on': discountApplied }"
                                        role="switch"
                                        :aria-checked="discountApplied"
                                        :aria-label="discountApplied ? 'Discount on' : 'Discount off'"
                                        @click="toggleDiscount"
                                    >
                                        <span class="switch-knob" />
                                    </button>
                                </div>
                                <input
                                    id="med-discount"
                                    v-model.number="form.discount_percent"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="field"
                                    :disabled="!discountApplied"
                                    @input="clearError('discount_percent')"
                                >
                                <p class="med-hint">{{ discountApplied ? 'Discount reduces unit price.' : 'Discount off.' }}</p>
                                <p v-if="fieldError('discount_percent')" class="field-error">{{ fieldError('discount_percent') }}</p>
                            </div>
                        </div>

                        <div class="med-toggle-field">
                            <div class="med-field">
                                <div class="med-label-row">
                                    <label class="field-label" for="med-alert">Alert qty</label>
                                    <button
                                        type="button"
                                        class="switch"
                                        :class="{ 'is-on': form.manage_stock }"
                                        role="switch"
                                        :aria-checked="form.manage_stock"
                                        :aria-label="form.manage_stock ? 'Manage stock on' : 'Manage stock off'"
                                        @click="form.manage_stock = !form.manage_stock"
                                    >
                                        <span class="switch-knob" />
                                    </button>
                                </div>
                                <input id="med-alert" v-model.number="form.alert_qty" type="number" min="0" class="field">
                                <p class="med-hint">{{ form.manage_stock ? 'Opens stock after save.' : 'Save alert qty only.' }}</p>
                            </div>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="med-rack">Rack / shelf</label>
                                <input id="med-rack" v-model="form.product_location" type="text" class="field" placeholder="Shelf A1">
                            </div>
                            <div class="med-field" :class="{ 'has-error': fieldError('image') }">
                                <label class="field-label" for="med-image">Image</label>
                                <div class="med-upload">
                                    <label class="med-upload-control" for="med-image">
                                        <span class="med-upload-btn">Choose file</span>
                                        <span class="med-upload-name">{{ imageName || 'No file chosen' }}</span>
                                    </label>
                                    <input
                                        id="med-image"
                                        type="file"
                                        accept="image/*"
                                        class="med-upload-input"
                                        @change="onImage"
                                    >
                                    <div v-if="imagePreview" class="med-upload-preview-wrap">
                                        <img :src="imagePreview" alt="" class="med-upload-preview">
                                        <button type="button" class="med-upload-clear" title="Remove image" @click="clearImage">
                                            <i class="bi bi-x" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                                <p v-if="fieldError('image')" class="field-error">{{ fieldError('image') }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Compliance</h6>
                        <p>Prescription and controlled-substance flags</p>
                    </header>
                    <div class="comp-flags">
                        <button type="button" class="flag-switch" :class="{ 'is-on': form.requires_prescription }" role="switch" :aria-checked="form.requires_prescription" @click="form.requires_prescription = !form.requires_prescription">
                            <span class="switch" :class="{ 'is-on': form.requires_prescription }"><span class="switch-knob" /></span>
                            <span>
                                <strong>Requires prescription</strong>
                                <small>Rx-only at POS</small>
                            </span>
                        </button>
                        <button type="button" class="flag-switch" :class="{ 'is-on': form.is_controlled }" role="switch" :aria-checked="form.is_controlled" @click="form.is_controlled = !form.is_controlled">
                            <span class="switch" :class="{ 'is-on': form.is_controlled }"><span class="switch-knob" /></span>
                            <span>
                                <strong>Controlled medicine</strong>
                                <small>Logged in controlled register</small>
                            </span>
                        </button>
                        <button type="button" class="flag-switch" :class="{ 'is-on': form.is_narcotic }" role="switch" :aria-checked="form.is_narcotic" @click="form.is_narcotic = !form.is_narcotic">
                            <span class="switch" :class="{ 'is-on': form.is_narcotic }"><span class="switch-knob" /></span>
                            <span>
                                <strong>Narcotic</strong>
                                <small>Stricter controlled tracking</small>
                            </span>
                        </button>
                    </div>
                </section>

                <section class="med-panel med-panel-notes">
                    <header class="med-panel-head">
                        <h6>Notes</h6>
                        <p>Optional staff description</p>
                    </header>
                    <textarea v-model="form.details" rows="2" class="field" placeholder="Short description…"></textarea>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="medicine-edit-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Update medicine' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
    medicine: Object,
    categories: Array,
    manufacturers: Array,
    generics: { type: Array, default: () => [] },
    medicineTypes: { type: Array, default: () => [] },
    dosageForms: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
})

const mapOptions = (items, valueKey = 'id', labelKey = 'name') => [...(items || [])]
    .sort((a, b) => String(a[labelKey] || '').localeCompare(String(b[labelKey] || '')))
    .map((item) => ({ value: item[valueKey], label: item[labelKey] }))

const categoryOptions = computed(() => mapOptions(props.categories))
const manufacturerOptions = computed(() => mapOptions(props.manufacturers))
const genericOptions = computed(() => mapOptions(props.generics))
const typeOptions = computed(() => mapOptions(props.medicineTypes))
const dosageFormOptions = computed(() => mapOptions(props.dosageForms))
const unitOptions = computed(() => {
    const options = mapOptions(props.units, 'name', 'name')
    const current = props.medicine.unit
    if (current && !options.some((option) => String(option.value) === String(current))) {
        options.unshift({ value: current, label: current })
    }
    return options
})

const storedImage = (path) => {
    if (!path) return ''
    if (path.startsWith('http') || path.startsWith('/')) return path
    return `/storage/${path}`
}

const form = useForm({
    name: props.medicine.name || '',
    generic_id: props.medicine.generic_id || '',
    strength: props.medicine.strength || '',
    category_id: props.medicine.category_id || '',
    medicine_type_id: props.medicine.medicine_type_id || '',
    dosage_form_id: props.medicine.dosage_form_id || '',
    manufacturer_id: props.medicine.manufacturer_id || '',
    unit: props.medicine.unit || '',
    barcode: props.medicine.barcode_data || '',
    manufacturer_price: Number(props.medicine.manufacturer_price) || 0,
    price: Number(props.medicine.price) || 0,
    box_size: Number(props.medicine.box_size) || 1,
    discount_percent: Number(props.medicine.discount_percent) || 0,
    alert_qty: Number(props.medicine.alert_qty) || 0,
    product_location: props.medicine.product_location || '',
    details: props.medicine.details || '',
    image: null,
    status: props.medicine.status === true || props.medicine.status === 1,
    manage_stock: false,
    requires_prescription: props.medicine.requires_prescription === true || props.medicine.requires_prescription === 1,
    is_controlled: props.medicine.is_controlled === true || props.medicine.is_controlled === 1,
    is_narcotic: props.medicine.is_narcotic === true || props.medicine.is_narcotic === 1,
})

const discountApplied = ref(Number(props.medicine.discount_percent) > 0)
const imagePreview = ref(storedImage(props.medicine.image))
const imageName = ref('')
const localErrors = ref({})

const fieldError = (key) => localErrors.value[key] || form.errors[key] || ''

const flatErrors = computed(() => {
    const merged = { ...form.errors, ...localErrors.value }
    return Object.fromEntries(Object.entries(merged).filter(([, value]) => value))
})

const hasErrors = computed(() => Object.keys(flatErrors.value).length > 0)

const unitPrice = computed(() => {
    const mrp = Number(form.price) || 0
    const size = Math.max(1, Number(form.box_size) || 1)
    const discount = discountApplied.value ? Math.min(100, Math.max(0, Number(form.discount_percent) || 0)) : 0
    return ((mrp / size) * (1 - discount / 100)).toFixed(2)
})

const profitAmount = computed(() => (Number(form.price) || 0) - (Number(form.manufacturer_price) || 0))

const profitLabel = computed(() => {
    const tp = Number(form.manufacturer_price) || 0
    const profit = profitAmount.value
    const pct = tp > 0 ? ((profit / tp) * 100).toFixed(1) : '0.0'
    return `${profit.toFixed(2)} (${pct}%)`
})

const clearError = (key) => {
    if (localErrors.value[key]) {
        const next = { ...localErrors.value }
        delete next[key]
        localErrors.value = next
    }
    if (form.errors[key]) {
        form.clearErrors(key)
    }
}

const toggleDiscount = () => {
    discountApplied.value = !discountApplied.value
    if (!discountApplied.value) {
        form.discount_percent = 0
        clearError('discount_percent')
    }
}

const onImage = (event) => {
    const file = event.target.files?.[0] || null
    form.image = file
    imageName.value = file?.name || ''
    imagePreview.value = file ? URL.createObjectURL(file) : storedImage(props.medicine.image)
    clearError('image')
}

const clearImage = () => {
    form.image = null
    imageName.value = ''
    imagePreview.value = storedImage(props.medicine.image)
    const input = document.getElementById('med-image')
    if (input) input.value = ''
    clearError('image')
}

const validate = () => {
    const errors = {}
    if (!String(form.name || '').trim()) errors.name = 'Medicine name is required.'
    if (!form.generic_id) errors.generic_id = 'Generic name is required.'
    if (!String(form.strength || '').trim()) errors.strength = 'Strength is required.'
    if (!form.medicine_type_id) errors.medicine_type_id = 'Medicine type is required.'
    if (!form.unit) errors.unit = 'Unit is required.'
    if (form.manufacturer_price === '' || form.manufacturer_price === null || Number(form.manufacturer_price) < 0) {
        errors.manufacturer_price = 'Box TP must be 0 or greater.'
    }
    if (form.price === '' || form.price === null || Number(form.price) < 0) {
        errors.price = 'Box MRP must be 0 or greater.'
    }
    if (!form.box_size || Number(form.box_size) < 1) errors.box_size = 'Box size must be at least 1.'
    if (discountApplied.value) {
        const discount = Number(form.discount_percent)
        if (Number.isNaN(discount) || discount < 0 || discount > 100) {
            errors.discount_percent = 'Discount must be between 0 and 100.'
        }
    }
    if (form.image && !(form.image instanceof File)) {
        errors.image = 'Choose a valid image file.'
    }
    localErrors.value = errors
    return Object.keys(errors).length === 0
}

const submitForm = () => {
    if (!validate()) return

    form.transform((data) => {
        const payload = { ...data }
        if (!discountApplied.value) payload.discount_percent = 0
        if (!(payload.image instanceof File)) delete payload.image
        payload.box_size = Math.max(1, Number(payload.box_size) || 1)
        payload.alert_qty = Math.max(0, Number(payload.alert_qty) || 0)
        payload.status = payload.status ? 1 : 0
        payload.manage_stock = payload.manage_stock ? 1 : 0
        payload.requires_prescription = payload.requires_prescription ? 1 : 0
        payload.is_controlled = payload.is_controlled ? 1 : 0
        payload.is_narcotic = payload.is_narcotic ? 1 : 0
        return payload
    }).put(route('medicines.update', props.medicine.id), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
    })
}

watch(discountApplied, (on) => {
    if (!on) form.discount_percent = 0
})
</script>

<style scoped>
.api-lookup-btn {
    font-weight: 650;
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 81, 86, 190), 0.18);
}

.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.med-form-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 0.75rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.55rem);
    padding: 0.75rem 0.85rem 0.85rem;
}

.med-panel-notes {
    background: var(--shell-panel-surface, #fff);
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

.med-field.grow {
    flex: 1;
    min-width: 0;
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

.med-row-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    height: 2rem;
    min-height: 2rem;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0 0.55rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.field:disabled,
.field-readonly {
    background: color-mix(in srgb, var(--shell-panel-bg, #eef0f5) 85%, #fff);
    color: var(--shell-panel-muted, #74788d);
}

.field-file {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1.2;
}

textarea.field {
    height: auto;
    min-height: 4.5rem;
    padding: 0.4rem 0.55rem;
    line-height: 1.35;
}

.has-error .field {
    border-color: #f46a6a;
}

.field-error {
    margin: 0.2rem 0 0;
    font-size: 0.7rem;
    color: #f46a6a;
}

.med-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.45rem;
    margin-bottom: 0.55rem;
}

.med-metric {
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.4rem);
    padding: 0.45rem 0.55rem;
}

.med-metric.accent {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
}

.med-metric-label {
    display: block;
    margin-bottom: 0.1rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-metric strong {
    font-size: 0.92rem;
    color: var(--shell-panel-text, #343747);
}

.med-metric.accent strong {
    color: var(--shell-panel-accent-text, #1e8f68);
}

.med-upload {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    min-width: 0;
}

.med-upload-control {
    display: flex;
    align-items: stretch;
    flex: 1 1 auto;
    min-width: 0;
    height: var(--app-control-h, 2rem);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.3rem);
    background: var(--shell-panel-bg, #fff);
    overflow: hidden;
    cursor: pointer;
}

.med-upload-btn {
    display: inline-flex;
    align-items: center;
    flex: 0 0 auto;
    padding: 0 0.7rem;
    border-right: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-muted-bg, #f3f5f9);
    color: var(--shell-panel-text, #343747);
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
}

.med-upload-name {
    display: block;
    flex: 1 1 auto;
    min-width: 0;
    padding: 0 0.65rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.78rem;
    line-height: var(--app-control-h, 2rem);
    color: var(--shell-panel-muted, #74788d);
}

.med-upload-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.med-upload-preview-wrap {
    position: relative;
    flex: 0 0 auto;
    width: 2rem;
    height: 2rem;
}

.med-upload-preview {
    width: 2rem;
    height: 2rem;
    border-radius: var(--pf-radius, 0.3rem);
    object-fit: cover;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    display: block;
}

.med-upload-clear {
    position: absolute;
    top: -0.35rem;
    right: -0.35rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1rem;
    height: 1rem;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: #f46a6a;
    color: #fff;
    font-size: 0.7rem;
    line-height: 1;
    cursor: pointer;
}

.med-toggle-field {
    margin-bottom: 0.55rem;
}

.med-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.2rem;
}

.med-label-row .field-label {
    margin-bottom: 0;
}

.med-hint {
    margin: 0.2rem 0 0;
    font-size: 0.68rem;
    color: var(--shell-panel-muted, #74788d);
}

.switch {
    position: relative;
    height: 1.2rem;
    width: 2.1rem;
    flex-shrink: 0;
    border: 1px solid #c5cad3;
    border-radius: 999px;
    background: #dfe3ea;
    padding: 0;
}

.switch.is-on {
    background: #34c38f;
    border-color: #2ea974;
}

.switch-knob {
    position: absolute;
    top: 1px;
    left: 1px;
    height: calc(1.2rem - 4px);
    width: calc(1.2rem - 4px);
    border-radius: 999px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.18);
    transition: transform 0.15s ease;
}

.switch.is-on .switch-knob {
    transform: translateX(0.9rem);
}

.comp-flags {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.55rem;
}

.flag-switch {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    width: 100%;
    text-align: left;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-surface, #fff);
    border-radius: var(--pf-radius, 0.5rem);
    padding: 0.55rem 0.65rem;
    cursor: pointer;
}

.flag-switch.is-on {
    border-color: var(--shell-panel-accent-border, #cfd3f5);
    background: var(--shell-panel-accent-bg, #eef0fb);
}

.flag-switch strong {
    display: block;
    font-size: 0.78rem;
    color: var(--shell-panel-text, #343747);
}

.flag-switch small {
    display: block;
    margin-top: 0.1rem;
    font-size: 0.68rem;
    color: var(--shell-panel-muted, #74788d);
}

@media (max-width: 991.98px) {
    .comp-flags {
        grid-template-columns: 1fr;
    }
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

.med-alert {
    margin-bottom: 0.75rem;
    border: 1px solid #f5c2c7;
    background: #f8d7da;
    color: #842029;
    border-radius: var(--pf-radius, 0.45rem);
    padding: 0.55rem 0.7rem;
    font-size: 0.78rem;
}

.med-alert ul {
    margin: 0.35rem 0 0;
    padding-left: 1rem;
}

@media (max-width: 991.98px) {
    .med-form-grid,
    .med-row-2,
    .med-row-3,
    .med-metrics {
        grid-template-columns: 1fr;
    }
}
</style>
