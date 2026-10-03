<template>
    <Head title="Pharmacy POS" />
    <div class="pos-screen">
        <header class="pos-top">
            <Link :href="route('dashboard')" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i>Exit
            </Link>
            <div class="min-w-0">
                <h1 class="pos-title mb-0">Pharmacy POS</h1>
                <p class="text-muted font-size-12 mb-0">{{ invoiceNo }}</p>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <span class="text-muted font-size-13 d-none d-sm-inline">Today {{ money(todaySales) }} · {{ todayCount }} bills</span>
                <Link :href="route('invoices.index')" class="btn btn-sm btn-light">Invoices</Link>
            </div>
        </header>

        <div class="card pos-speed mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-lg-6">
                        <div class="pos-search">
                            <i class="bi bi-search"></i>
                            <input
                                ref="productSearchInput"
                                v-model="productSearch"
                                type="search"
                                class="form-control form-control-sm"
                                placeholder="Search medicine, generic, barcode, or SKU"
                                autofocus
                                autocomplete="off"
                                @input="onProductSearchInput"
                                @keydown.enter.prevent="searchEnter"
                            >
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                            <button
                                v-for="chip in chips"
                                :key="chip.key"
                                type="button"
                                class="btn btn-sm"
                                :class="filter === chip.key ? 'btn-primary' : 'btn-light'"
                                @click="setFilter(chip.key)"
                            >
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
                    <span v-if="searching" class="text-primary font-size-12">Searching…</span>
                    <span class="text-muted font-size-12">{{ catalog.length }} shown · {{ totalStock }} saleable</span>
                    <span class="badge badge-soft-warning">{{ lowStockCount }} low</span>
                </div>
            </div>
        </div>

        <div v-if="submitError" class="alert alert-danger py-2">{{ submitError }}</div>

        <div class="pos-stage">
            <div class="pos-stage-list">
                <div class="card h-100 mb-0">
                    <div class="card-body p-2 p-md-3">
                        <div v-if="!catalog.length && !searching" class="text-center text-muted py-5">
                            <div>{{ productSearch ? 'No medicines match this search.' : 'No medicines available.' }}</div>
                            <button
                                v-if="productSearch.trim().length >= 2"
                                type="button"
                                class="btn btn-sm btn-outline-primary mt-3"
                                :disabled="medexLooking"
                                @click="lookupMedex"
                            >
                                {{ medexLooking ? 'Looking up MedEx…' : 'Lookup on MedEx' }}
                            </button>
                            <div v-if="medexHits.length" class="list-group text-start mt-3 mx-auto" style="max-width: 420px;">
                                <button
                                    v-for="hit in medexHits"
                                    :key="hit.link"
                                    type="button"
                                    class="list-group-item list-group-item-action"
                                    :disabled="medexSaving === hit.link"
                                    @click="importMedexHit(hit)"
                                >
                                    <span class="fw-medium">{{ hit.name }}</span>
                                    <span class="d-block text-muted font-size-12">{{ hit.form }} · {{ hit.strength }}</span>
                                    <span class="font-size-12 text-primary">{{ medexSaving === hit.link ? 'Saving…' : 'Add to catalog' }}</span>
                                </button>
                            </div>
                            <div v-if="medexNote" class="text-muted font-size-12 mt-2">{{ medexNote }}</div>
                        </div>
                        <div v-else class="pos-drug-grid">
                            <article
                                v-for="product in catalog"
                                :key="product.id"
                                class="pos-drug-card"
                                :class="{ 'is-out': stockOf(product) < 1 }"
                            >
                                <div class="pos-drug-top">
                                    <div class="min-w-0">
                                        <div class="fw-medium text-truncate">{{ product.name }}</div>
                                        <div class="text-muted font-size-12 text-truncate">
                                            {{ genericLabel(product) }}
                                            <span v-if="product.manufacturer?.name"> · {{ product.manufacturer.name }}</span>
                                        </div>
                                    </div>
                                    <span class="badge" :class="badgeClass(product)">{{ badge(product) }}</span>
                                </div>
                                <div class="pos-drug-foot">
                                    <div>
                                        <div class="fw-medium">{{ money(displayPrice(product)) }}</div>
                                        <div class="font-size-12" :class="stockClass(product)">
                                            Stock {{ stockOf(product) }}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        :disabled="stockOf(product) < 1 || addingId === product.id"
                                        @click="addProduct(product)"
                                    >
                                        {{ stockOf(product) < 1 ? 'Out' : (addingId === product.id ? '…' : 'Add') }}
                                    </button>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pos-stage-ticket">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0">Customer</h5>
                            <button v-if="selectedCustomer" type="button" class="btn btn-link btn-sm p-0" @click="clearCustomer">Clear</button>
                        </div>
                        <div class="position-relative mb-2">
                            <input
                                v-model="customerSearch"
                                type="search"
                                class="form-control form-control-sm"
                                placeholder="Phone or name (optional)"
                                @input="onCustomerSearchInput"
                            >
                            <div v-if="customerSearchResults.length" class="pos-suggest card">
                                <button
                                    v-for="customer in customerSearchResults"
                                    :key="customer.id"
                                    type="button"
                                    class="dropdown-item"
                                    @click="selectCustomer(customer)"
                                >
                                    {{ customer.name }} <span class="text-muted">{{ customer.mobile }}</span>
                                </button>
                            </div>
                        </div>
                        <div v-if="selectedCustomer" class="mb-2">
                            <p class="mb-0 fw-medium">{{ selectedCustomer.name }}</p>
                            <p v-if="customerPhoneDisplay" class="text-muted font-size-12 mb-0">{{ customerPhoneDisplay }}</p>
                        </div>
                        <p v-else class="text-muted font-size-12 mb-2">No customer selected — sale posts as Walk-in Customer.</p>
                        <div class="border rounded p-2 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="font-size-12 fw-semibold text-muted">Quick add</span>
                                <button type="button" class="btn btn-link btn-sm p-0" :disabled="quickCreating" @click="quickCreateCustomer">
                                    {{ quickCreating ? 'Saving…' : 'Save customer' }}
                                </button>
                            </div>
                            <input v-model="quickCustomer.name" type="text" class="form-control form-control-sm mb-1" placeholder="Name">
                            <input v-model="quickCustomer.mobile" type="tel" class="form-control form-control-sm" placeholder="Phone">
                            <p v-if="quickCustomerError" class="text-danger font-size-12 mb-0 mt-1">{{ quickCustomerError }}</p>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h5 class="card-title mb-0">Prescription</h5>
                                <span class="text-muted font-size-12">Optional</span>
                            </div>
                            <input ref="rxFileInput" type="file" accept="image/*,application/pdf" class="d-none" @change="onRxFile">
                            <div class="pos-rx-row">
                                <button
                                    v-if="prescriptionFile && rxPreviewUrl"
                                    type="button"
                                    class="pos-rx-thumb"
                                    title="View prescription"
                                    @click="showRxPreview = true"
                                >
                                    <img v-if="rxIsImage" :src="rxPreviewUrl" alt="Rx preview" @error="onRxPreviewError">
                                    <span v-else class="pos-rx-pdf"><i class="bi bi-file-earmark-pdf"></i></span>
                                </button>
                                <div v-else-if="prescriptionFile && rxPreviewLoading" class="pos-rx-thumb is-empty">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </div>
                                <div v-else class="pos-rx-thumb is-empty"><i class="bi bi-image"></i></div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-light" @click="pickRxFile">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light" @click="openRxCamera">
                                        <i class="bi bi-camera me-1"></i>Camera
                                    </button>
                                    <button v-if="prescriptionFile" type="button" class="btn btn-sm btn-outline-danger" @click="clearRx">Remove</button>
                                </div>
                            </div>
                            <p v-if="prescriptionFile" class="font-size-12 text-muted mb-0 mt-1 text-truncate">{{ prescriptionFile.name }}</p>
                            <p v-if="rxMessage && !showRxCamera" class="font-size-12 text-danger mb-0 mt-1">{{ rxMessage }}</p>
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

                        <div class="input-group input-group-sm mt-3 pos-discount-group">
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
                        <button
                            v-for="bill in heldBills"
                            :key="bill.id"
                            type="button"
                            class="btn btn-sm btn-light w-100 mb-1"
                            @click="router.post(route('pos.resume', bill.id))"
                        >
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
                    <p class="text-muted font-size-12">{{ selectedCustomer ? selectedCustomer.name : 'Walk-in Customer' }}{{ prescriptionFile ? ' · Rx attached' : '' }}</p>
                    <button class="btn btn-primary w-100" :disabled="isProcessing">{{ isProcessing ? 'Posting...' : 'Confirm and open invoice' }}</button>
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

        <div v-if="showRxCamera" class="pos-modal" @click.self="closeRxCamera">
            <div class="card pos-modal-card pos-modal-wide">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="bi bi-camera me-1"></i>Capture prescription</h5>
                    <button type="button" class="btn btn-sm btn-light" @click="closeRxCamera">Close</button>
                </div>
                <div class="card-body">
                    <video v-show="rxStreamReady" ref="rxVideoRef" class="pos-video" playsinline muted autoplay></video>
                    <div v-if="!rxStreamReady" class="pos-rx-placeholder">
                        <i class="bi bi-camera-video-off font-size-24 d-block mb-2"></i>
                        <p class="mb-0">{{ rxMessage || 'Starting camera…' }}</p>
                    </div>
                    <p v-if="rxStreamReady" class="text-muted mt-2 mb-0">{{ rxMessage || 'Frame the prescription, then capture.' }}</p>
                    <button v-if="rxStreamReady" type="button" class="btn btn-primary w-100 mt-3" @click="captureRx">
                        <i class="bi bi-camera me-1"></i>Capture photo
                    </button>
                    <button
                        v-else-if="rxMessage && rxMessage !== 'Starting camera…'"
                        type="button"
                        class="btn btn-light w-100 mt-3"
                        @click="closeRxCamera"
                    >
                        Use file upload instead
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showRxPreview && rxPreviewUrl" class="pos-modal" @click.self="showRxPreview = false">
            <div class="card pos-modal-card pos-modal-wide">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Prescription</h5>
                    <button type="button" class="btn btn-sm btn-light" @click="showRxPreview = false">Close</button>
                </div>
                <div class="card-body text-center">
                    <img v-if="rxIsImage" :src="rxPreviewUrl" alt="Prescription" class="pos-rx-large" @error="onRxPreviewError">
                    <a v-else :href="rxPreviewUrl" target="_blank" rel="noopener" class="btn btn-primary">Open PDF</a>
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
    medicines: { type: Array, default: () => [] },
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
    { key: 'Herbal', label: 'Herbal', icon: 'bi-flower1' },
    { key: 'Supplement', label: 'Supplement', icon: 'bi-heart-pulse' },
    { key: 'Device', label: 'Device', icon: 'bi-bandaid' },
]

