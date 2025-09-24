<template>
  <AuthenticatedLayout>
    <Head title="Sales Reports" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sales Reports</h2>
    </template>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <form class="grid grid-cols-1 md:grid-cols-4 gap-3" @submit.prevent="apply">
      <input type="date" v-model="form.from" class="input" />
      <input type="date" v-model="form.to" class="input" />
      <div class="relative">
        <input type="text" v-model="medicineQuery" @input="searchMedicines" placeholder="Search medicine..." class="input" />
        <div v-if="medicineResults.length" class="absolute z-10 bg-white border rounded mt-1 w-full max-h-56 overflow-auto">
          <div v-for="m in medicineResults" :key="m.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectMedicine(m)">
            {{ m.name }}
          </div>
        </div>
        <div class="text-sm text-gray-500 mt-1" v-if="form.medicine_id">Selected: {{ selectedMedicineName }}</div>
      </div>
      <div class="relative">
        <input type="text" v-model="customerQuery" @input="searchCustomers" placeholder="Search customer..." class="input" />
        <div v-if="customerResults.length" class="absolute z-10 bg-white border rounded mt-1 w-full max-h-56 overflow-auto">
          <div v-for="c in customerResults" :key="c.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectCustomer(c)">
            {{ c.name }}
          </div>
        </div>
        <div class="text-sm text-gray-500 mt-1" v-if="form.customer_id">Selected: {{ selectedCustomerName }}</div>
      </div>
      <div class="md:col-span-4">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
      </div>
    </form>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card"><div class="text-sm text-gray-500">Total Qty</div><div class="text-2xl font-semibold">{{ summary.total_quantity }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Total Sales</div><div class="text-2xl font-semibold">{{ currency(summary.total_sales) }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Avg Price</div><div class="text-2xl font-semibold">{{ currency(avgPrice) }}</div></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card"><apexchart type="bar" height="300" :options="dailyOptions" :series="dailySeries" /></div>
        <div class="card"><apexchart type="line" height="300" :options="monthlyOptions" :series="monthlySeries" /></div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts

const props = defineProps({
  filters: Object,
  summary: Object,
  daily: Object,
  monthly: Object,
})

const form = reactive({
  from: props.filters?.from || null,
  to: props.filters?.to || null,
  medicine_id: props.filters?.medicine_id ?? null,
  customer_id: props.filters?.customer_id ?? null,
})

function apply() {
  router.get(route('reports.sales'), { ...form }, { preserveState: true, replace: true })
}

function currency(n) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(n || 0))
}

// Async search
const medicineQuery = ref('')
const customerQuery = ref('')
const medicineResults = ref([])
const customerResults = ref([])
const selectedMedicineName = computed(() => medicineResults.value.find(m => m.id === form.medicine_id)?.name || '')
const selectedCustomerName = computed(() => customerResults.value.find(c => c.id === form.customer_id)?.name || '')

let medicineTimer, customerTimer
function searchMedicines() {
  clearTimeout(medicineTimer)
  if (!medicineQuery.value) { medicineResults.value = []; return }
  medicineTimer = setTimeout(async () => {
    const res = await fetch(route('api.medicines.search', { q: medicineQuery.value }))
    medicineResults.value = await res.json()
  }, 250)
}
function selectMedicine(m) {
  form.medicine_id = m.id
  medicineQuery.value = m.name
  medicineResults.value = []
}
function searchCustomers() {
  clearTimeout(customerTimer)
  if (!customerQuery.value) { customerResults.value = []; return }
  customerTimer = setTimeout(async () => {
    const res = await fetch(route('api.customers.search', { q: customerQuery.value }))
    customerResults.value = await res.json()
  }, 250)
}
function selectCustomer(c) {
  form.customer_id = c.id
  customerQuery.value = c.name
  customerResults.value = []
}

// Charts
const avgPrice = computed(() => (props.summary?.total_quantity ? (props.summary.total_sales / props.summary.total_quantity) : 0))
const dailyLabels = Object.keys(props.daily || {})
const dailyQty = dailyLabels.map(k => props.daily[k]?.quantity || 0)
const dailySales = dailyLabels.map(k => props.daily[k]?.sales || 0)
const dailyOptions = {
  chart: { toolbar: { show: false } },
  xaxis: { categories: dailyLabels },
  dataLabels: { enabled: false },
  title: { text: 'Daily Sales', style: { fontWeight: 600 } }
}
const dailySeries = [
  { name: 'Qty', type: 'column', data: dailyQty },
  { name: 'Sales', type: 'column', data: dailySales }
]

const monthlyLabels = Object.keys(props.monthly || {})
const monthlyQty = monthlyLabels.map(k => props.monthly[k]?.quantity || 0)
const monthlySales = monthlyLabels.map(k => props.monthly[k]?.sales || 0)
const monthlyOptions = {
  chart: { toolbar: { show: false } },
  xaxis: { categories: monthlyLabels },
  dataLabels: { enabled: false },
  title: { text: 'Monthly Sales', style: { fontWeight: 600 } }
}
const monthlySeries = [
  { name: 'Qty', data: monthlyQty },
  { name: 'Sales', data: monthlySales }
]
</script>

<style scoped>
.input { @apply border rounded px-3 py-2 w-full; }
.card { @apply bg-white overflow-hidden shadow-sm sm:rounded-lg p-4; }
</style>


