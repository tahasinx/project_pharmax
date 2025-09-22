<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add New Purchase
                </h2>
                <Link :href="route('purchases.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Purchases
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Left Side - Purchase Information -->
                                <div class="lg:col-span-2 space-y-6">
                                    <!-- Basic Information -->
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-medium text-gray-900">Purchase Information</h3>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
                                                <input v-model="form.purchase_date"
                                                       type="date"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                       required>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                                            <textarea v-model="form.details"
                                                      rows="3"
                                                      placeholder="Enter purchase details..."
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                        </div>
                                    </div>

                                    <!-- Product Selection -->
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-medium text-gray-900">Add Products</h3>

                                        <div class="flex space-x-2">
                                            <input v-model="productSearch"
                                                   type="text"
                                                   placeholder="Search medicines..."
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <button type="button"
                                                    @click="searchProducts"
                                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                                Search
                                            </button>
                                        </div>

                                        <!-- Search Results -->
                                        <div v-if="productSearchResults.length > 0" class="border border-gray-300 rounded-md max-h-48 overflow-y-auto">
                                            <div v-for="product in productSearchResults" :key="product.id"
                                                 class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
                                                 @click="addProduct(product)">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <div class="font-medium">{{ product.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ product.generic_name }}</div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="font-medium">${{ parseFloat(product.price).toFixed(2) }}</div>
                                                        <div class="text-sm text-gray-500">{{ product.category?.name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cart Items -->
                                    <div v-if="cartItems.length > 0" class="space-y-4">
                                        <h3 class="text-lg font-medium text-gray-900">Purchase Items</h3>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="(item, index) in cartItems" :key="index">
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                                                            <div class="text-sm text-gray-500">{{ item.generic_name }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input v-model.number="item.quantity"
                                                                   type="number"
                                                                   min="1"
                                                                   class="w-20 px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                   @input="updateItemTotal(index)">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input v-model.number="item.rate"
                                                                   type="number"
                                                                   step="0.01"
                                                                   min="0"
                                                                   class="w-24 px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                   @input="updateItemTotal(index)">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            ${{ item.total.toFixed(2) }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <button type="button"
                                                                    @click="removeItem(index)"
                                                                    class="text-red-600 hover:text-red-900">
                                                                Remove
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Side - Summary -->
                                <div class="lg:col-span-1">
                                    <div class="bg-gray-50 p-6 rounded-lg sticky top-6">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Summary</h3>

                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Subtotal:</span>
                                                <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Tax (10%):</span>
                                                <span class="font-medium">${{ tax.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Discount:</span>
                                                <span class="font-medium">${{ discount.toFixed(2) }}</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="flex justify-between text-lg font-semibold">
                                                <span>Total:</span>
                                                <span>${{ total.toFixed(2) }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-6">
                                            <button type="submit"
                                                    :disabled="cartItems.length === 0"
                                                    class="w-full bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded">
                                                Create Purchase
                                            </button>
                                        </div>
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
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    manufacturers: Array,
    medicines: Array
})

const productSearch = ref('')
const productSearchResults = ref([])
const cartItems = ref([])

const form = ref({
    manufacturer_id: '',
    purchase_date: new Date().toISOString().split('T')[0],
    details: '',
    grand_total: 0,
    total_tax: 0,
    total_discount: 0,
    items: []
})

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.total, 0)
})

const tax = computed(() => {
    return subtotal.value * 0.1 // 10% tax
})

const discount = computed(() => {
    return 0 // Can be implemented later
})

const total = computed(() => {
    return subtotal.value + tax.value - discount.value
})

const searchProducts = () => {
    if (productSearch.value.length < 2) {
        productSearchResults.value = []
        return
    }

    const searchLower = productSearch.value.toLowerCase()
    productSearchResults.value = props.medicines.filter(medicine =>
        medicine.name.toLowerCase().includes(searchLower) ||
        medicine.generic_name?.toLowerCase().includes(searchLower)
    ).slice(0, 10)
}

const addProduct = (product) => {
    const existingItem = cartItems.value.find(item => item.medicine_id === product.id)

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
    } else {
        cartItems.value.push({
            medicine_id: product.id,
            name: product.name,
            generic_name: product.generic_name,
            quantity: 1,
            rate: product.manufacturer_price || product.price,
            total: product.manufacturer_price || product.price
        })
    }

    productSearch.value = ''
    productSearchResults.value = []
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = item.quantity * item.rate
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const submitForm = () => {
    if (cartItems.value.length === 0) {
        alert('Please add at least one product to the purchase.')
        return
    }

    form.value.grand_total = total.value
    form.value.total_tax = tax.value
    form.value.total_discount = discount.value
    form.value.items = cartItems.value

    router.post(route('purchases.store'), form.value, {
        onSuccess: () => {
            // Redirect to purchase list
        }
    })
}
</script>
