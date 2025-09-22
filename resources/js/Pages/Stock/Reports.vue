<template>
    <Head title="Stock Reports" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Stock Reports & Analytics
                </h2>
                <Link :href="route('stocks.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Stock
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">⚠️</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Low Stock</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.low_stock_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                        <span class="text-orange-600 text-lg">⏰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.expiring_soon_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <span class="text-gray-600 text-lg">❌</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Expired</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.expired_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-lg">💊</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Medicines</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_medicines }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-600 text-lg">💰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Stock Value</p>
                                    <p class="text-2xl font-semibold text-gray-900">${{ formatCurrency(stats.total_stock_value) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock by Category Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Distribution by Category</h3>
                        <div class="space-y-4">
                            <div v-for="(quantity, category) in stockByCategory" :key="category" class="flex items-center">
                                <div class="w-32 text-sm font-medium text-gray-700">{{ category }}</div>
                                <div class="flex-1 mx-4">
                                    <div class="bg-gray-200 rounded-full h-4">
                                        <div class="bg-blue-600 h-4 rounded-full"
                                             :style="{ width: getPercentage(quantity) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="w-16 text-sm text-gray-900 text-right">{{ quantity }} units</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Medicines -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Medicines Expiring Soon (Next 30 Days)</h3>
                        <div v-if="expiringMedicines.length === 0" class="text-center py-8 text-gray-500">
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
                                    <tr v-for="stock in expiringMedicines" :key="stock.id">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: Object,
    stockByCategory: Object,
    expiringMedicines: Array
})

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toFixed(2)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getPercentage = (quantity) => {
    const max = Math.max(...Object.values(props.stockByCategory))
    return max > 0 ? (quantity / max) * 100 : 0
}

const getDaysLeftColor = (days) => {
    if (days <= 7) return 'bg-red-100 text-red-800'
    if (days <= 15) return 'bg-orange-100 text-orange-800'
    return 'bg-yellow-100 text-yellow-800'
}
</script>