const filter = ref('all')
const productSearch = ref('')
const productSearchInput = ref(null)
const catalog = ref([...(props.medicines || [])])
const searching = ref(false)
const medexLooking = ref(false)
const medexHits = ref([])
const medexSaving = ref('')
const medexNote = ref('')
const addingId = ref(null)
const cartItems = ref([])
const selectedCustomer = ref(null)
const customerSearch = ref('')
const customerSearchResults = ref([])
const discountCode = ref('')
const discountAmount = ref(0)
const discountNote = ref('')
const discountError = ref(false)
const submitError = ref('')
const isProcessing = ref(false)
const showPay = ref(false)
const pay = ref({ method: 'cash', amount: 0 })

let productSearchTimer = null
let customerSearchTimer = null
let searchSeq = 0

const stockOf = (product) => Number(product.stock_qty || 0)
const displayPrice = (product) => Number(product.price || 0)
const genericLabel = (product) => product.generic?.name || product.generic_name || product.dosage_form || 'Medicine'
const badge = (product) => {
    const category = (product.category?.name || '').toLowerCase()
    const typeName = (product.medicine_type?.name || product.medicineType?.name || '').toLowerCase()
    if (product.is_narcotic) return 'Narcotic'
    if (product.is_controlled) return 'Controlled'
    if (typeName === 'herbal' || category.includes('herbal')) return 'Herbal'
    if (category.includes('device') || typeName === 'device') return 'Device'
    if (category.includes('supplement')) return 'Supplement'
    return product.requires_prescription ? 'Rx' : 'OTC'
}
const badgeClass = (product) => {
    const kind = badge(product)
    if (kind === 'Narcotic') return 'badge-soft-danger'
    if (kind === 'Controlled') return 'badge-soft-warning'
    if (kind === 'Rx') return 'badge-soft-danger'
    if (kind === 'Herbal') return 'badge-soft-success'
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

const totalStock = computed(() => catalog.value.reduce((sum, product) => sum + stockOf(product), 0))
const lowStockCount = computed(() => catalog.value.filter((product) => stockOf(product) > 0 && stockOf(product) <= 10).length)
const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + Number(item.total || 0), 0))
const discount = computed(() => Number(discountAmount.value) || 0)
const tax = computed(() => Math.max(subtotal.value - discount.value, 0) * (taxRate.value / 100))
const total = computed(() => Math.max(subtotal.value - discount.value, 0) + tax.value)
const changeDue = computed(() => (pay.value.method === 'credit' ? 0 : Math.max(Number(pay.value.amount || 0) - total.value, 0)))
const stillDue = computed(() => (pay.value.method === 'credit' ? total.value : Math.max(total.value - Number(pay.value.amount || 0), 0)))

