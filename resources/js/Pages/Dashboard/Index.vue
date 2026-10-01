<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-emerald-800/60">Overview</p>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Executive dashboard</h2>
                    <p class="mt-1 text-sm text-slate-500">A live view of today’s counter, inventory, and cash position.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                    {{ new Date().toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' }) }}
                </span>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-[88rem] space-y-5 px-4 sm:px-7 lg:px-9">
                <form class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-white px-4 py-3 shadow-sm" @submit.prevent="saveDeadDays">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800">Stock movement window</label>
                        <span class="text-xs text-slate-500">Set the period used to identify slow and dead stock.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model.number="deadDays" type="number" min="1" class="w-24 border rounded px-2 py-1" aria-label="Stock movement window in days">
                        <span class="text-sm text-slate-500">days</span>
                        <button class="bg-gray-800 text-white px-3 py-1 rounded">Save</button>
                    </div>
                </form>
                <section aria-label="Key performance indicators">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-800">Key indicators</h3>
                        <span class="text-xs text-slate-400">Today and current inventory</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6">
                        <div v-for="(card, index) in cards" :key="card.label" class="dashboard-metric group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-150 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                            <span class="absolute inset-y-0 left-0 w-[3px]" :class="index >= 10 ? 'bg-amber-400' : 'bg-emerald-700/70'" />
                            <div class="text-[11px] font-medium leading-4 text-slate-500">{{ card.label }}</div>
                            <div class="mt-2 truncate text-xl font-semibold tabular-nums text-slate-900" :title="String(card.value)">{{ card.value }}</div>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                    <section class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
                        <header class="border-b border-slate-100 px-5 py-4">
                            <h3 class="text-sm font-semibold text-slate-800">Sales and purchases</h3>
                            <p class="mt-1 text-xs text-slate-500">Monthly totals for the last 12 months</p>
                        </header>
                        <div class="overflow-x-auto px-5 pb-4">
                        <table class="min-w-full text-sm">
                            <thead><tr class="text-left text-gray-500"><th class="py-3">Month</th><th class="py-3 text-right">Sales</th><th class="py-3 text-right">Purchases</th></tr></thead>
                            <tbody>
                                <tr v-for="row in monthlyData" :key="row.month" class="border-t border-slate-100">
                                    <td class="py-2.5 text-slate-600">{{ row.month }}</td>
                                    <td class="py-2.5 text-right font-medium tabular-nums text-slate-800">{{ row.sales }}</td>
                                    <td class="py-2.5 text-right font-medium tabular-nums text-slate-800">{{ row.purchases }}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </section>
                    <section class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">Product movement</h3>
                                <p class="mt-1 text-xs text-slate-500">Sales velocity and inventory exposure</p>
                            </div>
                            <i class="bi bi-arrow-left-right text-base text-emerald-800/60" aria-hidden="true" />
                        </div>
                        <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Top medicines · 30 days</h4>
                        <ul class="space-y-1 text-sm">
                            <li v-for="row in fastMovers" :key="row.name" class="flex justify-between border-b border-slate-100 py-2">
                                <span class="truncate pr-3 text-slate-700">{{ row.name }}</span><span class="tabular-nums font-medium text-slate-800">{{ row.sold }}</span>
                            </li>
                            <li v-if="fastMovers.length === 0" class="py-2 text-slate-500">No sales in the last 30 days.</li>
                        </ul>
                        <h4 class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500">Slow movers · {{ deadStockDays }} days</h4>
                        <ul class="space-y-1 text-sm">
                            <li v-for="row in slowMovers" :key="row.name" class="flex justify-between border-b border-slate-100 py-2">
                                <span class="truncate pr-3 text-slate-700">{{ row.name }}</span><span class="tabular-nums font-medium text-slate-800">{{ row.sold }}</span>
                            </li>
                            <li v-if="!slowMovers?.length" class="py-2 text-slate-500">None.</li>
                        </ul>
                        <h4 class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-slate-500">Dead stock · {{ deadStockDays }} days</h4>
                        <ul class="space-y-1 text-sm">
                            <li v-for="row in deadStock" :key="row.id" class="border-b border-slate-100 py-2 text-slate-700">{{ row.name }}</li>
                            <li v-if="deadStock.length === 0" class="py-2 text-slate-500">None.</li>
                        </ul>
                    </section>
                </div>

                <section class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <header class="mb-3 flex items-end justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Profit by category</h3>
                            <p class="mt-1 text-xs text-slate-500">Gross contribution across product categories</p>
                        </div>
                        <i class="bi bi-bar-chart-line text-base text-emerald-800/60" aria-hidden="true" />
                    </header>
                    <ul class="grid gap-x-8 text-sm sm:grid-cols-2 xl:grid-cols-3">
                        <li v-for="row in profitByCategory" :key="row.category" class="flex justify-between gap-3 border-b border-slate-100 py-2.5">
                            <span class="truncate text-slate-600">{{ row.category }}</span><span class="shrink-0 font-medium tabular-nums text-slate-800">{{ row.profit }}</span>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: Object,
    fastMovers: Array,
    deadStock: Array,
    slowMovers: { type: Array, default: () => [] },
    deadStockDays: { type: Number, default: 90 },
    profitByCategory: Array,
    monthlyData: Array,
})

const deadDays = ref(props.deadStockDays)
const saveDeadDays = () => router.post(route('dashboard.dead-stock-days'), { dead_stock_days: deadDays.value })
const cards = computed(() => [
    { label: "Today's sales", value: props.stats.todays_sales },
    { label: "Today's purchases", value: props.stats.todays_purchases },
    { label: 'Gross profit', value: props.stats.gross_profit },
    { label: 'Gross margin %', value: props.stats.gross_margin },
    { label: 'Profit after cost', value: props.stats.net_profit },
    { label: 'Cash in hand', value: props.stats.cash_in_hand },
    { label: 'Customer receivables', value: props.stats.receivables },
    { label: 'Supplier payable', value: props.stats.payables },
    { label: 'Inventory at cost', value: props.stats.inventory_value },
    { label: 'Inventory at MRP', value: props.stats.mrp_value },
    { label: 'Expiring in 30 days', value: props.stats.expiring },
    { label: 'Expired batches', value: props.stats.expired },
    { label: 'Low stock batches', value: props.stats.low_stock },
    { label: 'Out of stock', value: props.stats.out_of_stock },
    { label: 'Average basket', value: props.stats.basket },
    { label: "Today's prescriptions", value: props.stats.prescriptions_today },
    { label: 'Dispensed today', value: props.stats.dispensed_today },
])
</script>
