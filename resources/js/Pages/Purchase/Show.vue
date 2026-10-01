<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Purchase #{{ purchase.purchase_no }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('purchases.edit', purchase.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Purchase
                    </Link>
                    <Link :href="route('purchases.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Purchases
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Purchase Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Purchase ID</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ purchase.purchase_id }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Purchase Number</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ purchase.purchase_no }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Purchase Date</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(purchase.purchase_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Manufacturer</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ purchase.manufacturer?.name || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Created By</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ purchase.user?.name || 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Status</label>
                                            <span :class="purchase.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ purchase.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="purchase.details" class="mt-6">
                                    <label class="block text-sm font-medium text-gray-500 mb-2">Details</label>
                                    <p class="text-gray-700 whitespace-pre-line">{{ purchase.details }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Items -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h3>
                                <div class="overflow-x-auto">
                                    <LunaTable title="Show">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="item in purchase.items" :key="item.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ item.medicine?.name || 'Unknown' }}</div>
                                                    <div class="text-sm text-gray-500">{{ item.medicine?.generic_name || '' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ item.batch_id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(item.rate).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(item.discount || 0).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(item.total_amount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
</LunaTable>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Summary</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Purchase ID:</span>
                                        <span class="font-medium">{{ purchase.purchase_id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Purchase No:</span>
                                        <span class="font-medium">{{ purchase.purchase_no }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Manufacturer:</span>
                                        <span class="font-medium">{{ purchase.manufacturer?.name || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span :class="purchase.status ? 'text-green-600' : 'text-red-600'"
                                              class="font-medium">
                                            {{ purchase.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Financial Summary -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Financial Summary</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ (parseFloat(purchase.grand_total) - parseFloat(purchase.total_tax || 0)).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Tax:</span>
                                            <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(purchase.total_tax || 0).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Discount:</span>
                                            <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(purchase.total_discount || 0).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="flex justify-between text-lg font-semibold">
                                            <span>Total:</span>
                                            <span>{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(purchase.grand_total).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Quick Actions</h4>
                                    <div class="space-y-2">
                                        <Link :href="route('purchases.edit', purchase.id)"
                                              class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Edit Purchase
                                        </Link>
                                        <Link :href="route('purchases.index')"
                                              class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Back to Purchases
                                        </Link>
                                    </div>
                                </div>

                                <!-- Created Info -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Created</h4>
                                    <p class="text-sm text-gray-600">{{ formatDate(purchase.created_at) }}</p>
                                    <p class="text-sm text-gray-600">By: {{ purchase.user?.name || 'Unknown' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    purchase: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