const fetchCatalog = async () => {
    const seq = ++searchSeq
    searching.value = true
    try {
        const params = new URLSearchParams({
            q: productSearch.value.trim(),
            kind: filter.value,
            limit: '36',
        })
        const response = await fetch(`${route('api.medicines.search')}?${params}`, {
            headers: { Accept: 'application/json' },
        })
        const data = await response.json()
        if (seq === searchSeq) catalog.value = Array.isArray(data) ? data : []
    } catch {
        if (seq === searchSeq) submitError.value = 'Medicine search failed. Try again.'
    } finally {
        if (seq === searchSeq) searching.value = false
    }
}

const onProductSearchInput = () => {
    medexHits.value = []
    medexNote.value = ''
    clearTimeout(productSearchTimer)
    productSearchTimer = setTimeout(fetchCatalog, 280)
}
const setFilter = (key) => {
    filter.value = key
    fetchCatalog()
}

const lookupMedex = async () => {
    const q = productSearch.value.trim()
    if (q.length < 2) return
    medexLooking.value = true
    medexNote.value = ''
    medexHits.value = []
    try {
        const res = await fetch(`${route('api.medex.search')}?q=${encodeURIComponent(q)}`, {
            headers: { Accept: 'application/json' },
        })
        const data = await res.json()
        medexHits.value = Array.isArray(data) ? data.slice(0, 8) : []
        if (!medexHits.value.length) medexNote.value = 'No MedEx matches. Local catalog still works offline.'
    } catch {
        medexNote.value = 'MedEx lookup failed. Use local catalog.'
    } finally {
        medexLooking.value = false
    }
}

