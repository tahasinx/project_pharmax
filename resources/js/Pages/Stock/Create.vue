<template>
    <Head title="Add Stock" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add Stock
                </h2>
                <Link :href="route('stocks.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Stock
                </Link>
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
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine *</label>
                                        <div class="relative">
                                            <input v-model="medicineSearch"
                                                   @input="searchMedicines"
                                                   @focus="medicineSearchFocused = true"
                                                   @blur="handleBlur"
                                                   type="text"
                                                   placeholder="Search medicine..."
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   required>

                                            <!-- Search Results Dropdown -->
                                            <div v-if="medicineSearchFocused && medicineSearchResults.length > 0"
                                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                                <div v-for="medicine in medicineSearchResults"
                                                     :key="medicine.id"
                                                     @mousedown="selectMedicine(medicine)"
                                                     class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                                    <div class="font-medium text-gray-900">{{ medicine.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ medicine.generic_name }}</div>
                                                    <div class="text-xs text-gray-400">{{ medicine.category?.name || 'No Category' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                                        <input v-model="form.batch_number"
                                               type="text"
                                               placeholder="e.g., BATCH001"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                                        <input v-model="form.expiry_date"
                                               type="date"
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                                        <input v-model.number="form.quantity"
                                               type="number"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level *</label>
                                        <input v-model.number="form.min_stock_level"
                                               type="number"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Stock Level</label>
                                        <input v-model.number="form.max_stock_level"
                                               type="number"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>

                                <!-- Pricing & Supplier Information -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Supplier</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (per unit)</label>
                                        <input v-model.number="form.purchase_price"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (per unit)</label>
                                        <input v-model.number="form.selling_price"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                                        <input v-model="form.supplier"
                                               type="text"
                                               placeholder="Supplier name"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea v-model="form.notes"
                                                  rows="3"
                                                  placeholder="Additional notes..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="flex items-center">
                                        <input v-model="form.is_active"
                                               type="checkbox"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label class="ml-2 text-sm text-gray-900">Active</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <Link :href="route('stocks.index')"
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Add Stock
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
import { ref } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    medicines: Array
})

const form = ref({
    medicine_id: '',
    batch_number: '',
    expiry_date: '', // Required field - no default value
    quantity: 0,
    min_stock_level: 10,
    max_stock_level: '',
    purchase_price: '',
    selling_price: '',
    supplier: '',
    notes: '',
    is_active: true
})

// Medicine search functionality
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
    // Delay hiding the dropdown to allow click events to fire
    setTimeout(() => {
        medicineSearchFocused.value = false
    }, 200)
}

const submitForm = () => {
    router.post(route('stocks.store'), form.value, {
        onSuccess: () => {
            // Redirect to stocks index
        }
    })
}
</script>
