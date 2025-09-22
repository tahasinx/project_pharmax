<template>
    <Head title="Point of Sale" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Point of Sale (POS)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Product Selection -->
                    <div class="lg:col-span-2">
                        <!-- Customer Selection -->
                        <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                                        <div class="relative customer-search-container">
                                            <input v-model="customerSearch"
                                                   @input="searchCustomers"
                                                   type="text"
                                                   placeholder="Search customer..."
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <div v-if="customerSearchResults.length > 0"
                                                 class="absolute z-[99999] w-full bg-white border border-gray-300 rounded-md shadow-2xl max-h-60 overflow-y-auto mt-1">
                                                <div v-for="customer in customerSearchResults"
                                                     :key="customer.id"
                                                     @click="selectCustomer(customer)"
                                                     class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                                    {{ customer.name }} - {{ customer.mobile }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type</label>
                                        <select v-model="form.payment_type"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                            <option value="credit">Credit</option>
                                        </select>
                                    </div>
                                </div>
                                <div v-if="selectedCustomer" class="mt-4 p-3 bg-blue-50 rounded-md">
                                    <p class="text-sm text-blue-800">
                                        <strong>{{ selectedCustomer.name }}</strong> - {{ selectedCustomer.mobile }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Product Search -->
                        <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Products</h3>
                                <div class="relative product-search-container">
                                    <input v-model="productSearch"
                                           @input="searchProducts"
                                           type="text"
                                           placeholder="Search medicines..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div v-if="productSearchResults.length > 0"
                                         class="absolute z-[99999] w-full bg-white border border-gray-300 rounded-md shadow-2xl max-h-60 overflow-y-auto mt-1">
                                        <div v-for="product in productSearchResults"
                                             :key="product.id"
                                             @click="addProduct(product)"
                                             class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
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
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Cart Items</h3>
                                <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
                                    No items in cart
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
                                                <p class="font-medium">${{ Number(item.total).toFixed(2) }}</p>
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
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>

                                <div class="space-y-3 mb-6">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">${{ Number(subtotal).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="font-medium">${{ Number(tax).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium">-${{ Number(discount).toFixed(2) }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>${{ Number(total).toFixed(2) }}</span>
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
                                               placeholder="0.00"
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

                                    <button @click="processSale"
                                            :disabled="cartItems.length === 0 || !selectedCustomer"
                                            class="w-full bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded">
                                        Process Sale
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
// Icons replaced with emojis

const props = defineProps({
    customers: Array,
    medicines: Array,
    banks: Array,
    invoiceNo: String
})

const customerSearch = ref('')
const customerSearchResults = ref([])
const selectedCustomer = ref(null)
const productSearch = ref('')
const productSearchResults = ref([])
const cartItems = ref([])
const discountAmount = ref(0)

const form = ref({
    customer_id: null,
    payment_type: 'cash',
    paid_amount: 0,
    due_amount: 0,
    invoice_no: props.invoiceNo,
    date: new Date().toISOString().split('T')[0],
    items: []
})

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (Number(item.total) || 0), 0)
})

const tax = computed(() => {
    return (Number(subtotal.value) || 0) * 0.1 // 10% tax
})

const discount = computed(() => {
    return Number(discountAmount.value) || 0
})

const total = computed(() => {
    return (Number(subtotal.value) || 0) + (Number(tax.value) || 0) - (Number(discount.value) || 0)
})

// Watch for changes in paid amount and discount to update due amount
watch([() => form.value.paid_amount, () => discountAmount.value], () => {
    form.value.due_amount = total.value - form.value.paid_amount
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
    customerSearch.value = customer.name
    customerSearchResults.value = []
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

    // Ensure price is a number
    const price = Number(product.price) || 0

    if (existingItem) {
        existingItem.quantity += 1
        updateItemTotal(cartItems.value.indexOf(existingItem))
    } else {
        cartItems.value.push({
            id: product.id,
            medicine_id: product.id,
            name: product.name,
            generic_name: product.generic_name,
            price: price,
            quantity: 1,
            total: price
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
    item.total = Number(item.price) * Number(item.quantity)
}

const removeItem = (index) => {
    cartItems.value.splice(index, 1)
}

const processSale = () => {
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

    router.post(route('invoices.store'), form.value, {
        onSuccess: () => {
            // Reset form
            cartItems.value = []
            selectedCustomer.value = null
            customerSearch.value = ''
            discountAmount.value = 0
            form.value.paid_amount = 0
            form.value.due_amount = 0
        }
    })
}

const updateDiscount = () => {
    // Ensure discount doesn't exceed subtotal
    const maxDiscount = subtotal.value
    if (discountAmount.value > maxDiscount) {
        discountAmount.value = maxDiscount
    }
    // Trigger due amount recalculation
    form.value.due_amount = total.value - form.value.paid_amount
}

// Click outside handler to close search results
const handleClickOutside = (event) => {
    const customerSearchElement = event.target.closest('.customer-search-container')
    const productSearchElement = event.target.closest('.product-search-container')

    if (!customerSearchElement) {
        customerSearchResults.value = []
    }

    if (!productSearchElement) {
        productSearchResults.value = []
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
