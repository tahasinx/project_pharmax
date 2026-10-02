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
                                <div v-if="card.series" class="dash-spark">
                                    <apexchart type="area" height="46" :options="card.options" :series="card.series" />
                                </div>
                                <div v-else class="text-end">
                                    <span class="text-muted d-block font-size-12">{{ card.aside }}</span>
                                    <h5 class="mb-0">{{ card.asideValue }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="text-nowrap">
                            <span class="badge" :class="card.tone">{{ card.note }}</span>
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
                                <div class="dash-donut">
                                    <apexchart type="donut" height="230" :options="donutOptions" :series="categorySeries" />
                                </div>
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
                                        <apexchart type="radialBar" height="180" :options="radialOptions" :series="marginSeries" />
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
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <h5 class="card-title me-2 mb-0">Counter overview</h5>
                            <div class="btn-group btn-group-sm">
                                <button v-for="option in views" :key="option.key" type="button" class="btn" :class="view === option.key ? 'btn-primary' : 'btn-soft-secondary'" @click="view = option.key">{{ option.label }}</button>
                            </div>
                            <div v-if="view === 'trade'" class="ms-auto">
                                <button v-for="option in ranges" :key="option.months" type="button" class="btn btn-sm" :class="range === option.months ? 'btn-soft-primary' : 'btn-soft-secondary'" @click="range = option.months">{{ option.label }}</button>
                            </div>
                        </div>
                        <div v-if="view === 'trade'" class="row g-3 mb-3">
                            <div class="col-sm-4" v-for="item in periodTotals" :key="item.label">
                                <span class="text-muted font-size-12 d-block">{{ item.label }}</span>
                                <h5 class="mb-0">{{ item.value }}</h5>
                            </div>
                        </div>
                        <div class="dash-plot">
                            <apexchart v-if="plotReady" :key="plotKey" :type="plotType" height="300" :options="plotOptions" :series="plotSeries" />
                            <div v-else class="h-100 d-flex align-items-center justify-content-center text-center text-muted px-4">
                                {{ plotEmpty }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center mb-3">
                            <h5 class="card-title me-2 mb-0">Position</h5>
                        </div>
                        <div class="dash-plot dash-plot-side">
                            <apexchart v-if="positionReady" type="bar" height="220" :options="positionOptions" :series="positionSeries" />
                            <p v-else class="text-muted mb-3">Cash, dues, and inventory will appear here once a transaction is posted.</p>
                        </div>
                        <div v-if="paymentRows.length" class="mt-3">
                            <h6 class="text-muted text-uppercase font-size-11 mb-3">Payment mix</h6>
                            <div v-for="row in paymentRows" :key="row.method" class="mb-3">
                                <p class="mb-1 text-capitalize">{{ row.method }} <span class="float-end">{{ row.share }}%</span></p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" :style="{ width: row.share + '%' }" :aria-valuenow="row.share" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="fastMovers.length" class="mt-4">
                            <h5 class="card-title mb-3">Fast movers</h5>
                            <div v-for="(item, index) in fastMovers.slice(0, 5)" :key="item.name" class="d-flex align-items-center mb-2">
                                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-12 me-2" style="width: 24px; height: 24px;">{{ index + 1 }}</span>
                                <span class="text-truncate flex-grow-1">{{ item.name }}</span>
                                <span class="text-muted font-size-12">{{ item.sold }}</span>
                            </div>
                        </div>
                        <h5 class="card-title mt-4 mb-3">Dead stock</h5>
                        <div v-if="deadStock.length">
                            <div v-for="item in deadStock.slice(0, 4)" :key="item.id || item.name" class="d-flex align-items-center mb-2">
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Recent activity</h4>
                        <div class="flex-shrink-0">
                            <ul class="nav justify-content-end nav-tabs-custom rounded card-header-tabs">
                                <li class="nav-item" v-for="tab in activityTabs" :key="tab.key">
                                    <button type="button" class="nav-link" :class="{ active: activityTab === tab.key }" @click="activityTab = tab.key">{{ tab.label }}</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive px-3">
                            <table class="table align-middle table-nowrap table-borderless mb-0">
                                <tbody v-if="visibleActivity.length">
                                    <tr v-for="row in visibleActivity" :key="row.id">
                                        <td style="width: 50px;">
                                            <div class="font-size-22" :class="row.kind === 'sale' ? 'text-success' : 'text-danger'">
                                                <i class="bi d-block" :class="row.kind === 'sale' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle'"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <h5 class="font-size-14 mb-1">{{ row.kind === 'sale' ? 'Sale' : 'Purchase' }} {{ row.title }}</h5>
                                            <p class="text-muted mb-0 font-size-12">{{ row.party }}</p>
                                        </td>
                                        <td>
                                            <p class="text-muted mb-0">{{ row.date }}</p>
                                        </td>
                                        <td class="text-end">
                                            <h5 class="font-size-14 mb-0" :class="row.kind === 'sale' ? 'text-success' : 'text-danger'">{{ money(row.amount) }}</h5>
                                            <p class="text-muted mb-0 font-size-12">{{ Number(row.due) > 0 ? 'Due ' + money(row.due) : 'Settled' }}</p>
                                        </td>
                                        <td class="text-end" style="width: 90px;">
                                            <Link :href="row.href" class="btn btn-sm btn-soft-primary">Open</Link>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td class="text-muted px-3 py-4">No {{ activityTab === 'all' ? 'sales or purchases' : activityTab }} posted yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
import { useTheme } from '@/Composables/useTheme'

const apexchart = VueApexCharts
const { palette: themePalette } = useTheme()
const page = usePage()

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    fastMovers: { type: Array, default: () => [] },
    deadStock: { type: Array, default: () => [] },
    slowMovers: { type: Array, default: () => [] },
    deadStockDays: { type: Number, default: 90 },
    profitByCategory: { type: Array, default: () => [] },
    monthlyData: { type: Array, default: () => [] },
    dailyData: { type: Array, default: () => [] },
    activity: { type: Array, default: () => [] },
    payments: { type: Array, default: () => [] },
})

