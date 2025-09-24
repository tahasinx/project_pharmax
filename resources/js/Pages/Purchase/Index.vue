<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Purchase Management
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('purchases.create')"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        New Purchase
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search by purchase number, manufacturer..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input v-model="dateFrom"
                                       type="date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input v-model="dateTo"
                                       type="date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button @click="clearFilters"
                                        class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="purchases.data.length === 0" class="text-center py-8 text-gray-500">
                            No purchases found
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Purchase #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Manufacturer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grand Total
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="purchase in filteredPurchases" :key="purchase.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ purchase.purchase_no }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ purchase.manufacturer?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(purchase.purchase_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ purchase.items_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(purchase.grand_total).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="purchase.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ purchase.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('purchases.show', purchase.id)"
                                                      class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </Link>
                                                <Link :href="route('purchases.edit', purchase.id)"
                                                      class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </Link>
                                                <button @click="deletePurchase(purchase.id)"
                                                        class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="purchases.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link v-if="purchases.prev_page_url"
                                          :href="purchases.prev_page_url"
                                          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </Link>
                                    <Link v-if="purchases.next_page_url"
                                          :href="purchases.next_page_url"
                                          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing {{ purchases.from }} to {{ purchases.to }} of {{ purchases.total }} results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <Link v-for="link in purchases.links"
                                                  :key="link.label"
                                                  :href="link.url"
                                                  v-html="link.label"
                                                  :class="[
                                                      link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                  ]">
                                            </Link>
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    purchases: Object
})

const search = ref('')
const dateFrom = ref('')
const dateTo = ref('')

const filteredPurchases = computed(() => {
    let filtered = props.purchases.data

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(purchase =>
            purchase.purchase_no.toLowerCase().includes(searchLower) ||
            purchase.manufacturer?.name.toLowerCase().includes(searchLower)
        )
    }

    if (dateFrom.value) {
        filtered = filtered.filter(purchase => purchase.purchase_date >= dateFrom.value)
    }

    if (dateTo.value) {
        filtered = filtered.filter(purchase => purchase.purchase_date <= dateTo.value)
    }

    return filtered
})

const clearFilters = () => {
    search.value = ''
    dateFrom.value = ''
    dateTo.value = ''
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const deletePurchase = (id) => {
    if (confirm('Are you sure you want to delete this purchase?')) {
        router.delete(route('purchases.destroy', id))
    }
}
</script>
