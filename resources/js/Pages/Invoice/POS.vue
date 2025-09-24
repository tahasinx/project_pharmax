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

                        <!-- Error Banner -->
                        <div v-if="submitError" class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
                            {{ submitError }}
                        </div>

                        <!-- Product Search -->
                        <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Products</h3>
                                <div class="relative product-search-container">
                                    <div class="flex space-x-2">
                                        <input v-model="productSearch"
                                               @input="searchProducts"
                                               ref="productSearchInput"
                                               type="text"
                                               placeholder="Search medicines... (scan barcode/QR to auto-fill)"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <button @click="openScanModal"
                                                class="whitespace-nowrap px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md">
                                            Scan
                                        </button>
                                    </div>

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
                                                <p class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ product.price }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</p>
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
                                                <p class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ Number(item.total).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</p>
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
                                        <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ Number(subtotal).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ Number(tax).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium">-{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ Number(discount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ Number(total).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
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

    <!-- Scan Modal -->
    <div v-if="showScan" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/60" @click="closeScanModal"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between">
                    <h4 class="text-lg font-medium">Scan Barcode / QR Code</h4>
                    <button @click="closeScanModal" class="text-gray-500 hover:text-gray-700">✖</button>
                </div>
                <div class="p-4">
                    <div class="aspect-video bg-black rounded-md overflow-hidden flex items-center justify-center">
                        <video ref="videoRef" class="w-full h-full object-contain"></video>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
                        <div>
                            <span v-if="scanMessage">{{ scanMessage }}</span>
                            <span v-else>Point the camera at a code. It will auto-detect.</span>
                        </div>
                        <button @click="toggleTorch" :disabled="!canToggleTorch" class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-50">
                            {{ torchOn ? 'Torch Off' : 'Torch On' }}
                        </button>
                    </div>
                </div>
                <div class="p-4 border-t flex items-center justify-end space-x-2">
                    <button @click="restartScan" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">Restart</button>
                    <button @click="closeScanModal" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
// Scanner lib is optional; we lazy-load it when needed to avoid hard dependency
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
const productSearchInput = ref(null)
const cartItems = ref([])
const discountAmount = ref(0)
const submitError = ref('')

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

const addProduct = async (product) => {
    const existingItem = cartItems.value.find(item => item.id === product.id)

    // Fetch available batches to determine selling price
    let price = Number(product.price) || 0
    let batch_id = null
    try {
        const res = await fetch(route('api.medicines.stocks', product.id))
        const stocks = await res.json()
        if (Array.isArray(stocks) && stocks.length > 0) {
            const first = stocks[0]
            price = Number(first.selling_price ?? price) || price
            batch_id = first.id
        }
    } catch {}

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
            total: price,
            batch_id: batch_id,
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
    submitError.value = ''
    form.value.items = cartItems.value.map(item => ({
        medicine_id: item.medicine_id,
        quantity: item.quantity,
        rate: item.price,
        discount: 0,
        batch_id: item.batch_id || 'BATCH001'
    }))

    form.value.total_amount = Number(total.value)
    form.value.total_tax = Number(tax.value)
    form.value.total_discount = Number(discount.value)
    // Ensure due amount is correct on submit
    form.value.due_amount = Number((Number(total.value) - Number(form.value.paid_amount)).toFixed(2))

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
        ,
        onError: (errors) => {
            // Prefer specific items error; otherwise show first error message
            if (errors && errors.items && Array.isArray(errors.items) && errors.items.length > 0) {
                submitError.value = errors.items[0]
            } else if (errors) {
                const first = Object.values(errors)[0]
                submitError.value = Array.isArray(first) ? first[0] : String(first)
            } else {
                submitError.value = 'An error occurred while processing the sale.'
            }
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
    stopScanner()
})

// Scanner state
const showScan = ref(false)
const videoRef = ref(null)
const scanMessage = ref('')
const canToggleTorch = ref(false)
const torchOn = ref(false)
let ZXingReaderClass = null // will be set after dynamic import
let codeReader = null
let currentStream = null

const openScanModal = async () => {
    showScan.value = true
    await nextTick()
    startScanner()
}

const closeScanModal = () => {
    stopScanner()
    showScan.value = false
}

const startScanner = async () => {
    try {
        scanMessage.value = 'Initializing camera...'

        // Lazy-load @zxing/browser. If unavailable, disable scanner gracefully
        if (!ZXingReaderClass) {
            try {
                const ZXING_PKG = '@zxing/browser'
                const mod = await import(/* @vite-ignore */ ZXING_PKG)
                ZXingReaderClass = mod?.BrowserMultiFormatReader || null
            } catch (e1) {
                // Fallback to CDN without hard project dependency
                try {
                    const CDN_URL = 'https://cdn.skypack.dev/@zxing/browser'
                    const modCdn = await import(/* @vite-ignore */ CDN_URL)
                    ZXingReaderClass = modCdn?.BrowserMultiFormatReader || null
                } catch (e2) {
                    ZXingReaderClass = null
                }
            }
        }

        if (!ZXingReaderClass) {
            scanMessage.value = 'Scanner unavailable (dependency not installed)'
            return
        }

        codeReader = new ZXingReaderClass()
        const devices = await ZXingReaderClass.listVideoInputDevices()
        const deviceId = devices?.[0]?.deviceId
        if (!deviceId) {
            scanMessage.value = 'No camera found'
            return
        }

        const constraints = {
            video: {
                deviceId: { ideal: deviceId },
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        }

        const stream = await navigator.mediaDevices.getUserMedia(constraints)
        currentStream = stream
        videoRef.value.srcObject = stream
        await videoRef.value.play()
        scanMessage.value = 'Scanning...'

        // Torch capability
        const track = stream.getVideoTracks()[0]
        const capabilities = track.getCapabilities?.() || {}
        canToggleTorch.value = !!capabilities.torch

        // Decode continuously
        codeReader.decodeFromVideoDevice(deviceId, videoRef.value, (result, err) => {
            if (result) {
                onCodeDetected(result.getText())
            }
        })
    } catch (e) {
        console.error(e)
        scanMessage.value = 'Camera error. Please allow camera permission.'
    }
}

const stopScanner = () => {
    try {
        if (codeReader) {
            codeReader.reset()
            codeReader = null
        }
        if (currentStream) {
            currentStream.getTracks().forEach(t => t.stop())
            currentStream = null
        }
    } catch {}
}

const restartScan = () => {
    stopScanner()
    startScanner()
}

const toggleTorch = () => {
    if (!currentStream) return
    const track = currentStream.getVideoTracks()[0]
    const capabilities = track.getCapabilities?.() || {}
    if (!capabilities.torch) return
    torchOn.value = !torchOn.value
    track.applyConstraints({ advanced: [{ torch: torchOn.value }] })
}

const onCodeDetected = async (text) => {
    // Debounce by closing scanner immediately
    scanMessage.value = 'Code detected'
    closeScanModal()

    // Fill search box and query
    productSearch.value = text
    await nextTick()
    await searchProducts()

    // If single match, add automatically
    if (productSearchResults.value.length === 1) {
        addProduct(productSearchResults.value[0])
    } else if (productSearchResults.value.length > 1) {
        // keep list open for user to choose
    } else {
        // Try fallback exact fetch endpoint if available (product_id / barcode)
        try {
            const res = await fetch(`/api/medicines/search?q=${encodeURIComponent(text)}`)
            const data = await res.json()
            if (Array.isArray(data) && data.length === 1) {
                addProduct(data[0])
            }
        } catch {}
    }
    // Focus back to search for hardware scanners to continue typing
    productSearchInput.value?.focus()
}
</script>
