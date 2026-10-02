<template>
    <Head title="Pharmacy POS" />
    <div class="pos-screen">
        <header class="pos-top">
            <Link :href="route('dashboard')" class="btn btn-sm btn-light"><i class="bi bi-arrow-left me-1"></i>Exit</Link>
            <div class="min-w-0">
                <h1 class="pos-title mb-0">Pharmacy POS</h1>
                <p class="text-muted font-size-12 mb-0">{{ invoiceNo }}</p>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <span class="text-muted font-size-13">Today {{ money(todaySales) }} · {{ todayCount }} bills</span>
                <Link :href="route('invoices.index')" class="btn btn-sm btn-light">Invoices</Link>
            </div>
        </header>

        <div class="card pos-speed mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-lg-6">
                        <div class="pos-search">
                            <i class="bi bi-search"></i>
                            <input ref="productSearchInput" v-model="productSearch" type="search" class="form-control form-control-sm" placeholder="Search medicine, generic, barcode, or SKU" autofocus @keydown.enter.prevent="searchEnter">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                            <button v-for="chip in chips" :key="chip.key" type="button" class="btn btn-sm" :class="filter === chip.key ? 'btn-primary' : 'btn-light'" @click="filter = chip.key">
                                <i class="bi" :class="chip.icon"></i>
                                <span class="ms-1">{{ chip.label }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" @click="openScanModal">
                                <i class="bi bi-upc-scan"></i>
                                <span class="ms-1">Scan</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center gap-2 mt-2">
                    <span class="text-muted font-size-12">{{ catalog.length }} shown · {{ totalStock }} saleable</span>
                    <span class="badge badge-soft-warning">{{ lowStockCount }} low</span>
                </div>
            </div>
        </div>

        <div v-if="submitError" class="alert alert-danger py-2">{{ submitError }}</div>

        <div class="pos-stage">
            <div class="pos-stage-list">
                <div class="card h-100 mb-0">
                    <div class="card-body p-0">
                        <div class="table-responsive pos-list">
                            <table class="table table-hover table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Type</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in catalog" :key="product.id">
                                        <td>
                                            <div class="fw-medium text-truncate">{{ product.name }}</div>
                                            <div class="text-muted font-size-12 text-truncate">{{ product.generic_name || product.dosage_form || 'Medicine' }}<span v-if="product.manufacturer?.name"> · {{ product.manufacturer.name }}</span></div>
                                        </td>
                                        <td><span class="badge" :class="badgeClass(product)">{{ badge(product) }}</span></td>
                                        <td class="text-end" :class="stockClass(product)">{{ stockOf(product) }}</td>
                                        <td class="text-end fw-medium">{{ money(displayPrice(product)) }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary" :disabled="stockOf(product) < 1" @click="addProduct(product)">
                                                {{ stockOf(product) < 1 ? 'Out' : 'Add' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!catalog.length">
                                        <td colspan="5" class="text-center text-muted py-4">No medicines match this search.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pos-stage-ticket">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0">Customer</h5>
                            <button type="button" class="btn btn-link btn-sm p-0" @click="changingCustomer = !changingCustomer">Change</button>
                        </div>
                        <div v-if="changingCustomer || !selectedCustomer" class="position-relative mb-3">
                            <input v-model="customerSearch" type="search" class="form-control" placeholder="Name or mobile" @input="searchCustomers">
                            <div v-if="customerSearchResults.length" class="pos-suggest card">
                                <button v-for="customer in customerSearchResults" :key="customer.id" type="button" class="dropdown-item" @click="selectCustomer(customer)">
                                    {{ customer.name }} <span class="text-muted">{{ customer.mobile }}</span>
                                </button>
                            </div>
                        </div>
                        <div v-else class="mb-3">
                            <p class="mb-0 fw-medium">{{ selectedCustomer.name }}</p>
                            <p v-if="customerPhoneDisplay" class="text-muted font-size-12 mb-0">{{ customerPhoneDisplay }}</p>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0">Cart <span class="text-muted fw-normal">{{ cartItems.length }}</span></h5>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted" :disabled="!cartItems.length" @click="clearCart">Clear</button>
                        </div>
                        <div class="pos-lines">
                            <p v-if="!cartItems.length" class="text-muted text-center py-4 mb-0">Cart is empty</p>
                            <div v-for="(item, index) in cartItems" :key="item.id" class="pos-line">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="min-w-0">
                                        <p class="mb-0 text-truncate fw-medium">{{ item.name }}</p>
                                        <p class="text-muted font-size-12 mb-0">{{ money(item.price) }} · {{ packLabel(item.units, item.quantity) }}</p>
                                    </div>
                                    <span class="fw-medium">{{ money(item.total) }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-light pos-qty" @click="decreaseQuantity(index)">−</button>
                                    <span class="pos-qty-value">{{ item.quantity }}</span>
                                    <button type="button" class="btn btn-sm btn-light pos-qty" :disabled="item.stock > 0 && item.quantity >= item.stock" @click="increaseQuantity(index)">+</button>
                                    <span class="ms-auto text-muted font-size-12">Stock {{ item.stock }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="input-group input-group-sm mt-3">
                            <input v-model="discountCode" type="number" min="0" step="0.01" class="form-control" placeholder="Discount amount">
                            <button type="button" class="btn btn-light" @click="applyDiscount">Apply</button>
                        </div>
                        <p v-if="discountNote" class="font-size-12 mt-1 mb-0" :class="discountError ? 'text-danger' : 'text-muted'">{{ discountNote }}</p>
                        <dl class="pos-totals">
                            <div><dt>Subtotal</dt><dd>{{ money(subtotal) }}</dd></div>
                            <div><dt>Tax {{ taxRate }}%</dt><dd>{{ money(tax) }}</dd></div>
                            <div v-if="discount > 0"><dt>Discount</dt><dd>−{{ money(discount) }}</dd></div>
                            <div class="pos-grand"><dt>Invoice total</dt><dd>{{ money(total) }}</dd></div>
                        </dl>
                        <button type="button" class="btn btn-primary w-100" :disabled="isProcessing || !cartItems.length" @click="openPay">
                            {{ isProcessing ? 'Posting...' : 'Post invoice' }}
                        </button>
                        <button type="button" class="btn btn-link btn-sm w-100 text-muted" :disabled="!cartItems.length" @click="holdBill">Hold bill</button>
                        <button v-for="bill in heldBills" :key="bill.id" type="button" class="btn btn-sm btn-light w-100 mb-1" @click="router.post(route('pos.resume', bill.id))">
                            Resume {{ bill.label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showPay" class="pos-modal" @click.self="showPay = false">
            <form class="card pos-modal-card" @submit.prevent="processSale">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0">Invoice {{ invoiceNo }}</h5>
                        <button type="button" class="btn btn-sm btn-light" @click="showPay = false">Close</button>
                    </div>
                    <p class="text-muted mb-3">Total {{ money(total) }} will be posted as a sales invoice.</p>
                    <label class="form-label">Method</label>
                    <select v-model="pay.method" class="form-select mb-3">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                        <option value="bank">Bank</option>
                        <option value="credit">Credit</option>
                    </select>
                    <label class="form-label">Amount received</label>
                    <input v-model.number="pay.amount" type="number" min="0" step="0.01" class="form-control mb-2" :disabled="pay.method === 'credit'">
                    <button type="button" class="btn btn-sm btn-light mb-3" @click="pay.amount = Number(total.toFixed(2))">Exact {{ money(total) }}</button>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Change</span><span>{{ money(changeDue) }}</span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Balance due</span><span>{{ money(stillDue) }}</span></div>
                    <p v-if="!selectedCustomer" class="text-danger font-size-12">Choose a customer before posting this invoice.</p>
                    <button class="btn btn-primary w-100" :disabled="isProcessing || !selectedCustomer">{{ isProcessing ? 'Posting...' : 'Confirm and open invoice' }}</button>
                </div>
            </form>
        </div>

        <div v-if="showScan" class="pos-modal">
            <div class="card pos-modal-card pos-modal-wide">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Scan barcode</h5>
                    <button type="button" class="btn btn-sm btn-light" @click="closeScanModal">Close</button>
                </div>
                <div class="card-body">
                    <video ref="videoRef" class="pos-video"></video>
                    <p class="text-muted mt-2 mb-0">{{ scanMessage || 'Point the camera at a code.' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useLunaApp } from '@/Composables/useLunaApp'

useLunaApp()

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

const chips = [
    { key: 'all', label: 'All', icon: 'bi-grid' },
    { key: 'OTC', label: 'OTC', icon: 'bi-capsule' },
    { key: 'Rx', label: 'Rx', icon: 'bi-file-medical' },
    { key: 'Supplement', label: 'Supplement', icon: 'bi-heart-pulse' },
    { key: 'Device', label: 'Device', icon: 'bi-bandaid' },
]
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
    if (kind === 'Rx') return 'badge-soft-danger'
    if (kind === 'Supplement') return 'badge-soft-success'
    if (kind === 'Device') return 'badge-soft-info'
    return 'badge-soft-primary'
}
const stockClass = (product) => {
    const stock = stockOf(product)
    if (stock < 1) return 'text-danger fw-medium'
    if (stock <= 10) return 'text-warning fw-medium'
    return 'text-muted'
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
const changeDue = computed(() => pay.value.method === 'credit' ? 0 : Math.max(Number(pay.value.amount || 0) - total.value, 0))
const stillDue = computed(() => pay.value.method === 'credit' ? total.value : Math.max(total.value - Number(pay.value.amount || 0), 0))
const openPay = () => {
    pay.value.amount = Number(total.value.toFixed(2))
    showPay.value = true
}

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
    if (stockOf(product) < 1) {
        submitError.value = `${product.name} has no saleable stock.`
        return
    }
    submitError.value = ''
    const existing = cartItems.value.find(item => item.id === product.id)
    if (existing && existing.quantity >= existing.stock) return
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
    const item = cartItems.value[index]
    if (item.stock > 0 && item.quantity >= item.stock) return
    item.quantity += 1
    item.total = item.price * item.quantity
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
    const saved = localStorage.getItem('epharma-theme') || 'system'
    const appearance = saved === 'light' || saved === 'dark' || saved === 'night'
        ? saved
        : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    document.body.setAttribute('data-bs-theme', appearance === 'light' ? 'light' : 'dark')
    document.body.setAttribute('data-theme', appearance)
    productSearchInput.value?.focus()
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
