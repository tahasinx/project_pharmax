<template>
    <Head title="Pharmacy POS" />
    <div class="fixed inset-0 z-[80] overflow-y-auto bg-[#f6f7f9] text-gray-900">
        <div class="mx-auto min-h-full max-w-[1280px] px-5 py-6 sm:px-8">
            <header class="mb-5 flex items-start justify-between gap-6">
                <div class="flex items-start gap-4">
                    <Link :href="route('dashboard')" class="mt-1 inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                        <span aria-hidden="true">←</span> Exit
                    </Link>
                    <div>
                        <h1 class="text-[22px] font-semibold tracking-tight">Pharmacy POS</h1>
                        <p class="mt-0.5 text-sm text-gray-500">Search medicine or use barcode, add to cart, and checkout quickly</p>
                    </div>
                </div>
                <div class="flex items-start gap-8 text-right">
                    <div>
                        <p class="text-xs text-gray-500">Today's Sales</p>
                        <p class="text-lg font-semibold">{{ money(todaySales) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Transactions</p>
                        <p class="text-lg font-semibold">{{ todayCount }}</p>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="rounded-2xl border border-gray-200 bg-white p-4">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">⌕</span>
                            <input ref="productSearchInput" v-model="productSearch" type="text" placeholder="Search medicine or use barcode..." class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm outline-none focus:border-gray-400" @keydown.enter.prevent="searchEnter">
                        </div>
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 text-sm font-medium hover:bg-gray-50" @click="openScanModal">
                            Scan Barcode
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button v-for="chip in chips" :key="chip" type="button" class="rounded-full border px-3 py-1 text-xs font-medium" :class="filter === chip ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'" @click="filter = chip">
                            {{ chip === 'all' ? 'all' : chip }}
                        </button>
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="rounded-xl border border-gray-200 px-3 py-2">
                            <p class="text-[11px] text-gray-500">Products</p>
                            <p class="text-lg font-semibold">{{ medicines.length }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 px-3 py-2">
                            <p class="text-[11px] text-gray-500">Total Stock</p>
                            <p class="text-lg font-semibold">{{ totalStock }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
                            <p class="text-[11px] text-amber-700">Low Stock</p>
                            <p class="text-lg font-semibold text-amber-800">{{ lowStockCount }}</p>
                        </div>
                    </div>

                    <p v-if="submitError" class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ submitError }}</p>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <article v-for="product in catalog" :key="product.id" class="flex flex-col rounded-xl border border-gray-200 p-3">
                            <div class="flex items-start justify-between">
                                <span class="text-gray-400">⧉</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="badgeClass(product)">{{ badge(product) }}</span>
                            </div>
                            <h3 class="mt-2 text-sm font-semibold leading-tight">{{ product.name }}</h3>
                            <p class="text-xs text-gray-500">{{ product.generic_name || product.dosage_form || 'Medicine' }}</p>
                            <p class="text-[11px] text-gray-400">{{ product.manufacturer?.name }}</p>
                            <div class="mt-3 flex items-end justify-between">
                                <span class="text-sm font-semibold">{{ money(displayPrice(product)) }}</span>
                                <span class="text-[11px]" :class="stockOf(product) <= 10 ? 'font-medium text-amber-600' : 'text-gray-400'">{{ stockOf(product) }} left</span>
                            </div>
                            <button type="button" class="mt-3 rounded-lg border border-dashed border-gray-300 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" @click="addProduct(product)">+ Add to Cart</button>
                        </article>
                        <p v-if="catalog.length === 0" class="col-span-full py-10 text-center text-sm text-gray-400">No medicines match this search.</p>
                    </div>
                </section>

                <aside class="space-y-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-sm font-semibold">Customer</h2>
                            <button type="button" class="text-xs font-medium text-gray-500 hover:text-gray-900" @click="changingCustomer = !changingCustomer">Change</button>
                        </div>
                        <div v-if="changingCustomer || !selectedCustomer" class="relative customer-search-container">
                            <input v-model="customerSearch" type="text" placeholder="Search customer..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none" @input="searchCustomers">
                            <div v-if="customerSearchResults.length" class="absolute z-20 mt-1 max-h-48 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                                <button v-for="customer in customerSearchResults" :key="customer.id" type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50" @click="selectCustomer(customer)">
                                    {{ customer.name }} · {{ customer.mobile }}
                                </button>
                            </div>
                        </div>
                        <div v-else class="space-y-1 text-sm">
                            <p class="font-medium">{{ selectedCustomer.name }}</p>
                            <p v-if="customerPhoneDisplay" class="text-gray-500">{{ customerPhoneDisplay }}</p>
                            <p v-if="selectedCustomer.email" class="text-gray-500">{{ selectedCustomer.email }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-semibold">Cart</h2>
                                <p class="text-[11px] text-gray-400">{{ cartItems.length }} items</p>
                            </div>
                            <button type="button" class="text-xs text-gray-400 hover:text-gray-700" @click="clearCart">Clear</button>
                        </div>

                        <div v-if="cartItems.length === 0" class="py-8 text-center text-sm text-gray-400">Cart is empty</div>
                        <div v-else class="space-y-4">
                            <div v-for="(item, index) in cartItems" :key="item.id">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium">{{ item.name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ money(item.price) }} each</p>
                                        <p class="text-[11px] text-gray-400">{{ packLabel(item.units, item.quantity) }}</p>
                                    </div>
                                    <p class="text-sm font-semibold">{{ money(item.total) }}</p>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" class="h-7 w-7 rounded-md border border-gray-200 text-sm" @click="decreaseQuantity(index)">−</button>
                                    <span class="w-6 text-center text-sm">{{ item.quantity }}</span>
                                    <button type="button" class="h-7 w-7 rounded-md border border-gray-200 text-sm" @click="increaseQuantity(index)">+</button>
                                    <span class="ml-auto text-[11px] text-gray-400">Stock {{ item.stock }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <input v-model="discountCode" type="text" placeholder="Discount code" class="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none">
                            <button type="button" class="rounded-lg border border-gray-200 px-3 text-sm" @click="applyDiscount">Apply</button>
                        </div>
                        <p v-if="discountNote" class="mt-1 text-[11px]" :class="discountError ? 'text-red-600' : 'text-gray-500'">{{ discountNote }}</p>

                        <dl class="mt-4 space-y-1.5 text-sm">
                            <div class="flex justify-between text-gray-500"><dt>Subtotal</dt><dd>{{ money(subtotal) }}</dd></div>
                            <div class="flex justify-between text-gray-500"><dt>Tax ({{ taxRate }}%)</dt><dd>{{ money(tax) }}</dd></div>
                            <div v-if="discount > 0" class="flex justify-between text-gray-500"><dt>Discount</dt><dd>−{{ money(discount) }}</dd></div>
                            <div class="flex justify-between pt-1 text-base font-semibold"><dt>Total</dt><dd>{{ money(total) }}</dd></div>
                        </dl>

                        <button type="button" class="mt-4 w-full rounded-xl bg-gray-950 py-2.5 text-sm font-medium text-white disabled:bg-gray-300" :disabled="isProcessing || cartItems.length === 0" @click="showPay = true">
                            {{ isProcessing ? 'Processing...' : 'Process Payment' }}
                        </button>
                        <button type="button" class="mt-2 flex w-full items-center justify-center gap-2 py-2 text-sm text-gray-600 hover:text-gray-900" :disabled="cartItems.length === 0" @click="holdBill">
                            Save Order
                        </button>
                        <div v-if="heldBills.length" class="mt-2 space-y-1 border-t pt-2">
                            <button v-for="bill in heldBills" :key="bill.id" type="button" class="block w-full text-left text-xs text-gray-500 hover:text-gray-900" @click="router.post(route('pos.resume', bill.id))">
                                Resume {{ bill.label }}
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div v-if="showPay" class="fixed inset-0 z-[90] flex items-end justify-center bg-black/30 p-4 sm:items-center" @click.self="showPay = false">
            <form class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl" @submit.prevent="processSale">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold">Payment</h3>
                    <button type="button" class="text-sm text-gray-400" @click="showPay = false">Close</button>
                </div>
                <p class="mb-3 text-sm text-gray-500">Due {{ money(total) }}</p>
                <label class="mb-1 block text-xs text-gray-500">Method</label>
                <select v-model="pay.method" class="mb-3 w-full rounded-lg border-gray-200 text-sm">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="rocket">Rocket</option>
                    <option value="bank">Bank</option>
                    <option value="credit">Credit</option>
                </select>
                <label class="mb-1 block text-xs text-gray-500">Amount received</label>
                <input v-model.number="pay.amount" type="number" min="0" step="0.01" class="mb-4 w-full rounded-lg border-gray-200 text-sm">
                <p v-if="!selectedCustomer" class="mb-3 text-xs text-red-600">Choose a customer before taking payment.</p>
                <button class="w-full rounded-xl bg-gray-950 py-2.5 text-sm font-medium text-white" :disabled="isProcessing || !selectedCustomer">Confirm</button>
            </form>
        </div>

        <div v-if="showScan" class="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white">
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h4 class="font-medium">Scan Barcode</h4>
                    <button type="button" @click="closeScanModal">Close</button>
                </div>
                <div class="p-4">
                    <video ref="videoRef" class="aspect-video w-full rounded-lg bg-black object-contain"></video>
                    <p class="mt-2 text-sm text-gray-500">{{ scanMessage || 'Point the camera at a code.' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    customers: Array,
    medicines: Array,
    invoiceNo: String,
    heldBills: { type: Array, default: () => [] },
    resume: { type: Object, default: null },
    todaySales: { type: Number, default: 0 },
    todayCount: { type: Number, default: 0 },
})

const page = usePage()
const ui = computed(() => page.props.ui || {})
const taxRate = computed(() => Number(ui.value.default_tax_rate ?? 10))
const money = (amount) => {
    const value = Number(amount || 0).toFixed(2)
    return ui.value.currency_position === 'after'
        ? `${value}${ui.value.currency_symbol || ''}`
        : `${ui.value.currency_symbol || '$'}${value}`
}

const chips = ['all', 'OTC', 'Rx', 'Supplement', 'Device']
const filter = ref('all')
const productSearch = ref('')
const productSearchInput = ref(null)
const cartItems = ref([])
const selectedCustomer = ref(null)
const customerSearch = ref('')
const customerSearchResults = ref([])
const changingCustomer = ref(false)
const discountCode = ref('')
const discountAmount = ref(0)
const discountNote = ref('')
const discountError = ref(false)
const submitError = ref('')
const isProcessing = ref(false)
const showPay = ref(false)
const pay = ref({ method: 'cash', amount: 0 })

const stockOf = (product) => Number(product.stock_qty || 0)
const displayPrice = (product) => Number(product.price || 0)
const badge = (product) => {
    const category = (product.category?.name || '').toLowerCase()
    if (category.includes('device')) return 'Device'
    if (category.includes('supplement')) return 'Supplement'
    return product.requires_prescription ? 'Rx' : 'OTC'
}
const badgeClass = (product) => {
    const kind = badge(product)
    if (kind === 'Rx') return 'bg-rose-50 text-rose-600'
    if (kind === 'Supplement') return 'bg-emerald-50 text-emerald-700'
    if (kind === 'Device') return 'bg-sky-50 text-sky-700'
    return 'bg-gray-100 text-gray-500'
}

const totalStock = computed(() => props.medicines.reduce((sum, product) => sum + stockOf(product), 0))
const lowStockCount = computed(() => props.medicines.filter(product => stockOf(product) > 0 && stockOf(product) <= 10).length)
const catalog = computed(() => props.medicines.filter(product => {
    const kind = badge(product)
    if (filter.value !== 'all' && kind !== filter.value) return false
    const q = productSearch.value.trim().toLowerCase()
    if (!q) return true
    return [product.name, product.generic_name, product.sku, product.product_id, product.barcode_data, product.manufacturer?.name]
        .filter(Boolean).some(value => String(value).toLowerCase().includes(q))
}))

const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + Number(item.total || 0), 0))
const discount = computed(() => Number(discountAmount.value) || 0)
const tax = computed(() => Math.max(subtotal.value - discount.value, 0) * (taxRate.value / 100))
const total = computed(() => Math.max(subtotal.value - discount.value, 0) + tax.value)

const packLabel = (units, quantity) => {
    const levels = [...(units || [])].sort((a, b) => Number(b.factor_to_base) - Number(a.factor_to_base))
    if (!levels.length) return `${quantity} pcs`
    let remaining = Number(quantity) || 0
    const parts = []
    for (const unit of levels) {
        const factor = Math.max(1, Number(unit.factor_to_base) || 1)
        if (factor === 1) {
            if (remaining > 0) parts.push(`${remaining} ${unit.name}`)
            break
        }
        const count = Math.floor(remaining / factor)
        if (count > 0) {
            parts.push(`${count} ${unit.name}`)
            remaining -= count * factor
        }
    }
    return parts.join(' + ') || '0'
}

const addProduct = async (product) => {
    const existing = cartItems.value.find(item => item.id === product.id)
    let price = displayPrice(product)
    let batchId = null
    try {
        const response = await fetch(route('api.medicines.stocks', product.id))
        const stocks = await response.json()
        if (Array.isArray(stocks) && stocks[0]) {
            price = Number(stocks[0].selling_price ?? price) || price
            batchId = stocks[0].id
        }
    } catch (e) {}
    if (existing) {
        existing.quantity += 1
        existing.total = existing.price * existing.quantity
        return
    }
    cartItems.value.push({
        id: product.id,
        medicine_id: product.id,
        name: product.name,
        units: product.units || [],
        price,
        quantity: 1,
        total: price,
        batch_id: batchId,
        stock: stockOf(product),
    })
}

const increaseQuantity = (index) => {
    cartItems.value[index].quantity += 1
    cartItems.value[index].total = cartItems.value[index].price * cartItems.value[index].quantity
}
const decreaseQuantity = (index) => {
    if (cartItems.value[index].quantity <= 1) {
        cartItems.value.splice(index, 1)
        return
    }
    cartItems.value[index].quantity -= 1
    cartItems.value[index].total = cartItems.value[index].price * cartItems.value[index].quantity
}
const clearCart = () => { cartItems.value = [] }

const searchCustomers = async () => {
    if (customerSearch.value.length < 2) {
        customerSearchResults.value = []
        return
    }
    const response = await fetch(`/api/customers/search?q=${encodeURIComponent(customerSearch.value)}`)
    customerSearchResults.value = await response.json()
}
const selectCustomer = (customer) => {
    selectedCustomer.value = customer
    changingCustomer.value = false
    customerSearch.value = ''
    customerSearchResults.value = []
}

const customerPhoneDisplay = computed(() => selectedCustomer.value?.mobile || selectedCustomer.value?.phone || '')

const applyDiscount = () => {
    const raw = discountCode.value.trim()
    if (!raw) {
        discountAmount.value = 0
        discountNote.value = ''
        discountError.value = false
        return
    }
    const amount = Number(raw)
    if (!Number.isFinite(amount) || amount < 0) {
        discountError.value = true
        discountNote.value = 'Enter a discount amount. Named codes are not set up.'
        return
    }
    discountAmount.value = Math.min(amount, subtotal.value)
    discountError.value = false
    discountNote.value = `Discount ${money(discountAmount.value)} applied.`
}

const holdBill = () => {
    router.post(route('pos.hold'), {
        label: selectedCustomer.value?.name || 'Held bill',
        payload: {
            customer_id: selectedCustomer.value?.id,
            items: cartItems.value,
            discount: discountAmount.value,
        },
    })
}

const processSale = () => {
    if (!selectedCustomer.value) return
    submitError.value = ''
    isProcessing.value = true
    const paid = pay.value.method === 'credit' ? 0 : Number(pay.value.amount || 0)
    router.post(route('invoices.store'), {
        customer_id: selectedCustomer.value.id,
        payment_type: pay.value.method,
        paid_amount: paid,
        due_amount: Math.max(Number((total.value - paid).toFixed(2)), 0),
        invoice_no: props.invoiceNo,
        date: new Date().toISOString().slice(0, 10),
        total_amount: Number(total.value.toFixed(2)),
        total_tax: Number(tax.value.toFixed(2)),
        total_discount: Number(discount.value.toFixed(2)),
        items: cartItems.value.map(item => ({
            medicine_id: item.medicine_id,
            quantity: item.quantity,
            rate: item.price,
            discount: 0,
            batch_id: item.batch_id,
        })),
        payments: paid > 0 ? [{ method: pay.value.method, amount: paid }] : [],
    }, {
        onSuccess: () => {
            cartItems.value = []
            showPay.value = false
            discountAmount.value = 0
            discountCode.value = ''
        },
        onError: (errors) => {
            const first = errors?.items?.[0] || Object.values(errors || {})[0]
            submitError.value = Array.isArray(first) ? first[0] : (first || 'The sale could not be saved.')
            showPay.value = false
        },
        onFinish: () => { isProcessing.value = false },
    })
}

const searchEnter = () => {
    if (catalog.value.length === 1) addProduct(catalog.value[0])
}

const showScan = ref(false)
const videoRef = ref(null)
const scanMessage = ref('')
let ZXingReaderClass = null
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
        scanMessage.value = 'Starting camera...'
        if (!ZXingReaderClass) {
            try {
                const pkg = '@zxing/' + 'browser'
                const mod = await import(/* @vite-ignore */ pkg)
                ZXingReaderClass = mod?.BrowserMultiFormatReader || null
            } catch (e) {
                ZXingReaderClass = null
            }
        }
        if (!ZXingReaderClass) {
            scanMessage.value = 'Scanner is not available in this browser.'
            return
        }
        codeReader = new ZXingReaderClass()
        const devices = await ZXingReaderClass.listVideoInputDevices()
        const deviceId = devices?.[0]?.deviceId
        if (!deviceId) {
            scanMessage.value = 'No camera found'
            return
        }
        const stream = await navigator.mediaDevices.getUserMedia({ video: { deviceId: { ideal: deviceId }, facingMode: 'environment' } })
        currentStream = stream
        videoRef.value.srcObject = stream
        await videoRef.value.play()
        scanMessage.value = 'Scanning...'
        codeReader.decodeFromVideoDevice(deviceId, videoRef.value, (result) => {
            if (result) onCodeDetected(result.getText())
        })
    } catch (e) {
        scanMessage.value = 'Allow the camera to scan a code.'
    }
}
const stopScanner = () => {
    try {
        codeReader?.reset()
        codeReader = null
        currentStream?.getTracks().forEach(track => track.stop())
        currentStream = null
    } catch (e) {}
}
const onCodeDetected = (text) => {
    closeScanModal()
    productSearch.value = text
    const match = props.medicines.find(product => [product.barcode_data, product.product_id, product.sku, product.name].filter(Boolean).some(value => String(value).toLowerCase() === text.toLowerCase()))
    if (match) addProduct(match)
}

onMounted(() => {
    if (props.resume?.items?.length) {
        cartItems.value = props.resume.items
        const customer = props.customers.find(row => row.id === props.resume.customer_id)
        if (customer) selectedCustomer.value = customer
        discountAmount.value = Number(props.resume.discount || 0)
    }
    pay.value.amount = Number(total.value.toFixed(2))
})
onUnmounted(stopScanner)
watch(total, (value) => { pay.value.amount = Number(value.toFixed(2)) })
</script>
