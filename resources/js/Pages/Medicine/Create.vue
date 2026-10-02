<template>
    <AuthenticatedLayout>
        <FormScreen title="Add medicine" :close-href="route('medicines.index')">
                <ValidationErrors :errors="$page.props.errors" />

                <form @submit.prevent="submitForm">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Information -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name *</label>
                                        <input v-model="form.name"
                                               type="text"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Generic</label>
                                        <select v-model="form.generic_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                            <option value="">None</option>
                                            <option v-for="g in generics" :key="g.id" :value="g.id">{{ g.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                        <select v-model="form.brand_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                            <option value="">None</option>
                                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage form</label>
                                        <input v-model="form.dosage_form" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ATC / SKU</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input v-model="form.atc_code" placeholder="ATC" class="px-3 py-2 border border-gray-300 rounded-md">
                                            <input v-model="form.sku" placeholder="SKU" class="px-3 py-2 border border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                    <div class="text-sm space-y-1">
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.requires_prescription"> Prescription required</label>
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.is_controlled"> Controlled</label>
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.is_antibiotic"> Antibiotic</label>
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.is_high_risk"> High risk</label>
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.is_refrigerated"> Refrigerated</label>
                                        <label class="flex gap-2"><input type="checkbox" v-model="form.is_narcotic"> Narcotic</label>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label>
                                        <input v-model="form.generic_name"
                                               type="text"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Strength</label>
                                        <input v-model="form.strength"
                                               type="text"
                                               placeholder="e.g., 500mg, 10ml"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                        <SearchableSelect
                                            v-model="form.category_id"
                                            :options="categoryOptions"
                                            placeholder="Select Category"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer *</label>
                                        <SearchableSelect
                                            v-model="form.manufacturer_id"
                                            :options="manufacturerOptions"
                                            placeholder="Select Manufacturer"
                                        />
                                    </div>
                                </div>

                                <!-- Pricing & Inventory -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Inventory</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                                        <input v-model.number="form.price"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer Price *</label>
                                        <input v-model.number="form.manufacturer_price"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Box Size *</label>
                                        <input v-model.number="form.box_size"
                                               type="number"
                                               min="1"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input v-model="form.unit"
                                               type="text"
                                               placeholder="e.g., tablets, ml, mg"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Location</label>
                                        <input v-model="form.product_location"
                                               type="text"
                                               placeholder="e.g., Shelf A1, Refrigerator"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select v-model="form.status"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea v-model="form.details"
                                              rows="4"
                                              placeholder="Enter medicine description, usage instructions, side effects, etc."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8">
                                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">
                                                Automatic Code Generation
                                            </h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>QR codes and barcodes will be automatically generated for this medicine using the product ID.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <Link :href="route('medicines.index')" class="btn btn-light">Cancel</Link>
                                    <button type="submit" class="btn btn-primary">Create Medicine</button>
                                </div>
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
    brands: { type: Array, default: () => [] },
    errors: Object
})

// Formatted categories for SearchableSelect
const categoryOptions = computed(() => {
    let categories = [...props.categories]
    categories.sort((a, b) => a.name.localeCompare(b.name))
    return categories.map(category => ({
        value: category.id,
        label: category.name
    }))
})

// Formatted manufacturers for SearchableSelect
const manufacturerOptions = computed(() => {
    let manufacturers = [...props.manufacturers]
    manufacturers.sort((a, b) => a.name.localeCompare(b.name))
    return manufacturers.map(manufacturer => ({
        value: manufacturer.id,
        label: manufacturer.name
    }))
})

const form = ref({
    name: '',
    generic_name: '',
    generic_id: '',
    brand_id: '',
    dosage_form: '',
    atc_code: '',
    sku: '',
    requires_prescription: false,
    is_controlled: false,
    is_antibiotic: false,
    is_high_risk: false,
    is_refrigerated: false,
    is_narcotic: false,
    units: [
        { name: 'Box', factor_to_base: 100 },
        { name: 'Strip', factor_to_base: 10 },
        { name: 'Piece', factor_to_base: 1 },
    ],
    strength: '',
    category_id: '',
    manufacturer_id: '',
    price: 0,
    manufacturer_price: 0,
    box_size: 1,
    unit: '',
    product_location: '',
    details: '',
    status: true
})

const submitForm = () => {
    router.post(route('medicines.store'), form.value)
}
</script>