const importMedexHit = async (hit) => {
    if (!hit?.link) return
    medexSaving.value = hit.link
    medexNote.value = ''
    try {
        const detailRes = await fetch(`${route('api.medex.product')}?url=${encodeURIComponent(hit.link)}`, {
            headers: { Accept: 'application/json' },
        })
        const detail = await detailRes.json()
        if (detail.error) {
            medexNote.value = detail.error
            return
        }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const saveRes = await fetch(route('api.medicines.storeExternal'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf || '',
            },
            body: JSON.stringify({
                name: detail.name || hit.name,
                generic_name: detail.generic || null,
                strength: detail.strength || hit.strength || null,
                dosage_form: detail.form || hit.form || null,
                manufacturer: detail.manufacturer || null,
                brand: detail.name || hit.name,
                medex_id: detail.medex_id || null,
                medex_name: detail.medex_name || null,
                details: detail,
                segment: 'allopathic',
            }),
        })
        const saved = await saveRes.json()
        if (!saveRes.ok) {
            medexNote.value = saved.message || 'Could not save medicine.'
            return
        }
        medexNote.value = 'Saved to catalog. Searching again…'
        medexHits.value = []
        await fetchCatalog()
    } catch {
        medexNote.value = 'Could not import from MedEx.'
    } finally {
        medexSaving.value = ''
    }
}
const searchEnter = () => {
    if (catalog.value.length === 1) addProduct(catalog.value[0])
}
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
    addingId.value = product.id
    const existing = cartItems.value.find((item) => item.id === product.id)
    if (existing && existing.quantity >= existing.stock) {
        addingId.value = null
        return
    }
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
    } else {
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
    addingId.value = null
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

const quickCustomer = ref({ name: '', mobile: '' })
const quickCustomerError = ref('')
const quickCreating = ref(false)
const prescriptionFile = ref(null)
const rxFileInput = ref(null)
const showRxCamera = ref(false)
const rxStreamReady = ref(false)
const rxMessage = ref('')
const rxVideoRef = ref(null)
const showRxPreview = ref(false)
const rxPreviewUrl = ref('')
const rxPreviewLoading = ref(false)
const rxIsImage = ref(false)
let rxStream = null
let rxObjectUrl = null
let rxReader = null

const isImageFile = (file) => {
    if (!file) return false
    if (String(file.type || '').startsWith('image/')) return true
    return /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(String(file.name || ''))
}

const revokeRxObjectUrl = () => {
    if (rxObjectUrl) {
        URL.revokeObjectURL(rxObjectUrl)
        rxObjectUrl = null
    }
}

const setRxPreview = (file) => {
    if (rxReader) {
        try { rxReader.abort() } catch (e) {}
        rxReader = null
    }
    revokeRxObjectUrl()
    rxPreviewUrl.value = ''
    rxIsImage.value = false
    rxPreviewLoading.value = false
    if (!file) return

    const image = isImageFile(file)
    rxIsImage.value = image
    rxPreviewLoading.value = true

    if (image) {
        const reader = new FileReader()
        rxReader = reader
        reader.onload = () => {
            if (rxReader !== reader) return
            rxPreviewUrl.value = String(reader.result || '')
            rxPreviewLoading.value = false
            rxReader = null
        }
        reader.onerror = () => {
            if (rxReader !== reader) return
            // Fallback blob URL if FileReader fails
            rxObjectUrl = URL.createObjectURL(file)
            rxPreviewUrl.value = rxObjectUrl
            rxPreviewLoading.value = false
            rxReader = null
        }
        reader.readAsDataURL(file)
        return
    }

    rxObjectUrl = URL.createObjectURL(file)
    rxPreviewUrl.value = rxObjectUrl
    rxPreviewLoading.value = false
}

const onRxPreviewError = () => {
    rxIsImage.value = false
}

const pickRxFile = () => {
    rxFileInput.value?.click()
}

const onCustomerSearchInput = () => {
    clearTimeout(customerSearchTimer)
    customerSearchTimer = setTimeout(searchCustomers, 250)
}
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
    customerSearch.value = ''
    customerSearchResults.value = []
    quickCustomer.value = { name: '', mobile: '' }
    quickCustomerError.value = ''
}
const clearCustomer = () => { selectedCustomer.value = null }
const customerPhoneDisplay = computed(() => selectedCustomer.value?.mobile || selectedCustomer.value?.phone || '')

