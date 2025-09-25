<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 font-bold">👥</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Customers
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.total_customers }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-600 font-bold">💊</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Medicines
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.total_medicines }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <span class="text-yellow-600 font-bold">💰</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Today's Sales
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ ui.currency_position === 'before' ? ui.currency_symbol : '' }}{{ stats.todays_sales.toLocaleString() }}{{ ui.currency_position === 'after' ? ui.currency_symbol : '' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 font-bold">🛒</span>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Today's Purchases
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ ui.currency_position === 'before' ? ui.currency_symbol : '' }}{{ stats.todays_purchases.toLocaleString() }}{{ ui.currency_position === 'after' ? ui.currency_symbol : '' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Best Selling Products -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Best Selling Products</h3>
                        <div v-if="bestSellingProducts.length > 0" class="space-y-3">
                            <div v-for="(product, index) in bestSellingProducts.slice(0, 5)" :key="product.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-sm font-medium text-blue-600">
                                        {{ index + 1 }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                                        <p class="text-xs text-gray-500">{{ product.category?.name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">{{ ui.currency_position === 'before' ? ui.currency_symbol : '' }}{{ Number(product.display_price).toFixed(2) }}{{ ui.currency_position === 'after' ? ui.currency_symbol : '' }}</p>
                                    <p class="text-xs text-gray-500">{{ product.sold_quantity }} sold</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-gray-400 text-2xl">📊</span>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No Sales Yet</h4>
                            <p class="text-gray-500 mb-4">Start selling medicines to see your best-selling products here.</p>
                            <Link :href="route('pos')"
                                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                                <span class="mr-2">🛒</span>
                                Start POS Sales
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link :href="route('pos')"
                                  class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-blue-600 font-bold">🛒</span>
                                </div>
                                <span class="text-sm font-medium text-blue-900">POS Sales</span>
                            </Link>

                            <Link :href="route('medicines.create')"
                                  class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-green-600 font-bold">➕</span>
                                </div>
                                <span class="text-sm font-medium text-green-900">Add Medicine</span>
                            </Link>

                            <Link :href="route('customers.create')"
                                  class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-purple-600 font-bold">👤</span>
                                </div>
                                <span class="text-sm font-medium text-purple-900">Add Customer</span>
                            </Link>

                            <Link :href="route('purchases.create')"
                                  class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-yellow-600 font-bold">🚚</span>
                                </div>
                                <span class="text-sm font-medium text-yellow-900">New Purchase</span>
                            </Link>
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
    bestSellingProducts: Array,
    monthlyData: Array,
    ui: Object
})
</script>

