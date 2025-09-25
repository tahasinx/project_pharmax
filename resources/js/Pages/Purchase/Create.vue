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
                                                <div class="relative">
                                                    <input v-model="manufacturerSearch"
                                                           @input="searchManufacturers"
                                                           @focus="showManufacturerResults = true"
                                                           type="text"
                                                           placeholder="Search manufacturers..."
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                           :class="{ 'border-red-500': !selectedManufacturer && manufacturerSearch.length > 0 }">

                                                    <!-- Manufacturer Search Results -->
                                                    <div v-if="showManufacturerResults && manufacturerSearchResults.length > 0"
                                                         class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                                        <div v-for="manufacturer in manufacturerSearchResults" :key="manufacturer.id"
                                                             class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
                                                             @click="selectManufacturer(manufacturer)">
                                                            <div class="font-medium">{{ manufacturer.name }}</div>
                                                            <div v-if="manufacturer.email" class="text-sm text-gray-500">{{ manufacturer.email }}</div>
                                                            <div v-if="manufacturer.mobile" class="text-sm text-gray-500">{{ manufacturer.mobile }}</div>
                                                        </div>
                                                    </div>

                                                    <!-- Selected Manufacturer Display -->
                                                    <div v-if="selectedManufacturer" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                                        <div class="flex justify-between items-center">
                                                            <div>
                                                                <div class="font-medium text-blue-900">{{ selectedManufacturer.name }}</div>
                                                                <div v-if="selectedManufacturer.email" class="text-sm text-blue-700">{{ selectedManufacturer.email }}</div>
                                                            </div>
                                                            <button type="button" @click="clearManufacturer" class="text-blue-600 hover:text-blue-800">
                                                                ✕
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Validation Message -->
                                                    <div v-if="!selectedManufacturer && manufacturerSearch.length > 0" class="mt-1 text-sm text-red-600">
                                                        Please select a manufacturer from the dropdown
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
                                                <input v-model="form.purchase_date"
                                                       type="date"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Chalan No</label>
                                                <input v-model="form.chalan_no"
                                                       type="text"
                                                       placeholder="Auto-generated if empty"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
                                                <select v-model="form.payment_type"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        required>
                                                    <option value="cash">Cash</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                                                <input v-model="form.paid_amount"
                                                       type="number"
                                                       step="0.01"
                                                       min="0"
                                                       placeholder="0.00"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Amount</label>
                                                <input :value="dueAmount.toFixed(2)"
                                                       type="number"
                                                       step="0.01"
                                                       readonly
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
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

                                        <div class="relative">
                                            <input v-model="productSearch"
                                                   @input="searchProducts"
                                                   type="text"
                                                   placeholder="Search medicines..."
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                                        <div class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(product.price).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</div>
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
                                                            {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ item.total.toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
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
                                                <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ subtotal.toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Tax (10%):</span>
                                                <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ tax.toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Discount:</span>
                                                <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ discount.toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="flex justify-between text-lg font-semibold">
                                                <span>Total:</span>
                                                <span>{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ total.toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
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

// Manufacturer search
const manufacturerSearch = ref('')
const manufacturerSearchResults = ref([])
const selectedManufacturer = ref(null)
const showManufacturerResults = ref(false)

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

const dueAmount = computed(() => {
    return total.value - form.value.paid_amount
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
            total: rate
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
    if (!selectedManufacturer.value) {
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

    router.post(route('purchases.store'), form.value, {
        onSuccess: () => {
            // Redirect to purchase list
        }
    })
}
</script>
