<template>
  <AuthenticatedLayout>
    <Head title="Customer Dues" />
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customer Dues</h2>
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

      <div class="card">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold">Outstanding by Customer</h3>
          <div class="text-sm text-gray-500">Total Due: {{ currency(totalDue) }}</div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="text-left text-sm text-gray-500">
                <th class="py-2">Customer</th>
                <th class="py-2">Invoices</th>
                <th class="py-2">Due</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in byCustomer" :key="row.name" class="border-t">
                <td class="py-2">{{ row.name }}</td>
                <td class="py-2">{{ row.invoices }}</td>
                <td class="py-2">{{ currency(row.due) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  filters: Object,
  byCustomer: Array,
})

const form = reactive({
  from: props.filters?.from || null,
  to: props.filters?.to || null,
})

function apply() {
  router.get(route('reports.customer-dues'), { ...form }, { preserveState: true, replace: true })
}

function currency(n) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(n || 0))
}

const totalDue = computed(() => (props.byCustomer || []).reduce((s, r) => s + Number(r.due || 0), 0))
</script>

<style scoped>
.input { @apply border rounded px-3 py-2 w-full; }
.card { @apply bg-white overflow-hidden shadow-sm sm:rounded-lg p-4; }
</style>


