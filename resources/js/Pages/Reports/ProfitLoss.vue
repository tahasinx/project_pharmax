<template>
    <Head title="Profit & loss" />
    <ReportShell
        title="Profit & loss"
        subtitle="Revenue, estimated COGS and gross margin"
        :metrics="[
            { label: 'Revenue', value: money(metrics.revenue), accent: true },
            { label: 'COGS', value: money(metrics.cogs) },
            { label: 'Gross profit', value: money(metrics.gross_profit), accent: metrics.gross_profit >= 0 },
            { label: 'Margin', value: marginLabel },
        ]"
        :filter-chips="filterChips"
        @apply-filters="apply"
        @clear-filters="reset"
        @remove-filter="removeFilter"
    >
        <template #actions>
            <a :href="exportUrl" class="btn btn-outline-primary btn-sm">Export CSV</a>
        </template>
        <template #filters>
            <div class="filter-field">
                <label class="field-label">From</label>
                <input v-model="form.from" type="date" class="field">
            </div>
            <div class="filter-field">
                <label class="field-label">To</label>
                <input v-model="form.to" type="date" class="field">
            </div>
        </template>
        <section class="med-panel">
            <header class="med-panel-head"><h6>Notes</h6><p>COGS uses average purchase / manufacturer price × quantity sold</p></header>
            <p class="mb-0 text-muted font-size-13">Use stock valuation and purchases reports for inventory-side verification.</p>
        </section>
    </ReportShell>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ReportShell from '@/Components/ReportShell.vue'
import { useMoney } from '@/Composables/useMoney'

const props = defineProps({ filters: Object, metrics: Object })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const marginLabel = computed(() => {
    const rev = Number(props.metrics?.revenue || 0)
    if (!rev) return '0%'
    return `${((Number(props.metrics.gross_profit) / rev) * 100).toFixed(1)}%`
})
const apply = () => router.get(route('reports.profit-loss'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.profit-loss', { ...form, export: 1 }))
</script>

<style scoped>
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
</style>
