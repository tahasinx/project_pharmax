<template>
    <Head title="Stock Alerts" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Stock Alerts & Notifications
                </h2>
                <Link :href="route('stocks.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Stock
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Low Stock Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-red-600 text-lg">⚠️</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Low Stock Alerts</h3>
                            <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">
                                {{ lowStock.length }} items
                            </span>
                        </div>

                        <div v-if="lowStock.length === 0" class="text-center py-8 text-gray-500">
                            No low stock alerts
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Medicine
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Stock
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Min Level
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Shortage
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stock in lowStock" :key="stock.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ stock.medicine.name }}</div>
                                                <div class="text-sm text-gray-500">{{ stock.medicine.generic_name }}</div>
                                                <div class="text-xs text-gray-400">{{ stock.medicine.category?.name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium text-red-600">{{ stock.quantity }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ stock.min_stock_level }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="text-red-600 font-medium">
                                                {{ stock.min_stock_level - stock.quantity }} units
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('stocks.edit', stock.id)"
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                Update Stock
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-orange-600 text-lg">⏰</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Expiring Soon (Next 30 Days)</h3>
                            <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full">
                                {{ expiringSoon.length }} items
                            </span>
                        </div>

                        <div v-if="expiringSoon.length === 0" class="text-center py-8 text-gray-500">
                            No medicines expiring soon
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
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
                                            Days Left
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stock in expiringSoon" :key="stock.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ stock.medicine.name }}</div>
                                                <div class="text-sm text-gray-500">{{ stock.medicine.generic_name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ stock.batch_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ stock.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(stock.expiry_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getDaysLeftColor(stock.daysUntilExpiry())"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ stock.daysUntilExpiry() }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('stocks.edit', stock.id)"
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                Update Stock
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Expired Stock Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-gray-600 text-lg">❌</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Expired Stock</h3>
                            <span class="ml-2 bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded-full">
                                {{ expired.length }} items
                            </span>
                        </div>

                        <div v-if="expired.length === 0" class="text-center py-8 text-gray-500">
                            No expired stock
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
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
                                            Days Overdue
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stock in expired" :key="stock.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ stock.medicine.name }}</div>
                                                <div class="text-sm text-gray-500">{{ stock.medicine.generic_name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ stock.batch_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ stock.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(stock.expiry_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ Math.abs(stock.daysUntilExpiry()) }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('stocks.edit', stock.id)"
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                Update Stock
                                            </Link>
                                        </td>
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
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    lowStock: Array,
    expired: Array,
    expiringSoon: Array
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getDaysLeftColor = (days) => {
    if (days <= 7) return 'bg-red-100 text-red-800'
    if (days <= 15) return 'bg-orange-100 text-orange-800'
    return 'bg-yellow-100 text-yellow-800'
}
</script>
