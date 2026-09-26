<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Executive dashboard</h2>
        </template>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <form class="bg-white rounded shadow px-4 py-3 flex items-center gap-3 text-sm" @submit.prevent="saveDeadDays">
                    <label>Dead and slow stock window (days)</label>
                    <input v-model.number="deadDays" type="number" min="1" class="w-24 border rounded px-2 py-1">
                    <button class="bg-gray-800 text-white px-3 py-1 rounded">Save</button>
                </form>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="card in cards" :key="card.label" class="bg-white shadow-sm rounded-lg p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ card.label }}</div>
                        <div class="mt-1 text-xl font-semibold text-gray-900">{{ card.value }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="font-medium mb-3">Sales and purchases, last 12 months</h3>
                        <table class="min-w-full text-sm">
                            <thead><tr class="text-left text-gray-500"><th>Month</th><th>Sales</th><th>Purchases</th></tr></thead>
                            <tbody>
                                <tr v-for="row in monthlyData" :key="row.month" class="border-t">
                                    <td class="py-1">{{ row.month }}</td>
                                    <td>{{ row.sales }}</td>
                                    <td>{{ row.purchases }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="font-medium mb-3">Top medicines, 30 days</h3>
                        <ul class="text-sm space-y-1">
                            <li v-for="row in fastMovers" :key="row.name" class="flex justify-between border-b py-1">
                                <span>{{ row.name }}</span><span>{{ row.sold }}</span>
                            </li>
                            <li v-if="fastMovers.length === 0" class="text-gray-500">No sales in the last 30 days.</li>
                        </ul>
                        <h3 class="font-medium mt-6 mb-3">Slow movers, last {{ deadStockDays }} days</h3>
                        <ul class="text-sm space-y-1">
                            <li v-for="row in slowMovers" :key="row.name" class="flex justify-between border-b py-1">
                                <span>{{ row.name }}</span><span>{{ row.sold }}</span>
                            </li>
                            <li v-if="!slowMovers?.length" class="text-gray-500">None.</li>
                        </ul>
                        <h3 class="font-medium mt-6 mb-3">Dead stock, no sale in {{ deadStockDays }} days</h3>
                        <ul class="text-sm space-y-1">
                            <li v-for="row in deadStock" :key="row.id">{{ row.name }}</li>
                            <li v-if="deadStock.length === 0" class="text-gray-500">None.</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-medium mb-3">Profit by category</h3>
                    <ul class="text-sm">
                        <li v-for="row in profitByCategory" :key="row.category" class="flex justify-between border-b py-1">
                            <span>{{ row.category }}</span><span>{{ row.profit }}</span>
                        </li>
                    </ul>
                </div>
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
