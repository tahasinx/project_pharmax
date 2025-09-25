<template>
  <AuthenticatedLayout>
    <Head title="Profit / Loss" />
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profit / Loss</h2>
        <Link :href="route('reports.index')"
              class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
          </svg>
          <span>Back to Reports</span>
        </Link>
      </div>
    </template>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <form class="grid grid-cols-1 md:grid-cols-3 gap-3" @submit.prevent="apply">
      <input type="date" v-model="form.from" class="input" />
      <input type="date" v-model="form.to" class="input" />
      <div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
      </div>
    </form>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card"><div class="text-sm text-gray-500">Revenue</div><div class="text-2xl font-semibold">{{ currency(metrics.revenue) }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">COGS</div><div class="text-2xl font-semibold">{{ currency(metrics.cogs) }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Gross Profit</div><div class="text-2xl font-semibold">{{ currency(metrics.gross_profit) }}</div></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
          <apexchart type="donut" height="320" :options="donutOptions" :series="donutSeries" />
        </div>
        <div class="card">
          <div class="text-sm text-gray-500 mb-2">Gross Margin</div>
          <apexchart type="radialBar" height="320" :options="gmOptions" :series="gmSeries" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts

const props = defineProps({
  filters: Object,
  metrics: Object,
})

const form = reactive({
  from: props.filters?.from || null,
  to: props.filters?.to || null,
})

function apply() {
  router.get(route('reports.profit-loss'), { ...form }, { preserveState: true, replace: true })
}

function currency(n) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(n || 0))
}

// Charts
const donutSeries = [Number(props.metrics?.cogs || 0), Number(props.metrics?.gross_profit || 0)]
const donutOptions = {
  labels: ['COGS', 'Gross Profit'],
  legend: { position: 'bottom' },
  colors: ['#EF4444', '#10B981']
}

const gm = computed(() => {
  const rev = Number(props.metrics?.revenue || 0); if (!rev) return 0
  return Math.max(0, Math.min(100, (Number(props.metrics?.gross_profit || 0) / rev) * 100))
})
const gmSeries = [Number(gm.value.toFixed(2))]
const gmOptions = {
  labels: ['Gross Margin %'],
  plotOptions: { radialBar: { hollow: { size: '60%' }, dataLabels: { value: { formatter: (v) => `${v}%` } } } },
  colors: ['#6366F1']
}
</script>

<style scoped>
.input { @apply border rounded px-3 py-2 w-full; }
.card { @apply bg-white overflow-hidden shadow-sm sm:rounded-lg p-4; }
</style>


