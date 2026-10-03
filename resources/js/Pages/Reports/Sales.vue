<template>
    <Head title="Sales report" />
    <ReportShell
        title="Sales"
        subtitle="Quantity, revenue, payment and cashier mix"
        :metrics="[
            { label: 'Total qty', value: summary.total_quantity },
            { label: 'Total sales', value: money(summary.total_sales), accent: true },
            { label: 'Avg unit', value: money(avgPrice) },
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

        <div class="row g-3">
            <div class="col-lg-6">
                <section class="med-panel">
                    <header class="med-panel-head"><h6>Daily sales</h6><p>Quantity and revenue by day</p></header>
                    <apexchart type="bar" height="280" :options="dailyOptions" :series="dailySeries" />
                </section>
            </div>
            <div class="col-lg-6">
                <section class="med-panel">
                    <header class="med-panel-head"><h6>Monthly sales</h6><p>Trend by month</p></header>
                    <apexchart type="line" height="280" :options="monthlyOptions" :series="monthlySeries" />
                </section>
            </div>
        </div>

        <LunaTable title="By payment type" empty-text="No sales in this range">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Type</th><th>Qty</th><th>Sales</th></tr></thead>
                <tbody>
                    <tr v-for="row in byPayment" :key="row.type">
                        <td class="text-uppercase">{{ row.type }}</td>
                        <td>{{ row.quantity }}</td>
                        <td>{{ money(row.sales) }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <LunaTable title="By cashier" empty-text="No cashier sales">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Cashier</th><th>Qty</th><th>Sales</th></tr></thead>
                <tbody>
                    <tr v-for="row in byCashier" :key="row.cashier">
                        <td>{{ row.cashier }}</td>
                        <td>{{ row.quantity }}</td>
                        <td>{{ money(row.sales) }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </ReportShell>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'
import ReportShell from '@/Components/ReportShell.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

const apexchart = VueApexCharts
const props = defineProps({
    filters: Object,
    summary: Object,
    daily: Object,
    monthly: Object,
    byPayment: Array,
    byCashier: Array,
})
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const avgPrice = computed(() => {
    const qty = Number(props.summary?.total_quantity || 0)
    return qty ? Number(props.summary.total_sales) / qty : 0
})
const apply = () => router.get(route('reports.sales'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.sales', { ...form, export: 1 }))

const dailyKeys = computed(() => Object.keys(props.daily || {}))
const dailySeries = computed(() => [{ name: 'Sales', data: dailyKeys.value.map((k) => props.daily[k].sales) }])
const dailyOptions = computed(() => ({ chart: { toolbar: { show: false } }, xaxis: { categories: dailyKeys.value }, dataLabels: { enabled: false } }))
const monthlyKeys = computed(() => Object.keys(props.monthly || {}))
const monthlySeries = computed(() => [{ name: 'Sales', data: monthlyKeys.value.map((k) => props.monthly[k].sales) }])
const monthlyOptions = computed(() => ({ chart: { toolbar: { show: false } }, xaxis: { categories: monthlyKeys.value }, dataLabels: { enabled: false } }))
</script>

<style scoped>
.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; color: var(--shell-panel-muted, #495057); }
.field {
    width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff); padding: 0.32rem 0.55rem; font-size: 0.82rem;
}
</style>
