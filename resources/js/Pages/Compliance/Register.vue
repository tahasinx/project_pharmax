<template>
    <Head title="Controlled register" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex flex-column gap-2 min-w-0">
                <h4 class="mb-0 font-size-18">Controlled medicine register</h4>
                <FilterSummary
                    :chips="filterChips"
                    @open="showFilters = true"
                    @clear="reset"
                    @remove="removeFilter"
                />
            </div>
        </template>

        <div class="comp-stats mb-3">
            <div class="comp-stat">
                <span class="comp-stat-label">Total entries</span>
                <strong>{{ stats?.total ?? 0 }}</strong>
            </div>
            <div class="comp-stat">
                <span class="comp-stat-label">Today</span>
                <strong>{{ stats?.today ?? 0 }}</strong>
            </div>
            <div class="comp-stat accent">
                <span class="comp-stat-label">Qty today</span>
                <strong>{{ stats?.qty_today ?? 0 }}</strong>
            </div>
        </div>

        <FilterDrawer
            :show="showFilters"
            title="Register filters"
            @close="showFilters = false"
            @apply="apply"
            @clear="reset"
        >
            <div class="filter-field">
                <label class="field-label">Search</label>
                <input v-model="form.q" type="search" class="field" placeholder="Medicine, generic, or customer…">
            </div>
            <div class="filter-field">
                <label class="field-label">From</label>
                <input v-model="form.from" type="date" class="field">
            </div>
            <div class="filter-field">
                <label class="field-label">To</label>
                <input v-model="form.to" type="date" class="field">
            </div>
            <div class="filter-field">
                <label class="field-label">Source</label>
                <select v-model="form.source" class="field">
                    <option value="">All sources</option>
                    <option value="prescription">Prescription</option>
                    <option value="pos">POS</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
        </FilterDrawer>

        <LunaTable title="Register" empty-text="No controlled dispenses recorded.">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Dispensed</th>
                        <th>Medicine</th>
                        <th>Qty</th>
                        <th>Customer</th>
                        <th>Batch</th>
                        <th>Source</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.id">
                        <td>
                            <div class="fw-semibold">{{ formatDate(row.dispensed_at) }}</div>
                            <div class="text-muted font-size-12">{{ formatTime(row.dispensed_at) }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ row.medicine?.name || '—' }}</div>
                            <div class="text-muted font-size-12">{{ row.medicine?.generic_name || '—' }}</div>
                            <div class="flag-row">
                                <span v-if="row.medicine?.is_narcotic" class="flag-chip is-narco">Narcotic</span>
                                <span v-else-if="row.medicine?.is_controlled" class="flag-chip is-ctrl">Controlled</span>
                            </div>
                        </td>
                        <td class="fw-semibold">{{ row.quantity }}</td>
                        <td>
                            <div>{{ row.customer?.name || '—' }}</div>
                            <div class="text-muted font-size-12">{{ row.customer?.mobile || '' }}</div>
                        </td>
                        <td>
                            <div>{{ row.stock?.batch_number || '—' }}</div>
                            <div class="text-muted font-size-12">{{ formatDate(row.stock?.expiry_date) }}</div>
                        </td>
                        <td>
                            <span class="source-chip" :class="'src-' + (row.source || 'prescription')">
                                {{ sourceLabel(row.source) }}
                            </span>
                        </td>
                        <td>{{ row.user?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import FilterDrawer from '@/Components/FilterDrawer.vue'
import FilterSummary from '@/Components/FilterSummary.vue'

const props = defineProps({
    rows: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
})

const showFilters = ref(false)
const form = reactive({
    q: props.filters?.q || '',
    from: props.filters?.from || '',
    to: props.filters?.to || '',
    source: props.filters?.source || '',
})

const sourceLabels = { prescription: 'Prescription', pos: 'POS', manual: 'Manual' }
const filterChips = computed(() => {
    const chips = []
    if (form.q) chips.push({ key: 'q', label: 'Search', value: form.q })
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    if (form.source) chips.push({ key: 'source', label: 'Source', value: sourceLabels[form.source] || form.source })
    return chips
})

const apply = () => {
    showFilters.value = false
    router.get(route('controlled.index'), { ...form }, { preserveState: true, replace: true })
}
const reset = () => {
    form.q = ''
    form.from = ''
    form.to = ''
    form.source = ''
    apply()
}
const removeFilter = (key) => {
    if (key in form) form[key] = ''
    apply()
}

const sourceLabel = (source) => {
    if (source === 'pos') return 'POS'
    if (source === 'manual') return 'Manual'
    return 'Rx'
}

const formatDate = (value) => {
    if (!value) return '—'
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value).slice(0, 10)
    return d.toLocaleDateString()
}

const formatTime = (value) => {
    if (!value) return ''
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return ''
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.comp-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.65rem;
}

.comp-stat {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 0.7rem 0.9rem;
}

.comp-stat.accent {
    border-color: var(--shell-panel-accent-border, #cfd3f5);
    background: var(--shell-panel-accent-bg, #eef0fb);
}

.comp-stat-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
    margin-bottom: 0.15rem;
}

.comp-stat strong {
    font-size: 1.15rem;
    color: var(--shell-panel-text, #343747);
}

.comp-filters {
    display: grid;
    grid-template-columns: 1.4fr repeat(3, minmax(0, 1fr)) auto;
    gap: 0.55rem;
    align-items: end;
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 0.75rem 0.9rem;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.field {
    width: 100%;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0.32rem 0.55rem;
    font-size: 0.82rem;
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.comp-filter-actions {
    display: flex;
    gap: 0.35rem;
    padding-bottom: 0.05rem;
}

.flag-row {
    display: flex;
    gap: 0.3rem;
    margin-top: 0.2rem;
}

.flag-chip,
.source-chip {
    display: inline-flex;
    align-items: center;
    border-radius: var(--pf-radius, 999px);
    padding: 0.12rem 0.45rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.flag-chip.is-ctrl {
    background: #fff4e6;
    color: #c27803;
}

.flag-chip.is-narco {
    background: #fde8e8;
    color: #c0392b;
}

.source-chip {
    background: var(--shell-panel-bg, #f1f3f7);
    color: var(--shell-panel-muted, #74788d);
}

.source-chip.src-pos {
    background: #e8f4ff;
    color: #2474b8;
}

.source-chip.src-prescription {
    background: #eef0fb;
    color: #5156be;
}

@media (max-width: 991px) {
    .comp-stats,
    .comp-filters {
        grid-template-columns: 1fr 1fr;
    }

    .comp-filter-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 575px) {
    .comp-stats,
    .comp-filters {
        grid-template-columns: 1fr;
    }
}
</style>
