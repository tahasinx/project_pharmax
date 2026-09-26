<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Medicine - {{ medicine.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.show', medicine.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Medicine
                    </Link>
                    <Link :href="route('medicines.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Medicines
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Global Validation Errors -->
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
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage form / ATC / SKU</label>
                                        <input v-model="form.dosage_form" placeholder="Dosage form" class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2">
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
                                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800">
                                                Code Management
                                            </h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p>Codes will be auto-generated if missing. Use the "Generate Codes" button to customize or regenerate codes.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between">
                                    <Link :href="route('medicines.codes', medicine.id)"
                                          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Generate Codes
                                    </Link>

                                    <div class="flex space-x-4">
                                        <Link :href="route('medicines.show', medicine.id)"
                                              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                            Cancel
                                        </Link>
                                        <button type="submit"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Update Medicine
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({
    medicine: Object,
    categories: Array,
    manufacturers: Array,
    generics: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
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

onMounted(() => {
    // Populate form with existing medicine data
    form.value = {
        name: props.medicine.name,
        generic_name: props.medicine.generic_name || '',
        generic_id: props.medicine.generic_id || '',
        brand_id: props.medicine.brand_id || '',
        dosage_form: props.medicine.dosage_form || '',
        atc_code: props.medicine.atc_code || '',
        sku: props.medicine.sku || '',
        requires_prescription: !!props.medicine.requires_prescription,
        is_controlled: !!props.medicine.is_controlled,
        is_antibiotic: !!props.medicine.is_antibiotic,
        is_high_risk: !!props.medicine.is_high_risk,
        is_refrigerated: !!props.medicine.is_refrigerated,
        is_narcotic: !!props.medicine.is_narcotic,
        units: props.medicine.units?.length ? props.medicine.units.map(unit => ({ name: unit.name, factor_to_base: unit.factor_to_base })) : [{ name: 'Piece', factor_to_base: 1 }],
        strength: props.medicine.strength || '',
        category_id: props.medicine.category_id,
        manufacturer_id: props.medicine.manufacturer_id,
        price: props.medicine.price,
        manufacturer_price: props.medicine.manufacturer_price,
        box_size: props.medicine.box_size,
        unit: props.medicine.unit || '',
        product_location: props.medicine.product_location || '',
        details: props.medicine.details || '',
        status: props.medicine.status
    }
})

const submitForm = () => {
    router.put(route('medicines.update', props.medicine.id), form.value, {
        onSuccess: () => {
            // Redirect to medicine show page
        }
    })
}
</script>
