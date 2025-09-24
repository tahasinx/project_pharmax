<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Invoice #{{ invoice.invoice_no }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('invoices.show', invoice.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        View Invoice
                    </Link>
                    <Link :href="route('invoices.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Invoices
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Side - Customer and Invoice Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Customer Selection -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                                            <div class="relative customer-search-container">
                                                <input v-model="customerSearch"
                                                       @input="searchCustomers"
                                                       @focus="customerSearchFocused = true"
                                                       type="text"
                                                       placeholder="Search customer..."
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                       required>
                                                <div v-if="customerSearchFocused && customerSearchResults.length > 0"
                                                     class="absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">
                                                    <div v-for="customer in customerSearchResults"
                                                         :key="customer.id"
                                                         @click="selectCustomer(customer)"
                                                         class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0">
                                                        {{ customer.name }} - {{ customer.mobile }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
                                            <select v-model="form.payment_type"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    required>
                                                <option value="cash">Cash</option>
                                                <option value="bank">Bank</option>
                                                <option value="credit">Credit</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div v-if="selectedCustomer" class="mt-4 p-3 bg-blue-50 rounded-md">
                                        <p class="text-sm text-blue-800">
                                            <strong>{{ selectedCustomer.name }}</strong><br>
                                            Mobile: {{ selectedCustomer.mobile }}<br>
                                            Email: {{ selectedCustomer.email || 'N/A' }}<br>
                                            Address: {{ selectedCustomer.address || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                                            <input v-model="form.invoice_no"
                                                   type="text"
                                                   readonly
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                            <input v-model="form.date"
                                                   type="date"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Selection -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add Products</h3>
                                    <div class="relative mb-4">
                                        <input v-model="productSearch"
                                               @input="searchProducts"
                                               type="text"
                                               placeholder="Search medicines..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <div v-if="productSearchResults.length > 0"
                                             class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <div v-for="product in productSearchResults"
                                                 :key="product.id"
                                                 @click="addProduct(product)"
                                                 class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b">
                                                <div class="flex justify-between">
                                                    <div>
                                                        <p class="font-medium">{{ product.name }}</p>
                                                        <p class="text-sm text-gray-500">{{ product.generic_name }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-medium">${{ product.price }}</p>
                                                        <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Items -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                                    <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
                                        No items added to invoice
                                    </div>
                                    <div v-else class="space-y-4">
                                        <div v-for="(item, index) in cartItems" :key="index"
                                             class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                                                <p class="text-sm text-gray-500">{{ item.generic_name }}</p>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <div class="flex items-center space-x-2">
                                                    <button @click="decreaseQuantity(index)"
                                                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                        -
                                                    </button>
                                                    <input v-model.number="item.quantity"
                                                           @change="updateItemTotal(index)"
                                                           type="number"
                                                           min="1"
                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                                    <button @click="increaseQuantity(index)"
                                                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                        +
                                                    </button>
                                                </div>
                                                <div class="w-20 text-right">
                                                    <p class="font-medium">${{ formatMoney(item.total) }}</p>
                                                </div>
                                                <button @click="removeItem(index)"
                                                        class="text-red-600 hover:text-red-800">
                                                    🗑️
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Order Summary -->
                        <div class="lg:col-span-1">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Summary</h3>

                                    <div class="space-y-3 mb-6">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium">${{ formatMoney(subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Tax (10%):</span>
                                            <span class="font-medium">${{ formatMoney(tax) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Discount:</span>
                                            <span class="font-medium">-${{ formatMoney(discount) }}</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Total:</span>
                                            <span>${{ formatMoney(total) }}</span>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Discount Amount</label>
                                            <input v-model.number="discountAmount"
                                                   @input="updateDiscount"
                                                   type="number"
                                                   step="0.01"
                                                   min="0"
                                                   :max="subtotal"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                                            <input v-model.number="form.paid_amount"
                                                   type="number"
                                                   step="0.01"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Amount</label>
                                            <input v-model.number="form.due_amount"
                                                   type="number"
                                                   step="0.01"
                                                   readonly
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                        </div>



                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                            <textarea v-model="form.details"
                                                      rows="3"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                        </div>

                                        <button type="submit"
                                                :disabled="cartItems.length === 0 || !form.customer_id"
                                                class="w-full bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded">
                                            Update Invoice
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
import { ref, computed, watch, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    invoice: Object,
    customers: Array,
    medicines: Array,
})

// Ensure dates are in YYYY-MM-DD format for <input type="date">
const formatDateForInput = (value) => {
    if (!value) return ''
    const dt = new Date(value)
    if (Number.isNaN(dt.getTime())) return ''
    const pad = (n) => String(n).padStart(2, '0')
    return `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}`
}

const productSearch = ref('')
const productSearchResults = ref([])
const cartItems = ref([])
const selectedCustomer = ref(null)
const customerSearch = ref('')
const customerSearchResults = ref([])
const customerSearchFocused = ref(false)
const discountAmount = ref(0)

const form = ref({
    customer_id: props.invoice.customer_id,
    payment_type: props.invoice.payment_type,
    paid_amount: props.invoice.paid_amount,
    due_amount: props.invoice.due_amount,
    invoice_no: props.invoice.invoice_no,
    date: formatDateForInput(props.invoice.date),
    details: props.invoice.details,
    items: []
})

const subtotal = computed(() => {
    const sum = cartItems.value.reduce((sum, item) => Number(sum) + Number(item.total || 0), 0)
    return Math.round(sum * 100) / 100 // Round to 2 decimal places
})

const tax = computed(() => {
    const taxAmount = subtotal.value * 0.1 // 10% tax
    return Math.round(taxAmount * 100) / 100 // Round to 2 decimal places
})

const discount = computed(() => {
    return Number(discountAmount.value) || 0
})

const total = computed(() => {
    const totalAmount = subtotal.value + tax.value - discount.value
    return Math.round(totalAmount * 100) / 100 // Round to 2 decimal places
})

// Safe money formatter for numbers or computeds
const formatMoney = (val) => {
    const n = typeof val === 'number' ? val : Number(val?.value ?? val)
    return Number(n || 0).toFixed(2)
}

watch(() => form.value.paid_amount, (newValue) => {
    const dueAmount = total.value - (Number(newValue) || 0)
    form.value.due_amount = Math.round(dueAmount * 100) / 100 // Round to 2 decimal places
})

onMounted(() => {
    // Load existing invoice items into cart
    cartItems.value = props.invoice.items.map(item => {
        const priceNum = Number(item.rate) || 0
        const qtyNum = Number(item.quantity) || 1
        const totalNum = Number(item.total_amount)
        return {
            id: item.medicine_id,
            medicine_id: item.medicine_id,
            name: item.medicine?.name || 'Unknown',
            generic_name: item.medicine?.generic_name || '',
            price: priceNum,
            quantity: qtyNum,
            total: Number.isFinite(totalNum) ? totalNum : priceNum * qtyNum
        }
    })

    // Load customer details
    selectedCustomer.value = props.customers.find(c => c.id == form.value.customer_id)
    if (selectedCustomer.value) {
        customerSearch.value = selectedCustomer.value.name
    }

    // Load discount amount
    discountAmount.value = Number(props.invoice.invoice_discount) || 0
})

const searchCustomers = async () => {
    if (customerSearch.value.length < 2) {
        customerSearchResults.value = []
        return
    }

    try {
        const response = await fetch(`/api/customers/search?q=${customerSearch.value}`)
        customerSearchResults.value = await response.json()
    } catch (error) {
        console.error('Error searching customers:', error)
    }
}

const selectCustomer = (customer) => {
    selectedCustomer.value = customer
    form.value.customer_id = customer.id
    customerSearch.value = `${customer.name}`
    customerSearchResults.value = []
    customerSearchFocused.value = false
}

const updateDiscount = () => {
    if (discountAmount.value > subtotal.value) {
        discountAmount.value = subtotal.value
    }
    if (discountAmount.value < 0) {
        discountAmount.value = 0
    }
}

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
    }
}

const addProduct = (product) => {
    const existingItem = cartItems.value.find(item => item.id === product.id)

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
    } else {
        cartItems.value.push({
            id: product.id,
            medicine_id: product.id,
            name: product.name,
            generic_name: product.generic_name,
            price: Number(product.price) || 0,
            quantity: 1,
            total: Number(product.price) || 0
        })
    }

    productSearch.value = ''
    productSearchResults.value = []
}

const increaseQuantity = (index) => {
    cartItems.value[index].quantity += 1
    updateItemTotal(index)
}

const decreaseQuantity = (index) => {
    if (cartItems.value[index].quantity > 1) {
        cartItems.value[index].quantity -= 1
        updateItemTotal(index)
    }
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = (Number(item.price) || 0) * (Number(item.quantity) || 0)
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const submitForm = () => {
    form.value.items = cartItems.value.map(item => ({
        medicine_id: item.medicine_id,
        quantity: item.quantity,
        rate: item.price,
        discount: 0,
        batch_id: 'BATCH001' // Default batch
    }))

    form.value.total_amount = total.value
    form.value.total_tax = tax.value
    form.value.total_discount = discount.value
    form.value.invoice_discount = discount.value

    router.put(route('invoices.update', props.invoice.id), form.value, {
        onSuccess: () => {
            // Redirect to invoice show page
        }
    })
}
</script>