const quickCreateCustomer = async () => {
    quickCustomerError.value = ''
    if (!quickCustomer.value.name.trim() || !quickCustomer.value.mobile.trim()) {
        quickCustomerError.value = 'Name and phone are required to create.'
        return
    }
    quickCreating.value = true
    try {
        const response = await fetch(route('api.customers.quick'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({
                name: quickCustomer.value.name.trim(),
                mobile: quickCustomer.value.mobile.trim(),
            }),
        })
        const data = await response.json()
        if (!response.ok) {
            quickCustomerError.value = data?.message || data?.errors?.mobile?.[0] || 'Could not create customer.'
            return
        }
        selectCustomer(data)
    } catch {
        quickCustomerError.value = 'Could not create customer.'
    } finally {
        quickCreating.value = false
    }
}

const onRxFile = (event) => {
    const file = event.target.files?.[0] || null
    prescriptionFile.value = file
    setRxPreview(file)
    rxMessage.value = ''
}
const clearRx = () => {
    prescriptionFile.value = null
    if (rxFileInput.value) rxFileInput.value.value = ''
    setRxPreview(null)
    rxMessage.value = ''
    showRxPreview.value = false
    closeRxCamera()
}
const openRxCamera = async () => {
    closeRxCamera()
    showRxCamera.value = true
    rxStreamReady.value = false
    rxMessage.value = 'Starting camera…'
    await nextTick()
    if (!navigator.mediaDevices?.getUserMedia) {
        rxMessage.value = 'Camera is not available in this browser. Use upload instead.'
        return
    }
    try {
        const devices = await navigator.mediaDevices.enumerateDevices()
        const cams = devices.filter((d) => d.kind === 'videoinput')
        if (!cams.length) {
            rxMessage.value = 'No camera found. Use upload instead.'
            return
        }
        rxStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: 'environment' },
                deviceId: cams[0]?.deviceId ? { ideal: cams[0].deviceId } : undefined,
            },
            audio: false,
        })
        if (rxVideoRef.value) {
            rxVideoRef.value.srcObject = rxStream
            await rxVideoRef.value.play()
            rxStreamReady.value = true
            rxMessage.value = 'Frame the prescription, then capture.'
        }
    } catch (err) {
        rxStreamReady.value = false
        const name = err?.name || ''
        if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
            rxMessage.value = 'Camera permission denied. Allow access or use upload.'
        } else if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
            rxMessage.value = 'No camera found. Use upload instead.'
        } else if (name === 'NotReadableError' || name === 'TrackStartError') {
            rxMessage.value = 'Camera is in use by another app.'
        } else {
            rxMessage.value = 'Camera unavailable. Use upload instead.'
        }
    }
}
const closeRxCamera = () => {
    if (rxStream) {
        rxStream.getTracks().forEach((t) => t.stop())
        rxStream = null
    }
    if (rxVideoRef.value) rxVideoRef.value.srcObject = null
    rxStreamReady.value = false
    showRxCamera.value = false
}
const captureRx = () => {
    const video = rxVideoRef.value
    if (!video || !rxStreamReady.value) return
    const canvas = document.createElement('canvas')
    canvas.width = video.videoWidth || 1280
    canvas.height = video.videoHeight || 720
    canvas.getContext('2d').drawImage(video, 0, 0)
    canvas.toBlob((blob) => {
        if (!blob) {
            rxMessage.value = 'Could not capture photo.'
            return
        }
        const file = new File([blob], `prescription-${Date.now()}.jpg`, { type: 'image/jpeg' })
        prescriptionFile.value = file
        setRxPreview(file)
        rxMessage.value = ''
        closeRxCamera()
    }, 'image/jpeg', 0.92)
}

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
    submitError.value = ''
    isProcessing.value = true
    const paid = pay.value.method === 'credit' ? 0 : Number(pay.value.amount || 0)
    router.post(route('invoices.store'), {
        customer_id: selectedCustomer.value?.id || selectedCustomer.value?.customer_id || null,
        payment_type: pay.value.method,
        paid_amount: paid,
        due_amount: Math.max(Number((total.value - paid).toFixed(2)), 0),
        invoice_no: props.invoiceNo,
        date: new Date().toISOString().slice(0, 10),
        total_amount: Number(total.value.toFixed(2)),
        total_tax: Number(tax.value.toFixed(2)),
        total_discount: Number(discount.value.toFixed(2)),
        items: cartItems.value.map((item) => ({
            medicine_id: item.medicine_id,
            quantity: item.quantity,
            rate: item.price,
            discount: 0,
            batch_id: item.batch_id,
        })),
        payments: paid > 0 ? [{ method: pay.value.method, amount: paid }] : [],
        prescription: prescriptionFile.value || null,
    }, {
        forceFormData: true,
        onSuccess: () => {
            cartItems.value = []
            selectedCustomer.value = null
            clearRx()
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
        currentStream?.getTracks().forEach((track) => track.stop())
        currentStream = null
    } catch (e) {}
}
const onCodeDetected = async (text) => {
    closeScanModal()
    productSearch.value = text
    await fetchCatalog()
    const match = catalog.value.find((product) => [product.barcode_data, product.product_id, product.sku, product.name]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase() === text.toLowerCase()))
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
        discountAmount.value = Number(props.resume.discount || 0)
    }
    pay.value.amount = Number(total.value.toFixed(2))
})
onUnmounted(() => {
    stopScanner()
    closeRxCamera()
    if (rxObjectUrl) URL.revokeObjectURL(rxObjectUrl)
    clearTimeout(productSearchTimer)
    clearTimeout(customerSearchTimer)
})
watch(total, (value) => { pay.value.amount = Number(value.toFixed(2)) })
watch(() => props.medicines, (rows) => {
    if (!productSearch.value && filter.value === 'all') catalog.value = [...(rows || [])]
})
</script>
