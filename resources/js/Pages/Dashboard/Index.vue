<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
        </template>

        <div class="row">
            <div v-for="card in summary" :key="card.label" class="col-xl-3 col-md-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted mb-3 lh-1 d-block text-truncate">{{ card.label }}</span>
                                <h4 class="mb-3">{{ card.value }}</h4>
                            </div>
                            <div class="col-6">
                                <apexchart v-if="card.series" type="area" height="46" :options="card.options" :series="card.series" />
                                <div v-else class="text-end">
                                    <span class="text-muted d-block font-size-12">{{ card.aside }}</span>
                                    <h5 class="mb-0">{{ card.asideValue }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="text-nowrap">
                            <span class="badge bg-soft-primary text-primary">{{ card.note }}</span>
                            <span class="ms-1 text-muted font-size-13">{{ card.caption }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5">
                <div class="card card-h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Profit by category</h5>
                        <div v-if="categoryRows.length" class="row align-items-center">
                            <div class="col-sm">
                                <apexchart type="donut" height="230" :options="donutOptions" :series="categorySeries" />
                            </div>
                            <div class="col-sm align-self-center">
                                <div v-for="(row, index) in categoryRows" :key="row.category" class="mt-3">
                                    <p class="mb-1">
                                        <i class="mdi mdi-circle align-middle font-size-10 me-2" :style="{ color: palette[index % palette.length] }"></i>
                                        {{ row.category }}
                                    </p>
                                    <h6 class="mb-0">{{ money(row.profit) }}</h6>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted mb-0 dash-fill d-flex align-items-center">No category profit yet. It appears after sales are posted.</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Today</h5>
                                <div class="row align-items-center">
                                <div class="col-sm-5">
                                    <div class="dash-meter">
                                        <apexchart type="radialBar" height="190" :options="radialOptions" :series="marginSeries" />
                                    </div>
                                </div>
                                    <div class="col-sm-7">
                                        <p class="mb-1">Today's sales</p>
                                        <h4>{{ money(stats.todays_sales) }}</h4>
                                        <p class="text-muted mb-4">Average ticket {{ money(stats.basket) }}</p>
                                        <div class="row g-0">
                                            <div class="col-6">
                                                <p class="mb-2 text-muted text-uppercase font-size-11">Purchases</p>
                                                <h5 class="fw-medium">{{ money(stats.todays_purchases) }}</h5>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-2 text-muted text-uppercase font-size-11">Gross profit</p>
                                                <h5 class="fw-medium">{{ money(stats.gross_profit) }}</h5>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <Link :href="route('invoices.index')" class="btn btn-primary btn-sm">View sales <i class="mdi mdi-arrow-right ms-1"></i></Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card bg-primary text-white shadow-primary card-h-100">
                            <div class="card-body p-4">
                                <h4 class="lh-base fw-normal text-white mb-4">Stock to watch</h4>
                                <div class="row g-3 text-center">
                                    <div class="col-6" v-for="item in stockWatch" :key="item.label">
                                        <div class="watch-tile">
                                            <h4 class="text-white mb-1">{{ item.value }}</h4>
                                            <span class="text-white-50 font-size-13">{{ item.label }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <Link :href="route('stocks.expiry')" class="btn btn-light btn-sm">Expiry</Link>
                                    <Link :href="route('stocks.alerts')" class="btn btn-light btn-sm ms-1">Alerts</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center mb-4">
                            <h5 class="card-title me-2 mb-0">Sales and purchases</h5>
                            <div class="ms-auto">
                                <button v-for="option in ranges" :key="option.months" type="button" class="btn btn-sm" :class="range === option.months ? 'btn-soft-primary' : 'btn-soft-secondary'" @click="range = option.months">{{ option.label }}</button>
                            </div>
                        </div>
                        <apexchart type="bar" height="320" :options="barOptions" :series="barSeries" />
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Fast movers</h5>
                        <div v-if="fastMovers.length">
                            <div v-for="(item, index) in fastMovers" :key="item.name" class="d-flex align-items-center mb-3">
                                <div class="avatar-sm">
                                    <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-16">{{ index + 1 }}</span>
                                </div>
                                <div class="flex-grow-1 ms-3 text-truncate">{{ item.name }}</div>
                                <div class="flex-shrink-0 text-muted">{{ item.sold }}</div>
                            </div>
                        </div>
                        <p v-else class="text-muted mb-0">No sales in the last 30 days.</p>
                        <h5 class="card-title mt-4 mb-3">Dead stock</h5>
                        <div v-if="deadStock.length">
                            <div v-for="item in deadStock" :key="item.id || item.name" class="d-flex align-items-center mb-2">
                                <i class="bi bi-box-seam text-muted me-2"></i>
                                <span class="text-truncate">{{ item.name }}</span>
                            </div>
                            <p class="text-muted font-size-12 mb-0">No sales in {{ deadStockDays }} days.</p>
                        </div>
                        <p v-else class="text-muted mb-0">Nothing sitting unsold.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts
const page = usePage()

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    fastMovers: { type: Array, default: () => [] },
    deadStock: { type: Array, default: () => [] },
    slowMovers: { type: Array, default: () => [] },
    deadStockDays: { type: Number, default: 90 },
    profitByCategory: { type: Array, default: () => [] },
    monthlyData: { type: Array, default: () => [] },
})

const palette = ['#5156be', '#34c38f', '#50a5f1', '#f1b44c', '#f46a6a', '#74788d']
const ranges = [
    { label: '1M', months: 1 },
    { label: '6M', months: 6 },
    { label: '1Y', months: 12 },
]
const range = ref(12)

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    return ui.currency_position === 'after' ? `${amount}${ui.currency_symbol || ''}` : `${ui.currency_symbol || ''}${amount}`
}