const palette = ['#5156be', '#34c38f', '#50a5f1', '#f1b44c', '#f46a6a', '#74788d']
const ranges = [
    { label: '1M', months: 1 },
    { label: '6M', months: 6 },
    { label: '1Y', months: 12 },
]
const range = ref(12)
const view = ref('trade')
const views = [
    { key: 'trade', label: 'Trade' },
    { key: 'stock', label: 'Stock' },
]
const activityTab = ref('all')
const activityTabs = [
    { key: 'all', label: 'All' },
    { key: 'sale', label: 'Sales' },
    { key: 'purchase', label: 'Purchases' },
]

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
    chart: {
        sparkline: { enabled: true },
        animations: { enabled: false },
        parentHeightOffset: 0,
        toolbar: { show: false },
        fontFamily: 'IBM Plex Sans, sans-serif',
    },
    stroke: { width: 2, curve: 'smooth' },
    fill: { opacity: 0.25 },
    colors: [color],
    tooltip: { enabled: true, x: { show: false }, y: { formatter: (value) => money(value) } },
})

const changeNote = (value) => `${Number(value) > 0 ? '+' : ''}${Number(value || 0).toFixed(1)}%`
const changeTone = (value) => Number(value) >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger'

const summary = computed(() => [
    { label: "Today's sales", value: money(props.stats.todays_sales), note: changeNote(props.stats.sales_change), tone: changeTone(props.stats.sales_change), caption: 'Vs last week', series: [{ data: salesTrend.value }], options: sparkOptions('#5156be') },
    { label: "Today's purchases", value: money(props.stats.todays_purchases), note: changeNote(props.stats.purchases_change), tone: changeTone(props.stats.purchases_change), caption: 'Vs last week', series: [{ data: purchaseTrend.value }], options: sparkOptions('#34c38f') },
    { label: 'Cash in hand', value: money(props.stats.cash_in_hand), note: money(props.stats.receivables), tone: 'bg-soft-primary text-primary', caption: 'Receivables', aside: 'Inventory', asideValue: money(props.stats.inventory_value) },
    { label: 'Gross margin', value: `${Number(props.stats.gross_margin || 0).toFixed(1)}%`, note: changeNote(props.stats.margin_change), tone: changeTone(props.stats.margin_change), caption: 'Vs last week', series: [{ data: marginTrend.value }], options: sparkOptions('#f1b44c') },
])

const visibleActivity = computed(() => {
    const rows = props.activity || []
    return activityTab.value === 'all' ? rows : rows.filter((row) => row.kind === activityTab.value)
})

const paymentRows = computed(() => {
    const rows = props.payments || []
    const total = rows.reduce((sum, row) => sum + Number(row.total || 0), 0)
    return rows.map((row) => ({
        method: String(row.method || 'Unspecified').replaceAll('_', ' '),
        share: total > 0 ? Math.round((Number(row.total) / total) * 100) : 0,
    }))
})

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
    chart: { fontFamily: 'IBM Plex Sans, sans-serif', foreColor: themePalette.value.muted, background: 'transparent', parentHeightOffset: 0, toolbar: { show: false } },
    plotOptions: { pie: { donut: { size: '72%' } } },
    tooltip: { y: { formatter: (value) => money(value) }, theme: themePalette.value.canvas === '#f8f8fb' ? 'light' : 'dark' },
}))

