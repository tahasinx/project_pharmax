<template>
  <AuthenticatedLayout>
    <Head title="Purchase Reports" />
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchase Reports</h2>
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
      <div class="relative">
        <input type="text" v-model="supplierQuery" @input="searchSuppliers" placeholder="Search supplier..." class="input" />
        <div v-if="supplierResults.length" class="absolute z-10 bg-white border rounded mt-1 w-full max-h-56 overflow-auto">
          <div v-for="m in supplierResults" :key="m.id" class="px-3 py-2 hover:bg-gray-100 cursor-pointer" @click="selectSupplier(m)">{{ m.name }}</div>
        </div>
        <div class="text-sm text-gray-500 mt-1" v-if="form.manufacturer_id">Selected: {{ selectedSupplierName }}</div>
      </div>
      <div class="md:col-span-3">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
      </div>
    </form>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card"><div class="text-sm text-gray-500">Count</div><div class="text-2xl font-semibold">{{ summary.count }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Total</div><div class="text-2xl font-semibold">{{ currency(summary.total) }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Tax</div><div class="text-2xl font-semibold">{{ currency(summary.tax) }}</div></div>
        <div class="card"><div class="text-sm text-gray-500">Discount</div><div class="text-2xl font-semibold">{{ currency(summary.discount) }}</div></div>
      </div>

      <div class="card">
        <h2 class="font-semibold mb-4">By Supplier</h2>
        <LunaTable title="Purchases">
<table class="table table-striped table-hover min-w-full">
          <thead>
            <tr class="text-left text-sm text-gray-500">
              <th class="py-2">Supplier</th>
              <th class="py-2">Count</th>
              <th class="py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in bySupplier" :key="row.name" class="border-t">
              <td class="py-2">{{ row.name || 'Unknown' }}</td>
              <td class="py-2">{{ row.count }}</td>
              <td class="py-2">{{ currency(row.total) }}</td>
            </tr>
          </tbody>
        </table>
</LunaTable>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { reactive, ref, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  filters: Object,
  summary: Object,
  bySupplier: Array,
})

const form = reactive({
  from: props.filters?.from || null,
  to: props.filters?.to || null,
  manufacturer_id: props.filters?.manufacturer_id ?? null,
})

function apply() {
  router.get(route('reports.purchases'), { ...form }, { preserveState: true, replace: true })
}

function currency(n) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(n || 0))
}

// Async supplier search (reusing manufacturers index for now)
const supplierQuery = ref('')
const supplierResults = ref([])
const selectedSupplierName = computed(() => supplierResults.value.find(m => m.id === form.manufacturer_id)?.name || '')
let supplierTimer
async function searchSuppliers() {
  clearTimeout(supplierTimer)
  if (!supplierQuery.value) { supplierResults.value = []; return }
  supplierTimer = setTimeout(async () => {
    // Simple client-side search via API not present; fallback to wide search using purchases page data later if needed
    const res = await fetch(route('manufacturers.index'))
    // If this route returns HTML, consider adding a small JSON endpoint later; for now, keep manual select minimal
  }, 250)
}
function selectSupplier(m) {
  form.manufacturer_id = m.id
  supplierQuery.value = m.name
  supplierResults.value = []
}
</script>

<style scoped>
.input { @apply border rounded px-3 py-2 w-full; }
.card { @apply bg-white overflow-hidden shadow-sm sm:rounded-lg p-4; }
</style>