const months = computed(() => props.monthlyData || [])
const salesTrend = computed(() => months.value.map((row) => Number(row.sales || 0)))
const purchaseTrend = computed(() => months.value.map((row) => Number(row.purchases || 0)))
const marginTrend = computed(() => months.value.map((row) => {
    const sales = Number(row.sales || 0)
    return sales > 0 ? Number((((sales - Number(row.purchases || 0)) / sales) * 100).toFixed(1)) : 0
}))

const sparkOptions = (color) => ({
    chart: { sparkline: { enabled: true }, animations: { enabled: false }, fontFamily: 'IBM Plex Sans, sans-serif' },
    stroke: { width: 2, curve: 'smooth' },
    fill: { opacity: 0.2 },
    colors: [color],
    tooltip: { enabled: false },
})

const summary = computed(() => [
    { label: "Today's sales", value: money(props.stats.todays_sales), note: `${props.stats.prescriptions_today || 0} Rx`, caption: 'Dispensed ' + (props.stats.dispensed_today || 0), series: [{ data: salesTrend.value }], options: sparkOptions('#5156be') },
    { label: "Today's purchases", value: money(props.stats.todays_purchases), note: money(props.stats.payables), caption: 'Payables', series: [{ data: purchaseTrend.value }], options: sparkOptions('#34c38f') },
    { label: 'Cash in hand', value: money(props.stats.cash_in_hand), note: money(props.stats.receivables), caption: 'Receivables', aside: 'Inventory', asideValue: money(props.stats.inventory_value) },
    { label: 'Gross margin', value: `${Number(props.stats.gross_margin || 0).toFixed(1)}%`, note: money(props.stats.gross_profit), caption: 'Gross profit', series: [{ data: marginTrend.value }], options: sparkOptions('#f1b44c') },
])

const stockWatch = computed(() => [
    { label: 'Expiring', value: props.stats.expiring || 0 },
    { label: 'Expired', value: props.stats.expired || 0 },
    { label: 'Low stock', value: props.stats.low_stock || 0 },
    { label: 'Out of stock', value: props.stats.out_of_stock || 0 },
])

const categoryRows = computed(() => (props.profitByCategory || []).filter((row) => Number(row.profit) > 0).slice(0, 6))
const categorySeries = computed(() => categoryRows.value.map((row) => Number(row.profit)))
const donutOptions = computed(() => ({
    labels: categoryRows.value.map((row) => row.category),
    colors: palette,
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { width: 0 },
    chart: { fontFamily: 'IBM Plex Sans, sans-serif' },
    plotOptions: { pie: { donut: { size: '72%' } } },
    tooltip: { y: { formatter: (value) => money(value) } },
}))

const marginSeries = computed(() => [Math.max(0, Math.min(100, Number(props.stats.gross_margin || 0)))])
const radialOptions = {
    chart: { fontFamily: 'IBM Plex Sans, sans-serif' },
    colors: ['#5156be'],
    plotOptions: {
        radialBar: {
            hollow: { size: '62%' },
            dataLabels: {
                name: { show: true, fontSize: '12px', color: '#74788d', offsetY: 18 },
                value: { fontSize: '20px', fontFamily: 'IBM Plex Sans, sans-serif', offsetY: -12, formatter: (value) => `${Number(value).toFixed(1)}%` },
            },
        },
    },
    labels: ['Margin'],
}

const ranged = computed(() => months.value.slice(-range.value))
const barSeries = computed(() => [
    { name: 'Sales', data: ranged.value.map((row) => Number(row.sales || 0)) },
    { name: 'Purchases', data: ranged.value.map((row) => Number(row.purchases || 0)) },
])
const barOptions = computed(() => {
    const peak = Math.max(0, ...ranged.value.flatMap((row) => [Number(row.sales || 0), Number(row.purchases || 0)]))
    return {
        chart: { toolbar: { show: false }, fontFamily: 'IBM Plex Sans, sans-serif' },
        colors: ['#5156be', '#34c38f'],
        plotOptions: { bar: { columnWidth: '42%', borderRadius: 3 } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f1f5', strokeDashArray: 4 },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { categories: ranged.value.map((row) => row.month), axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: {
            min: 0,
            max: peak > 0 ? undefined : 1,
            labels: { formatter: (value) => Number(value).toLocaleString() },
        },
        legend: { position: 'top', fontFamily: 'IBM Plex Sans, sans-serif' },
        tooltip: { y: { formatter: (value) => money(value) } },
    }
})
</script>
