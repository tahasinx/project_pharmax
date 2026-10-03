<template>
    <AuthenticatedLayout>
        <FormScreen :title="medicine.name" :close-href="route('medicines.index')">
            <template #header-actions>
                <span class="status-chip" :class="{ 'is-on': medicine.status }">
                    <span class="status-dot" />
                    {{ medicine.status ? 'Active' : 'Inactive' }}
                </span>
                <Link :href="route('medicines.edit', medicine.id)" class="btn btn-primary btn-sm">Edit</Link>
                <Link :href="route('medicines.codes', medicine.id)" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-journal-code me-1" />
                    Codes
                </Link>
            </template>

            <div class="med-view">
                <div class="med-tabs">
                    <button type="button" class="med-tab" :class="{ active: activeTab === 'catalog' }" @click="activeTab = 'catalog'">
                        Catalog overview
                    </button>
                    <button type="button" class="med-tab" :class="{ active: activeTab === 'reference' }" @click="openReferenceTab">
                        Reference details
                    </button>
                </div>

                <div v-if="activeTab === 'catalog'" class="med-tab-panel">
                    <section v-if="imageUrl" class="med-hero">
                        <img :src="imageUrl" :alt="medicine.name" class="med-hero-image">
                        <div class="med-hero-copy">
                            <h6>{{ medicine.name }}</h6>
                            <p>{{ [medicine.generic_name, medicine.strength, medicine.dosage_form || medicine.medicine_type?.name].filter(Boolean).join(' · ') || 'Catalog product' }}</p>
                        </div>
                    </section>

                    <div class="med-view-grid">
                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Product identity</h6>
                                <p>Catalog and classification</p>
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
                                <div class="med-fact">
                                    <dt>Type</dt>
                                    <dd>{{ medicine.medicine_type?.name || '—' }}</dd>
                                </div>
                                <div class="med-fact">
                                    <dt>Unit</dt>
                                    <dd>{{ medicine.unit || '—' }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Pricing</h6>
                                <p>Trade, retail, and margin</p>
                            </header>
                            <div class="med-metrics">
                                <div class="med-metric">
                                    <span class="med-metric-label">Box MRP</span>
                                    <span class="med-metric-value">{{ money(medicine.price) }}</span>
                                </div>
                                <div class="med-metric">
                                    <span class="med-metric-label">Box TP</span>
                                    <span class="med-metric-value">{{ money(medicine.manufacturer_price) }}</span>
                                </div>
                                <div class="med-metric">
                                    <span class="med-metric-label">Box size</span>
                                    <span class="med-metric-value">{{ medicine.box_size || 1 }} {{ medicine.unit || 'units' }}</span>
                                </div>
                                <div class="med-metric accent">
                                    <span class="med-metric-label">Profit</span>
                                    <span class="med-metric-value">{{ money(profitAmount) }} <small>({{ profitPercentage }}%)</small></span>
                                </div>
                            </div>
                            <dl v-if="medicine.discount_percent > 0" class="med-facts med-facts-tight">
                                <div class="med-fact">
                                    <dt>Discount</dt>
                                    <dd>{{ Number(medicine.discount_percent).toFixed(2) }}%</dd>
                                </div>
                            </dl>
                        </section>
                    </div>

                    <div class="med-view-grid">
                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Inventory</h6>
                                <p>Location and movement</p>
                            </header>
                            <dl class="med-facts">
                                <div class="med-fact">
                                    <dt>Rack / shelf</dt>
                                    <dd>{{ medicine.product_location || '—' }}</dd>
                                </div>
                                <div class="med-fact">
                                    <dt>Alert qty</dt>
                                    <dd>{{ medicine.alert_qty ?? 0 }}</dd>
                                </div>
                                <div class="med-fact">
                                    <dt>Purchases</dt>
                                    <dd>{{ medicine.purchase_items_count || 0 }}</dd>
                                </div>
                                <div class="med-fact">
                                    <dt>Sales</dt>
                                    <dd>{{ medicine.invoice_items_count || 0 }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="med-panel">
                            <header class="med-panel-head">
                                <h6>Record</h6>
                                <p>Lifecycle timestamps</p>
                            </header>
                            <dl class="med-facts">
                                <div class="med-fact">
                                    <dt>Created</dt>
                                    <dd>{{ formatDate(medicine.created_at) }}</dd>
                                </div>
                                <div class="med-fact">
                                    <dt>Updated</dt>
                                    <dd>{{ formatDate(medicine.updated_at) }}</dd>
                                </div>
                                <div class="med-fact" v-if="medicine.barcode_data">
                                    <dt>Barcode</dt>
                                    <dd class="mono">{{ medicine.barcode_data }}</dd>
                                </div>
                            </dl>
                        </section>
                    </div>

                    <section v-if="staffNotes" class="med-panel med-panel-notes">
                        <header class="med-panel-head">
                            <h6>Notes</h6>
                            <p>Staff description</p>
                        </header>
                        <p class="med-notes">{{ staffNotes }}</p>
                    </section>
                </div>

                <div v-else class="med-tab-panel">
                    <div v-if="referenceLoading" class="med-ref-empty">Loading reference details…</div>
                    <template v-else-if="referenceDetails">
                        <div class="med-ref-head">
                            <div class="med-ref-identity">
                                <img
                                    v-if="referenceDetails.image || referenceDetails.img || imageUrl"
                                    :src="referenceDetails.image || referenceDetails.img || imageUrl"
                                    :alt="referenceDetails.name || medicine.name"
                                    class="med-ref-thumb"
                                >
                                <div class="min-w-0">
                                    <h5 class="med-ref-name">{{ referenceDetails.name || medicine.name }}</h5>
                                    <p class="med-ref-meta">
                                        {{ [referenceDetails.generic || medicine.generic_name, referenceDetails.form || medicine.dosage_form, referenceDetails.strength || medicine.strength].filter(Boolean).join(' · ') }}
                                    </p>
                                    <p class="med-ref-meta">{{ referenceDetails.manufacturer || medicine.manufacturer?.name }}</p>
                                </div>
                            </div>
                            <span class="med-ref-badge">{{ referenceBadge }}</span>
                        </div>

                        <div v-if="referenceFacts.length" class="med-ref-facts">
                            <div v-for="fact in referenceFacts" :key="fact.label" class="med-ref-fact">
                                <span>{{ fact.label }}</span>
                                <strong>{{ fact.value }}</strong>
                            </div>
                        </div>

                        <section
                            v-for="section in referenceSections"
                            :key="section.label"
                            class="med-panel med-panel-notes"
                        >
                            <header class="med-panel-head">
                                <h6>{{ section.label }}</h6>
                                <p>Reference monograph</p>
                            </header>
                            <div v-if="section.html" class="med-notes" v-html="section.value" />
                            <p v-else class="med-notes">{{ section.value }}</p>
                        </section>
                    </template>
                    <div v-else class="med-ref-empty">
                        <i class="bi bi-journal-richtext" />
                        <p>{{ referenceNote }}</p>
                    </div>
                </div>
            </div>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    medicine: Object
})

