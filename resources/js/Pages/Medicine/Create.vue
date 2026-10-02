<template>
    <AuthenticatedLayout>
        <FormScreen title="Add medicine" :close-href="route('medicines.index')">
            <ValidationErrors :errors="$page.props.errors" />

            <form @submit.prevent="submitForm">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="field-label">Brand Name <span class="req">*</span></label>
                                    <input v-model="form.name" type="text" class="field" required>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="field-label">Generic Name <span class="req">*</span></label>
                                        <select v-model="form.generic_id" class="field" required>
                                            <option value="">Select generic</option>
                                            <option v-for="g in generics" :key="g.id" :value="g.id">{{ g.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="field-label">Strength <span class="req">*</span></label>
                                        <input v-model="form.strength" type="text" placeholder="e.g. 500mg" class="field" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="field-label">Category</label>
                                    <SearchableSelect
                                        v-model="form.category_id"
                                        :options="categoryOptions"
                                        placeholder="Select Category"
                                    />
                                </div>

                                <div>
                                    <label class="field-label">Medicine Type <span class="req">*</span></label>
                                    <select v-model="form.medicine_type_id" class="field" required>
                                        <option value="">Select type</option>
                                        <option v-for="type in medicineTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="field-label">Manufacturer / Company</label>
                                    <SearchableSelect
                                        v-model="form.manufacturer_id"
                                        :options="manufacturerOptions"
                                        placeholder="Select manufacturer"
                                    />
                                </div>

                                <div>
                                    <label class="field-label">Unit <span class="req">*</span></label>
                                    <select v-model="form.unit" class="field" required>
                                        <option value="">Select unit</option>
                                        <option v-for="unit in units" :key="unit.id" :value="unit.name">{{ unit.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="field-label">Barcode</label>
                                    <input v-model="form.barcode" type="text" class="field" placeholder="Leave blank to generate">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="field-label">Supplier Box Price (TP) <span class="req">*</span></label>
                                    <input v-model.number="form.manufacturer_price" type="number" step="0.01" min="0" class="field" required>
                                </div>

                                <div>
                                    <label class="field-label">Box MRP <span class="req">*</span></label>
                                    <input v-model.number="form.price" type="number" step="0.01" min="0" class="field" required>
                                </div>

                                <div>
                                    <label class="field-label">Unit Price</label>
                                    <input :value="unitPrice" type="text" class="field bg-gray-50" readonly>
                                </div>

                                <div>
                                    <label class="field-label">Discount %</label>
                                    <div class="flex items-center gap-3">
                                        <input v-model.number="form.discount_percent" type="number" step="0.01" min="0" max="100" class="field" :disabled="!discountApplied">
                                        <button type="button" class="switch" :class="{ 'is-on': discountApplied }" role="switch" :aria-checked="discountApplied" aria-label="Apply discount" @click="discountApplied = !discountApplied">
                                            <span class="switch-knob"></span>
                                        </button>
                                        <span class="switch-state" :class="{ 'is-on': discountApplied }">{{ discountApplied ? 'On' : 'Off' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ discountApplied ? 'Discount is applied to the unit price.' : 'Discount is off. Unit price stays the box MRP.' }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Alert QTY</label>
                                    <div class="flex items-center gap-3">
                                        <input v-model.number="form.alert_qty" type="number" min="0" class="field">
                                        <button type="button" class="switch" :class="{ 'is-on': form.manage_stock }" role="switch" :aria-checked="form.manage_stock" aria-label="Manage stock after save" @click="form.manage_stock = !form.manage_stock">
                                            <span class="switch-knob"></span>
                                        </button>
                                        <span class="switch-state" :class="{ 'is-on': form.manage_stock }">{{ form.manage_stock ? 'On' : 'Off' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">{{ form.manage_stock ? 'After save, the stock page opens for this medicine.' : 'Stock page stays closed. Only the alert quantity is saved.' }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Rack/Shelf Location</label>
                                    <input v-model="form.product_location" type="text" class="field" placeholder="e.g. Shelf A1">
                                </div>

                                <div>
                                    <label class="field-label">Image</label>
                                    <input type="file" accept="image/*" class="field" @change="onImage">
                                    <img v-if="imagePreview" :src="imagePreview" alt="Medicine preview" class="mt-2 h-20 w-20 rounded object-cover border">
                                </div>

                                <div>
                                    <label class="field-label">Status</label>
                                    <div class="flex items-center gap-3">
                                        <button type="button" class="switch" :class="{ 'is-on': form.status }" role="switch" :aria-checked="form.status" aria-label="Medicine status" @click="form.status = !form.status">
                                            <span class="switch-knob"></span>
                                        </button>
                                        <span class="switch-state" :class="{ 'is-on': form.status }">{{ form.status ? 'Active' : 'Inactive' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="field-label">Description</label>
                            <textarea v-model="form.details" rows="4" class="field" placeholder="Description"></textarea>
                        </div>

                        <div class="mt-8 d-flex justify-content-end gap-2">
                            <Link :href="route('medicines.index')" class="btn btn-light">Cancel</Link>
                            <button type="submit" class="btn btn-primary">Create Medicine</button>
                        </div>
                    </div>
                </div>
            </form>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'

const props = defineProps({
    categories: Array,
    manufacturers: Array,
    generics: { type: Array, default: () => [] },
    medicineTypes: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
})

const categoryOptions = computed(() => [...props.categories]
    .sort((a, b) => a.name.localeCompare(b.name))
    .map(category => ({ value: category.id, label: category.name })))

const manufacturerOptions = computed(() => [...props.manufacturers]
    .sort((a, b) => a.name.localeCompare(b.name))
    .map(manufacturer => ({ value: manufacturer.id, label: manufacturer.name })))

const form = ref({
    name: '',
    generic_id: '',
    strength: '',
    category_id: '',
    medicine_type_id: '',
    manufacturer_id: '',
    unit: '',
    barcode: '',
    manufacturer_price: 0,
    price: 0,
    discount_percent: 0,
    alert_qty: 0,
    product_location: '',
    details: '',
    image: null,
    box_size: 1,
    status: true,
    manage_stock: false,
    units: [
        { name: 'Box', factor_to_base: 100 },
        { name: 'Strip', factor_to_base: 10 },
        { name: 'Piece', factor_to_base: 1 },
    ],
})

const discountApplied = ref(false)
const imagePreview = ref('')

const unitPrice = computed(() => {
    const mrp = Number(form.value.price) || 0
    const size = Number(form.value.box_size) || 1
    const discount = discountApplied.value ? (Number(form.value.discount_percent) || 0) : 0
    const value = (mrp / size) * (1 - Math.min(discount, 100) / 100)
    return value.toFixed(2)
})

const onImage = (event) => {
    const file = event.target.files?.[0] || null
    form.value.image = file
    imagePreview.value = file ? URL.createObjectURL(file) : ''
}

const submitForm = () => {
    const payload = { ...form.value, manage_stock: form.value.manage_stock }
    if (!discountApplied.value) {
        payload.discount_percent = 0
    }
    if (!(payload.image instanceof File)) {
        delete payload.image
    }
    router.post(route('medicines.store'), payload, { forceFormData: true })
}
</script>

<style scoped>
.field-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}
.req {
    color: #dc2626;
    font-weight: 700;
}
.field {
    width: 100%;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    padding: 0.5rem 0.75rem;
}
.field:focus {
    outline: none;
    box-shadow: 0 0 0 2px #3b82f6;
}
.switch {
    position: relative;
    height: 1.75rem;
    width: 3rem;
    flex-shrink: 0;
    border-radius: 999px;
    background: #d1d5db;
    transition: background 0.15s ease;
}
.switch.is-on {
    background: #16a34a;
}
.switch-knob {
    position: absolute;
    top: 0.2rem;
    left: 0.2rem;
    height: 1.35rem;
    width: 1.35rem;
    border-radius: 999px;
    background: #fff;
    transition: transform 0.15s ease;
}
.switch.is-on .switch-knob {
    transform: translateX(1.25rem);
}
.switch-state {
    width: 1.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #9ca3af;
}
.switch-state.is-on {
    color: #15803d;
}
</style>
