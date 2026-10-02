<template>
    <Head title="Stock Management" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Stock</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('stocks.create')" class="btn btn-primary btn-sm">Add Stock</Link>
                <Link :href="route('stocks.reports')" class="btn btn-success btn-sm">Reports</Link>
                <Link :href="route('stocks.alerts')" class="btn btn-danger btn-sm">
                    Alerts
                    <span v-if="alerts.total > 0" class="badge bg-light text-danger ms-1">{{ alerts.total }}</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stock Alerts Summary -->
                <div v-if="alerts.total > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Alerts</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="text-red-600 text-2xl mr-3">⚠️</div>
                                    <div>
                                        <p class="text-sm font-medium text-red-800">Low Stock</p>
                                        <p class="text-2xl font-bold text-red-900">{{ alerts.low_stock }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="text-orange-600 text-2xl mr-3">⏰</div>
                                    <div>
                                        <p class="text-sm font-medium text-orange-800">Expiring Soon</p>
                                        <p class="text-2xl font-bold text-orange-900">{{ alerts.expiring_soon }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="text-gray-600 text-2xl mr-3">❌</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Expired</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ alerts.expired }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="listing-filters bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search medicines..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="statusFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="low">Low Stock</option>
                                    <option value="expiring">Expiring Soon</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select v-model="categoryFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
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

                <!-- Stock List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <LunaTable title="Stock" :pagination="stocks">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Medicine
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expiry Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Batch status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Weighted cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        MRP
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Purchase Price
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
                                <tr v-for="stock in filteredStocks" :key="stock.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ stock.medicine.name }}</div>
                                            <div class="text-sm text-gray-500">{{ stock.medicine.generic_name }}</div>
                                            <div class="text-xs text-gray-400">{{ stock.medicine.category?.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ stock.batch_number || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-900">{{ stock.quantity }}</span>
                                            <span v-if="isLowStock(stock)" class="ml-2 text-xs text-red-600">⚠️</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div v-if="stock.expiry_date">
                                            {{ formatDate(stock.expiry_date) }}
                                            <span v-if="isExpired(stock)" class="ml-1 text-xs text-red-600">❌</span>
                                            <span v-else-if="isExpiringSoon(stock)" class="ml-1 text-xs text-orange-600">⏰</span>
                                        </div>
                                        <span v-else class="text-gray-400">N/A</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ stock.warehouse?.name || 'Unassigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ stock.status || 'available' }}{{ stock.recalled ? ' · recalled' : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ Number(weightedCosts[stock.medicine_id] || 0).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ stock.mrp != null ? Number(stock.mrp).toFixed(2) : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(stock.purchase_price || 0).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(stock)"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getStatusText(stock) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="dt-actions">
                                            <Link :href="route('stocks.show', stock.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                            <Link :href="route('stocks.edit', stock.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                            <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteStock(stock.id)"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
</LunaTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { ref, computed } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

const props = defineProps({
    stocks: Object,
    alerts: Object,
    weightedCosts: { type: Object, default: () => ({}) }
})

const search = ref('')
const statusFilter = ref('')
const categoryFilter = ref('')

const filteredStocks = computed(() => {
    let filtered = props.stocks.data

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(stock =>
            stock.medicine.name.toLowerCase().includes(searchLower) ||
            stock.medicine.generic_name.toLowerCase().includes(searchLower) ||
            (stock.batch_number && stock.batch_number.toLowerCase().includes(searchLower))
        )
    }

    if (statusFilter.value) {
        filtered = filtered.filter(stock => {
            switch (statusFilter.value) {
                case 'low':
                    return stock.quantity <= stock.min_stock_level
                case 'expiring':
                    return stock.expiry_date && isExpiringSoon(stock)
                case 'expired':
                    return stock.expiry_date && isExpired(stock)
                default:
                    return true
            }
        })
    }

    if (categoryFilter.value) {
        filtered = filtered.filter(stock =>
            stock.medicine.category?.name === categoryFilter.value
        )
    }

    return filtered
})

const categories = computed(() => {
    const cats = new Set()
    props.stocks.data.forEach(stock => {
        if (stock.medicine.category?.name) {
            cats.add(stock.medicine.category.name)
        }
    })
    return Array.from(cats)
})

const clearFilters = () => {
    search.value = ''
    statusFilter.value = ''
    categoryFilter.value = ''
}

const getStatusColor = (stock) => {
    if (isExpired(stock)) return 'bg-red-100 text-red-800'
    if (isExpiringSoon(stock)) return 'bg-orange-100 text-orange-800'
    if (isLowStock(stock)) return 'bg-yellow-100 text-yellow-800'
    return 'bg-green-100 text-green-800'
}

const getStatusText = (stock) => {
    if (isExpired(stock)) return 'Expired'
    if (isExpiringSoon(stock)) return 'Expiring Soon'
    if (isLowStock(stock)) return 'Low Stock'
    return 'Good'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const deleteStock = (id) => {
    destroyRecord('stocks.destroy', id, 'Delete this stock entry?', 'The stock entry has been deleted.')
}

const isExpired = (stock) => {
    if (!stock.expiry_date) return false
    return new Date(stock.expiry_date) < new Date()
}

const isExpiringSoon = (stock) => {
    if (!stock.expiry_date) return false
    const expiryDate = new Date(stock.expiry_date)
    const now = new Date()
    const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000))
    return expiryDate >= now && expiryDate <= thirtyDaysFromNow
}

const isLowStock = (stock) => {
    return stock.quantity <= stock.min_stock_level
}
</script>
