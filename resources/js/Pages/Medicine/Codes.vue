<template>
    <AuthenticatedLayout>
        <FormScreen :title="`Codes — ${medicine.name}`" :close-href="route('medicines.index')">
            <template #header-actions>
                <Link :href="route('medicines.show', medicine.id)" class="btn btn-outline-secondary btn-sm">Details</Link>
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    :disabled="isLoading"
                    @click="generateAllCodes"
                >
                    {{ isLoading ? 'Generating…' : 'Generate all' }}
                </button>
            </template>

            <div class="med-view">
                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Product identity</h6>
                            <p>What these codes will encode</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Brand name</dt>
                                <dd>{{ medicine.name }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Generic name</dt>
                                <dd>{{ medicine.generic_name || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Strength</dt>
                                <dd>{{ medicine.strength || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Product ID</dt>
                                <dd class="mono">{{ medicine.product_id || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Category</dt>
                                <dd>{{ medicine.category?.name || '—' }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Manufacturer</dt>
                                <dd>{{ medicine.manufacturer?.name || '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Code status</h6>
                            <p>Saved on this medicine record</p>
                        </header>
                        <div class="med-metrics">
                            <div class="med-metric" :class="{ accent: !!medicine.qr_code_data }">
                                <span class="med-metric-label">QR code</span>
                                <span class="med-metric-value status-line">
                                    <i class="bi" :class="medicine.qr_code_data ? 'bi-check-circle-fill' : 'bi-dash-circle'" />
                                    {{ medicine.qr_code_data ? 'Saved' : 'Not saved' }}
                                </span>
                            </div>
                            <div class="med-metric" :class="{ accent: !!medicine.barcode_data }">
                                <span class="med-metric-label">Barcode</span>
                                <span class="med-metric-value status-line">
                                    <i class="bi" :class="medicine.barcode_data ? 'bi-check-circle-fill' : 'bi-dash-circle'" />
                                    {{ medicine.barcode_data ? 'Saved' : 'Not saved' }}
                                </span>
                            </div>
                        </div>
                        <dl v-if="medicine.barcode_data" class="med-facts med-facts-tight">
                            <div class="med-fact">
                                <dt>Barcode value</dt>
                                <dd class="mono">{{ medicine.barcode_data }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <section class="med-panel code-section">
                    <header class="med-panel-head code-head">
                        <div>
                            <h6>Codes</h6>
                            <p>Generate, size, download, then save to this medicine</p>
                        </div>
                        <div class="code-actions">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                :disabled="isLoading"
                                @click="generateQrCode"
                            >
                                <i class="bi bi-qr-code me-1" />
                                QR
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                :disabled="isLoading"
                                @click="generateBarcode"
                            >
                                <i class="bi bi-upc me-1" />
                                Barcode
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                :disabled="isLoading"
                                @click="generateAllCodes"
                            >
                                {{ isLoading ? 'Generating…' : 'Generate both' }}
                            </button>
                            <button
                                v-if="canDownloadBoth"
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="downloadBothCodes"
                            >
                                <i class="bi bi-download me-1" />
                                Download both
                            </button>
                            <button
                                v-if="canDownloadBoth"
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="printBothCodes"
                            >
                                <i class="bi bi-printer me-1" />
                                Print both
                            </button>
                        </div>
                    </header>

                    <div v-if="showPreview" class="code-size-row">
                        <div v-if="modalType === 'qr' || modalType === 'both'" class="code-size-field">
                            <span>QR size (px)</span>
                            <div class="code-size-controls">
                                <button
                                    v-for="size in qrSizePresets"
                                    :key="`qr-${size}`"
                                    type="button"
                                    class="btn btn-sm"
                                    :class="Number(qrSize) === size ? 'btn-primary' : 'btn-outline-secondary'"
                                    @click="setQrSize(size)"
                                >
                                    {{ size }}
                                </button>
                                <input
                                    v-model="qrSizeDraft"
                                    type="number"
                                    min="96"
                                    max="360"
                                    step="1"
                                    @input="onQrSizeInput"
                                    @keydown.enter.prevent="commitQrSize"
                                    @blur="commitQrSize"
                                >
                            </div>
                        </div>
                        <div v-if="modalType === 'barcode' || modalType === 'both'" class="code-size-field">
                            <span>Barcode height (px)</span>
                            <div class="code-size-controls">
                                <button
                                    v-for="size in barcodeSizePresets"
                                    :key="`bc-${size}`"
                                    type="button"
                                    class="btn btn-sm"
                                    :class="Number(barcodeSize) === size ? 'btn-primary' : 'btn-outline-secondary'"
                                    @click="setBarcodeSize(size)"
                                >
                                    {{ size }}
                                </button>
                                <input
                                    v-model="barcodeSizeDraft"
                                    type="number"
                                    min="48"
                                    max="200"
                                    step="1"
                                    @input="onBarcodeSizeInput"
                                    @keydown.enter.prevent="commitBarcodeSize"
                                    @blur="commitBarcodeSize"
                                >
                            </div>
                        </div>
                    </div>

                    <div v-if="!showPreview" class="code-empty">
                        <i class="bi bi-upc-scan" />
                        <p>No preview yet. Generate a QR code, barcode, or both.</p>
                    </div>

                    <div v-else class="code-preview-grid" :class="{ 'is-single': modalType !== 'both' }">
                        <article v-if="modalType === 'qr' || modalType === 'both'" class="code-card">
                            <header class="code-card-head">
                                <span class="code-card-title">QR code</span>
                                <span class="code-chip">{{ qrSize }}px</span>
                            </header>
                            <div class="code-card-canvas">
                                <canvas ref="qrCodeCanvas" />
                            </div>
                            <dl class="code-card-meta">
                                <div>
                                    <dt>Encodes</dt>
                                    <dd>Name, product ID, strength, maker</dd>
                                </div>
                                <div>
                                    <dt>Product ID</dt>
                                    <dd class="mono">{{ medicine.product_id || '—' }}</dd>
                                </div>
                            </dl>
                            <footer class="code-card-foot">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="downloadQrCode">
                                    <i class="bi bi-download me-1" />
                                    Download
                                </button>
                            </footer>
                        </article>

                        <article v-if="modalType === 'barcode' || modalType === 'both'" class="code-card">
                            <header class="code-card-head">
                                <span class="code-card-title">Barcode</span>
                                <span class="code-chip">{{ barcodeSize }}px · Code 128</span>
                            </header>
                            <div class="code-card-canvas is-barcode">
                                <canvas ref="barcodeCanvas" />
                            </div>
                            <dl class="code-card-meta">
                                <div>
                                    <dt>Format</dt>
                                    <dd>Code 128</dd>
                                </div>
                                <div>
                                    <dt>Value</dt>
                                    <dd class="mono">{{ generatedBarcodeData || medicine.product_id || '—' }}</dd>
                                </div>
                            </dl>
                            <footer class="code-card-foot">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="downloadBarcode">
                                    <i class="bi bi-download me-1" />
                                    Download
                                </button>
                            </footer>
                        </article>
                    </div>
                </section>
            </div>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button
                    v-if="showPreview"
                    type="button"
                    class="btn btn-primary"
                    :disabled="isSaving"
                    @click="saveCodes"
                >
                    {{ isSaving ? 'Saving…' : 'Save codes' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import QRCode from 'qrcode'
import JsBarcode from 'jsbarcode'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    medicine: Object,
})

const isLoading = ref(false)
const isSaving = ref(false)
const showPreview = ref(false)
const modalType = ref('both')
const generatedQrCodeData = ref('')
const generatedQrCodeType = ref('product_id')
const generatedBarcodeData = ref('')
const generatedBarcodeType = ref('code128')
const qrCodeCanvas = ref(null)
const barcodeCanvas = ref(null)
const qrSizePresets = [120, 160, 200, 240]
const barcodeSizePresets = [60, 88, 120, 160]
const qrSize = ref(Number(props.medicine.qr_code_size) || 180)
const barcodeSize = ref(Number(props.medicine.barcode_size) || 88)
const qrSizeDraft = ref(String(qrSize.value))
const barcodeSizeDraft = ref(String(barcodeSize.value))

const canDownloadBoth = computed(() => (
    showPreview.value
    && modalType.value === 'both'
    && !!generatedQrCodeData.value
    && !!generatedBarcodeData.value
))

const clamp = (value, min, max, fallback) => {
    const n = Number(value)
    if (! Number.isFinite(n)) return fallback
    return Math.min(max, Math.max(min, Math.round(n)))
}

let qrSizeTimer = null
let barcodeSizeTimer = null

const setQrSize = (size, { syncDraft = true } = {}) => {
    const next = clamp(size, 96, 360, qrSize.value || 180)
    if (qrSize.value !== next) {
        qrSize.value = next
    }
    if (syncDraft) {
        qrSizeDraft.value = String(next)
    }
}

const setBarcodeSize = (size, { syncDraft = true } = {}) => {
    const next = clamp(size, 48, 200, barcodeSize.value || 88)
    if (barcodeSize.value !== next) {
        barcodeSize.value = next
    }
    if (syncDraft) {
        barcodeSizeDraft.value = String(next)
    }
}

const commitQrSize = () => {
    window.clearTimeout(qrSizeTimer)
    if (qrSizeDraft.value === '') {
        qrSizeDraft.value = String(qrSize.value)
        return
    }
    // Keep typing fluid; only clamp/sync draft when value is complete enough.
    const raw = Number(qrSizeDraft.value)
    if (!Number.isFinite(raw)) return
    setQrSize(raw, { syncDraft: false })
    // Reflect clamped value once settled (blur/enter) or when out of range.
    if (raw < 96 || raw > 360) {
        qrSizeDraft.value = String(qrSize.value)
    }
}

const commitBarcodeSize = () => {
    window.clearTimeout(barcodeSizeTimer)
    if (barcodeSizeDraft.value === '') {
        barcodeSizeDraft.value = String(barcodeSize.value)
        return
    }
    const raw = Number(barcodeSizeDraft.value)
    if (!Number.isFinite(raw)) return
    setBarcodeSize(raw, { syncDraft: false })
    if (raw < 48 || raw > 200) {
        barcodeSizeDraft.value = String(barcodeSize.value)
    }
}

const onQrSizeInput = () => {
    window.clearTimeout(qrSizeTimer)
    qrSizeTimer = window.setTimeout(() => commitQrSize(), 80)
}

const onBarcodeSizeInput = () => {
    window.clearTimeout(barcodeSizeTimer)
    barcodeSizeTimer = window.setTimeout(() => commitBarcodeSize(), 80)
}

const barcodeFormat = (type) => {
    const key = String(type || 'code128').toLowerCase()
    return {
        code128: 'CODE128',
        code39: 'CODE39',
        ean13: 'EAN13',
        upc: 'UPC',
    }[key] || 'CODE128'
}

const barcodeTypeForSave = (type) => {
    const raw = String(type || 'code128')
    const map = {
        CODE128: 'code128',
        CODE39: 'code39',
        EAN13: 'ean13',
        UPC: 'upc',
    }
    return map[raw] || raw.toLowerCase()
}

const medicinePayload = () => JSON.stringify({
    name: props.medicine.name,
    product_id: props.medicine.product_id,
    generic_name: props.medicine.generic_name,
    strength: props.medicine.strength,
    category: props.medicine.category?.name,
    manufacturer: props.medicine.manufacturer?.name,
})

const clearCanvas = (canvas, width, height) => {
    if (!canvas) return
    canvas.width = Math.max(1, Math.round(width))
    canvas.height = Math.max(1, Math.round(height))
    const ctx = canvas.getContext('2d')
    ctx?.clearRect(0, 0, canvas.width, canvas.height)
}

const renderCodes = async () => {
    await nextTick()

    const nextQrSize = clamp(qrSize.value, 96, 360, 180)
    const nextBarcodeSize = clamp(barcodeSize.value, 48, 200, 88)

    if ((modalType.value === 'qr' || modalType.value === 'both') && generatedQrCodeData.value && qrCodeCanvas.value) {
        // Reset bitmap first so reducing size always redraws smaller.
        clearCanvas(qrCodeCanvas.value, nextQrSize, nextQrSize)
        await QRCode.toCanvas(qrCodeCanvas.value, generatedQrCodeData.value, {
            width: nextQrSize,
            margin: 1,
            color: { dark: '#000000', light: '#FFFFFF' },
        })
    }

    if ((modalType.value === 'barcode' || modalType.value === 'both') && generatedBarcodeData.value && barcodeCanvas.value) {
        clearCanvas(barcodeCanvas.value, 320, nextBarcodeSize + 40)
        const barWidth = nextBarcodeSize >= 120 ? 2.4 : 2
        JsBarcode(barcodeCanvas.value, generatedBarcodeData.value, {
            format: barcodeFormat(generatedBarcodeType.value),
            width: barWidth,
            height: nextBarcodeSize,
            displayValue: true,
            fontSize: Math.max(10, Math.round(nextBarcodeSize / 8)),
            margin: 10,
        })
    }
}

const loadSavedCodes = async () => {
    const hasQr = !!props.medicine.qr_code_data
    const hasBarcode = !!props.medicine.barcode_data

    setQrSize(props.medicine.qr_code_size || 180)
    setBarcodeSize(props.medicine.barcode_size || 88)

    if (!hasQr && !hasBarcode) {
        showPreview.value = false
        return
    }

    if (hasQr && hasBarcode) {
        modalType.value = 'both'
    } else if (hasQr) {
        modalType.value = 'qr'
    } else {
        modalType.value = 'barcode'
    }

    generatedQrCodeData.value = props.medicine.qr_code_data || ''
    generatedQrCodeType.value = props.medicine.qr_code_type || 'medicine_info'
    generatedBarcodeData.value = props.medicine.barcode_data || ''
    generatedBarcodeType.value = props.medicine.barcode_type || 'code128'
    showPreview.value = true
    await renderCodes()
}

onMounted(() => {
    loadSavedCodes()
})

watch(
    () => [props.medicine.qr_code_data, props.medicine.barcode_data, props.medicine.qr_code_size, props.medicine.barcode_size],
    () => {
        loadSavedCodes()
    },
)

watch([qrSize, barcodeSize], async () => {
    if (showPreview.value) {
        await renderCodes()
    }
}, { flush: 'post' })

const generateQrCode = async () => {
    isLoading.value = true
    generatedQrCodeData.value = medicinePayload()
    generatedQrCodeType.value = 'medicine_info'
    generatedBarcodeData.value = ''
    modalType.value = 'qr'
    showPreview.value = true
    await renderCodes()
    isLoading.value = false
}

const generateBarcode = async () => {
    isLoading.value = true
    generatedBarcodeData.value = props.medicine.product_id
    generatedBarcodeType.value = 'code128'
    generatedQrCodeData.value = ''
    modalType.value = 'barcode'
    showPreview.value = true
    await renderCodes()
    isLoading.value = false
}

const generateAllCodes = async () => {
    isLoading.value = true
    generatedQrCodeData.value = medicinePayload()
    generatedQrCodeType.value = 'medicine_info'
    generatedBarcodeData.value = props.medicine.product_id
    generatedBarcodeType.value = 'code128'
    modalType.value = 'both'
    showPreview.value = true
    await renderCodes()
    isLoading.value = false
}

const downloadQrCode = () => {
    if (!qrCodeCanvas.value) return
    const link = document.createElement('a')
    link.download = `qr-code-${props.medicine.name.replace(/\s+/g, '-').toLowerCase()}.png`
    link.href = qrCodeCanvas.value.toDataURL()
    link.click()
}

const downloadBarcode = () => {
    if (!barcodeCanvas.value) return
    const link = document.createElement('a')
    link.download = `barcode-${props.medicine.name.replace(/\s+/g, '-').toLowerCase()}.png`
    link.href = barcodeCanvas.value.toDataURL()
    link.click()
}

const buildCombinedCanvas = () => {
    if (!qrCodeCanvas.value || !barcodeCanvas.value) return null
    const qr = qrCodeCanvas.value
    const barcode = barcodeCanvas.value
    const gap = 24
    const pad = 24
    const labelH = 24
    const combinedCanvas = document.createElement('canvas')
    const ctx = combinedCanvas.getContext('2d')
    const width = Math.max(qr.width, barcode.width) + pad * 2
    const height = labelH + qr.height + gap + labelH + barcode.height + pad
    combinedCanvas.width = width
    combinedCanvas.height = height
    ctx.fillStyle = 'white'
    ctx.fillRect(0, 0, width, height)
    ctx.fillStyle = 'black'
    ctx.font = '14px sans-serif'
    ctx.fillText('QR Code', pad, 18)
    ctx.drawImage(qr, Math.round((width - qr.width) / 2), labelH)
    const barcodeTop = labelH + qr.height + gap
    ctx.fillText('Barcode', pad, barcodeTop + 14)
    ctx.drawImage(barcode, Math.round((width - barcode.width) / 2), barcodeTop + labelH)
    return combinedCanvas
}

const downloadBothCodes = () => {
    const combinedCanvas = buildCombinedCanvas()
    if (!combinedCanvas) return
    const link = document.createElement('a')
    link.download = `codes-${props.medicine.name.replace(/\s+/g, '-').toLowerCase()}.png`
    link.href = combinedCanvas.toDataURL()
    link.click()
}

const printBothCodes = () => {
    const combinedCanvas = buildCombinedCanvas()
    if (!combinedCanvas) return
    const printWindow = window.open('', '_blank')
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Codes - ${props.medicine.name}</title>
                <style>
                    body { margin: 0; padding: 20px; text-align: center; font-family: sans-serif; }
                    img { max-width: 100%; height: auto; }
                </style>
            </head>
            <body>
                <h2>${props.medicine.name} — QR Code &amp; Barcode</h2>
                <img src="${combinedCanvas.toDataURL()}" alt="Codes">
            </body>
        </html>
    `)
    printWindow.document.close()
    printWindow.print()
}

const saveCodes = () => {
    const saveData = {
        qr_code_size: clamp(qrSize.value, 96, 360, 180),
        barcode_size: clamp(barcodeSize.value, 48, 200, 88),
    }

    if (modalType.value === 'qr' || modalType.value === 'both') {
        saveData.qr_code_data = generatedQrCodeData.value
        saveData.qr_code_type = generatedQrCodeType.value
        saveData.qr_code_image_path = null
    }

    if (modalType.value === 'barcode' || modalType.value === 'both') {
        saveData.barcode_data = generatedBarcodeData.value
        saveData.barcode_type = barcodeTypeForSave(generatedBarcodeType.value)
        saveData.barcode_image_path = null
    }

    isSaving.value = true
    router.post(route('medicines.codes.save', props.medicine.id), saveData, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false
        },
        onSuccess: () => {
            showPreview.value = true
            router.reload({
                only: ['medicine'],
                onSuccess: () => {
                    loadSavedCodes()
                },
            })
        },
    })
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    padding: 1rem 1.1rem 1.15rem;
}

.med-metric {
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    padding: 0.75rem 0.85rem;
}

.med-panel-preview {
    background: var(--shell-panel-surface, #fff);
}

.med-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem 1rem;
    margin: 0;
}

.med-facts-tight {
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px dashed var(--shell-panel-border, #e6e8ee);
}

.med-fact {
    min-width: 0;
}

.med-fact dt {
    margin: 0 0 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-fact dd {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    word-break: break-word;
}

.med-fact dd.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 0.84rem;
    font-weight: 500;
}

.med-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.med-metric {
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.5rem;
    padding: 0.75rem 0.85rem;
}

.med-metric.accent {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
}

.med-metric-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-metric-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
}

.med-metric.accent .med-metric-value {
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-line {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.code-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.code-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.45rem;
    flex-shrink: 0;
}

.code-section .btn,
.code-section input {
    border-radius: var(--pf-radius, 10px) !important;
}

.code-size-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
    margin-bottom: 0.85rem;
}

.code-size-field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin: 0;
    padding: 0.7rem 0.8rem;
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
}

.code-size-field > span {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.code-size-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
}

.code-size-controls input {
    width: 4.5rem;
    height: 1.9rem;
    border: 1px solid var(--shell-panel-border, #dfe3ea);
    padding: 0 0.45rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
}

.code-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    min-height: 9rem;
    border: 1px dashed var(--shell-panel-border, #dfe3ea);
    border-radius: var(--pf-radius, 10px);
    background: var(--shell-panel-surface, #fff);
    color: var(--shell-panel-muted, #74788d);
    text-align: center;
    padding: 1.25rem;
}

.code-empty i {
    font-size: 1.55rem;
    color: var(--shell-panel-muted, #adb5bd);
}

.code-empty p {
    margin: 0;
    font-size: 0.84rem;
}

.code-preview-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.code-preview-grid.is-single {
    grid-template-columns: minmax(0, 22rem);
}

.code-card {
    display: flex;
    flex-direction: column;
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    overflow: hidden;
    min-height: 100%;
}

.code-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.7rem 0.85rem;
    border-bottom: 1px solid var(--shell-panel-border, #eef0f4);
}

.code-card-title {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.code-chip {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: var(--pf-radius, 10px);
    padding: 0.15rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
    white-space: nowrap;
}

.code-card-canvas {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 11rem;
    padding: 1rem;
    background: var(--shell-panel-canvas, #fafbfc);
}

.code-card-canvas.is-barcode {
    min-height: 11rem;
    padding: 1.25rem 0.75rem;
}

.code-card-canvas canvas {
    max-width: 100%;
    height: auto;
}

.code-card-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem 0.85rem;
    margin: 0;
    padding: 0.75rem 0.85rem;
    border-top: 1px solid var(--shell-panel-border, #eef0f4);
}

.code-card-meta dt {
    margin: 0 0 0.15rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.code-card-meta dd {
    margin: 0;
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    word-break: break-word;
}

.code-card-meta dd.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 0.78rem;
    font-weight: 500;
}

.code-card-foot {
    margin-top: auto;
    padding: 0.65rem 0.85rem 0.8rem;
    border-top: 1px solid var(--shell-panel-border, #eef0f4);
    background: var(--shell-panel-surface, #fff);
}

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts,
    .med-metrics,
    .code-preview-grid,
    .code-card-meta,
    .code-size-row {
        grid-template-columns: 1fr;
    }

    .code-preview-grid.is-single {
        grid-template-columns: 1fr;
    }

    .code-head {
        flex-direction: column;
    }

    .code-actions {
        justify-content: flex-start;
    }
}
</style>