const page = usePage()
const activeTab = ref('catalog')
const referenceLoading = ref(false)
const referenceDetails = ref(null)
const referenceFetched = ref(false)
const referenceError = ref('')

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toFixed(2)
    const symbol = ui.currency_symbol || ''
    return ui.currency_position === 'after' ? `${amount}${symbol}` : `${symbol}${amount}`
}

const profitAmount = computed(() => {
    const price = parseFloat(props.medicine.price) || 0
    const manufacturerPrice = parseFloat(props.medicine.manufacturer_price) || 0
    return (price - manufacturerPrice).toFixed(2)
})

const profitPercentage = computed(() => {
    const price = parseFloat(props.medicine.price) || 0
    const manufacturerPrice = parseFloat(props.medicine.manufacturer_price) || 0
    if (manufacturerPrice === 0) return '0.0'
    return (((price - manufacturerPrice) / manufacturerPrice) * 100).toFixed(1)
})

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const imageUrl = computed(() => {
    const path = props.medicine.image
    if (!path) return ''
    if (/^https?:\/\//i.test(path) || path.startsWith('/storage/') || path.startsWith('blob:')) return path
    return `/storage/${path.replace(/^\/+/, '')}`
})

const parsedDetails = computed(() => {
    const raw = props.medicine.details
    if (!raw) return null
    if (typeof raw === 'object') return raw
    try {
        return JSON.parse(raw)
    } catch {
        return null
    }
})

const staffNotes = computed(() => {
    if (parsedDetails.value) return ''
    return typeof props.medicine.details === 'string' ? props.medicine.details : ''
})

const storedReferenceSections = computed(() => {
    const sections = parsedDetails.value?.sections
    if (!sections || typeof sections !== 'object') return []
    return Object.entries(sections)
        .filter(([, value]) => value)
        .map(([label, value]) => ({
            label: label.replace(/_/g, ' '),
            value,
            html: label === 'dosage',
        }))
})

const referenceFacts = computed(() => {
    const details = referenceDetails.value
    if (!details) return []
    return [
        ['Therapeutic class', details.therapeutic_class],
        ['Unit price', details.unit_price],
        ['Strip price', details.strip_price],
        ['Pack size', details.pack_size],
        ['Storage', details.storage],
    ].filter((item) => item[1]).map(([label, value]) => ({ label, value }))
})

const referenceSections = computed(() => {
    const details = referenceDetails.value
    if (!details) return storedReferenceSections.value
    const live = [
        ['Indications', details.indications, details.indications_html],
        ['Pharmacology', details.pharmacology, details.pharmacology_html],
        ['Dosage', details.dosage, details.dosage_html ?? true],
        ['Interaction', details.interaction, details.interaction_html],
        ['Contraindications', details.contraindications, details.contraindications_html],
        ['Side effects', details.side_effects, details.side_effects_html],
        ['Pregnancy and lactation', details.pregnancy_lactation || details.pregnancy, details.pregnancy_html],
        ['Precautions', details.precautions, details.precautions_html],
        ['Overdose', details.overdose, details.overdose_html],
        ['Storage', details.storage, details.storage_html],
    ].filter((item) => item[1]).map(([label, value, html]) => ({ label, value, html: Boolean(html) }))
    return live.length ? live : storedReferenceSections.value
})

const referenceNote = computed(() => {
    if (referenceError.value) return referenceError.value
    if (storedReferenceSections.value.length) return 'Stored reference sections are available below after refresh.'
    if (!props.medicine.medex_id) return 'This medicine has no linked reference source yet.'
    return 'Reference details are unavailable right now.'
})

const referenceBadge = computed(() => (
    referenceDetails.value?.indications || referenceDetails.value?.pharmacology
        ? 'Direct reference'
        : 'Stored reference'
))

const openReferenceTab = async () => {
    activeTab.value = 'reference'
    if (referenceFetched.value || referenceLoading.value) return

    if (props.medicine.medex_id && props.medicine.medex_name) {
        referenceLoading.value = true
        referenceError.value = ''
        try {
            const medexUrl = `https://medex.com.bd/brands/${props.medicine.medex_id}/${props.medicine.medex_name}`
            const response = await fetch(`${route('api.medex.product')}?url=${encodeURIComponent(medexUrl)}`)
            if (!response.ok) throw new Error('lookup failed')
            referenceDetails.value = await response.json()
        } catch {
            referenceError.value = storedReferenceSections.value.length
                ? 'Live lookup failed. Showing stored reference details.'
                : 'Could not load live reference details.'
            if (storedReferenceSections.value.length) {
                referenceDetails.value = {
                    name: props.medicine.name,
                    generic: props.medicine.generic_name,
                    form: props.medicine.dosage_form,
                    strength: props.medicine.strength,
                    manufacturer: props.medicine.manufacturer?.name,
                }
            }
        } finally {
            referenceLoading.value = false
            referenceFetched.value = true
        }
        return
    }

    if (storedReferenceSections.value.length) {
        referenceDetails.value = {
            name: props.medicine.name,
            generic: props.medicine.generic_name,
            form: props.medicine.dosage_form,
            strength: props.medicine.strength,
            manufacturer: props.medicine.manufacturer?.name,
        }
    }
    referenceFetched.value = true
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-tabs {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.2rem;
    border-radius: var(--pf-radius, 0.55rem);
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    align-self: flex-start;
}

.med-tab {
    border: 0;
    background: transparent;
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.85rem;
    border-radius: calc(var(--pf-radius, 0.55rem) - 0.1rem);
}

.med-tab.active {
    background: var(--bs-primary, #5156be);
    color: #fff;
}

.med-tab-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-hero {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.65rem;
    background: var(--shell-panel-bg, #f8f9fc);
}

.med-hero-image {
    width: 5.5rem;
    height: 5.5rem;
    object-fit: contain;
    border-radius: 0.55rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-surface, #fff);
    flex-shrink: 0;
}

.med-hero-copy h6 {
    margin: 0;
    font-size: 1rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.med-hero-copy p {
    margin: 0.25rem 0 0;
    font-size: 0.82rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.65rem;
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-notes {
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
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
}

.med-metric.accent .med-metric-value {
    color: var(--shell-panel-accent-text, #1e8f68);
}

.med-metric-value small {
    font-size: 0.78rem;
    font-weight: 600;
    opacity: 0.8;
}

.med-notes {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.55;
    color: var(--shell-panel-muted, #495057);
    white-space: pre-line;
}

.med-ref-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.65rem;
    background: var(--shell-panel-bg, #f8f9fc);
}

.med-ref-identity {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    min-width: 0;
}

.med-ref-thumb {
    width: 4.25rem;
    height: 4.25rem;
    object-fit: contain;
    border-radius: 0.5rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-surface, #fff);
    flex-shrink: 0;
}

.med-ref-name {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.med-ref-meta {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-ref-badge {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    padding: 0.28rem 0.65rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.12);
    color: var(--bs-primary, #5156be);
}

.med-ref-facts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.55rem;
}

.med-ref-fact {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: 0.5rem;
    padding: 0.65rem 0.75rem;
    background: var(--shell-panel-surface, #fff);
}

.med-ref-fact span {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-ref-fact strong {
    font-size: 0.88rem;
    color: var(--shell-panel-text, #343747);
}

.med-ref-empty {
    min-height: 14rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-align: center;
    color: var(--shell-panel-muted, #74788d);
    border: 1px dashed var(--shell-panel-border, #e6e8ee);
    border-radius: 0.65rem;
    padding: 1.5rem;
    background: var(--shell-panel-bg, #f8f9fc);
}

.med-ref-empty i {
    font-size: 1.6rem;
    opacity: 0.55;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: 999px;
    padding: 0.28rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-dot {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 999px;
    background: var(--shell-panel-muted, #adb5bd);
}

.status-chip.is-on .status-dot {
    background: #34c38f;
}

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts,
    .med-metrics {
        grid-template-columns: 1fr;
    }

    .med-hero,
    .med-ref-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
