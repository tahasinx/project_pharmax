<template>
  <AuthenticatedLayout>
    <Head title="Reports" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link class="card" :href="route('reports.sales')">Sales Reports</Link>
          <Link class="card" :href="route('reports.purchases')">Purchase Reports</Link>
          <Link class="card" :href="route('reports.profit-loss')">Profit / Loss</Link>
          <Link class="card" :href="route('reports.customer-dues')">Customer Dues</Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>

<style scoped>
.card { @apply bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center font-medium hover:bg-gray-50; }
</style>