const marginSeries = computed(() => [Math.max(0, Math.min(100, Number(props.stats.gross_margin || 0)))])
const radialOptions = computed(() => ({
    chart: { fontFamily: 'IBM Plex Sans, sans-serif', foreColor: themePalette.value.muted, background: 'transparent', parentHeightOffset: 0, toolbar: { show: false } },
    colors: ['#5156be'],
    plotOptions: {
        radialBar: {
            hollow: { size: '62%' },
            track: { background: themePalette.value.grid },
            dataLabels: {
                name: { show: true, fontSize: '12px', color: themePalette.value.muted, offsetY: 18 },
                value: { fontSize: '20px', fontFamily: 'IBM Plex Sans, sans-serif', color: themePalette.value.text, offsetY: -12, formatter: (value) => `${Number(value).toFixed(1)}%` },
            },
        },
    },
    labels: ['Margin'],
}))

const ranged = computed(() => {
    if (range.value === 1) {
        return (props.dailyData || []).map((row) => ({ month: row.label, sales: row.sales, purchases: row.purchases }))
    }
    return months.value.slice(-range.value)
})

const sumOf = (key) => ranged.value.reduce((sum, row) => sum + Number(row[key] || 0), 0)
const periodTotals = computed(() => {
    const sales = sumOf('sales')
    const purchases = sumOf('purchases')
    return [
        { label: 'Sales', value: money(sales) },
        { label: 'Purchases', value: money(purchases) },
        { label: 'Sales − purchases', value: money(sales - purchases) },
    ]
})

const tradeHasMovement = computed(() => ranged.value.some((row) => Number(row.sales) > 0 || Number(row.purchases) > 0))
const stockCounts = computed(() => stockWatch.value.map((item) => Number(item.value || 0)))
const plotReady = computed(() => view.value === 'stock' ? stockCounts.value.some((value) => value > 0) : tradeHasMovement.value)
const plotEmpty = computed(() => view.value === 'stock' ? 'No batches are expiring, short, or out of stock.' : 'No sales or purchases in this period.')
const plotType = computed(() => view.value === 'stock' ? 'bar' : 'area')
const plotKey = computed(() => `${view.value}-${range.value}-${themePalette.value.canvas}`)
const plotSeries = computed(() => {
    if (view.value === 'stock') {
        return [{ name: 'Items', data: stockCounts.value }]
    }
    return [
        { name: 'Sales', data: ranged.value.map((row) => Number(row.sales || 0)) },
        { name: 'Purchases', data: ranged.value.map((row) => Number(row.purchases || 0)) },
    ]
})

const axisBase = () => ({
    chart: {
        toolbar: { show: false },
        parentHeightOffset: 0,
        fontFamily: 'IBM Plex Sans, sans-serif',
        foreColor: themePalette.value.muted,
        background: 'transparent',
    },
    dataLabels: { enabled: false },
    grid: { borderColor: themePalette.value.grid, strokeDashArray: 4 },
    legend: { position: 'top', fontFamily: 'IBM Plex Sans, sans-serif', labels: { colors: themePalette.value.text } },
})

const plotOptions = computed(() => {
    const base = axisBase()
    if (view.value === 'stock') {
        return {
            ...base,
            colors: ['#f1b44c', '#f46a6a', '#50a5f1', '#74788d'],
            plotOptions: { bar: { horizontal: true, distributed: true, borderRadius: 4, barHeight: '52%' } },
            xaxis: {
                categories: stockWatch.value.map((item) => item.label),
                labels: { style: { colors: themePalette.value.muted } },
            },
            yaxis: { labels: { style: { colors: themePalette.value.text } } },
            tooltip: { y: { formatter: (value) => `${Number(value)} items` } },
        }
    }
    return {
        ...base,
        colors: ['#5156be', '#34c38f'],
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
        xaxis: {
            categories: ranged.value.map((row) => row.month),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: themePalette.value.muted }, rotate: range.value === 1 ? -45 : 0 },
        },
        yaxis: {
            min: 0,
            labels: { style: { colors: themePalette.value.muted }, formatter: (value) => Number(value).toLocaleString() },
        },
        tooltip: { y: { formatter: (value) => money(value) } },
    }
})

const positionRows = computed(() => [
    { label: 'Cash', value: Number(props.stats.cash_in_hand || 0) },
    { label: 'Receivables', value: Number(props.stats.receivables || 0) },
    { label: 'Inventory', value: Number(props.stats.inventory_value || 0) },
    { label: 'Payables', value: Number(props.stats.payables || 0) },
])
const positionReady = computed(() => positionRows.value.some((row) => row.value !== 0))
const positionSeries = computed(() => [{ name: 'Balance', data: positionRows.value.map((row) => row.value) }])
const positionOptions = computed(() => ({
    ...axisBase(),
    colors: ['#5156be'],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '48%' } },
    xaxis: {
        categories: positionRows.value.map((row) => row.label),
        labels: { style: { colors: themePalette.value.muted }, formatter: (value) => Number(value).toLocaleString() },
    },
    yaxis: { labels: { style: { colors: themePalette.value.text } } },
    tooltip: { y: { formatter: (value) => money(value) } },
}))
</script>
