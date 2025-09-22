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
                                        <select v-model="form.category_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required>
                                            <option value="">Select Category</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer *</label>
                                        <select v-model="form.manufacturer_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required>
                                            <option value="">Select Manufacturer</option>
                                            <option v-for="manufacturer in manufacturers" :key="manufacturer.id" :value="manufacturer.id">
                                                {{ manufacturer.name }}
                                            </option>
                                        </select>
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
                            <div class="mt-8 flex justify-end space-x-4">
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
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    medicine: Object,
    categories: Array,
    manufacturers: Array
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
