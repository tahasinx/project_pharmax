<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Invoice #{{ invoice.invoice_no }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('invoices.edit', invoice.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Invoice
                    </Link>
                    <Link :href="route('invoices.print', invoice.id)"
                          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Print Invoice
                    </Link>
                    <Link :href="route('invoices.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Invoices
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Invoice Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Invoice Header -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Information</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Invoice Number:</span>
                                                <span class="font-medium">{{ invoice.invoice_no }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Date:</span>
                                                <span class="font-medium">{{ formatDate(invoice.date) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Payment Type:</span>
                                                <span class="font-medium capitalize">{{ invoice.payment_type }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Status:</span>
                                                <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'"
                                                      class="font-medium">
                                                    {{ invoice.due_amount > 0 ? 'Pending' : 'Paid' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Name:</span>
                                                <span class="font-medium">{{ invoice.customer?.name || 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Mobile:</span>
                                                <span class="font-medium">{{ invoice.customer?.mobile || 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Email:</span>
                                                <span class="font-medium">{{ invoice.customer?.email || 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Address:</span>
                                                <span class="font-medium">{{ invoice.customer?.address || 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                                <div class="overflow-x-auto">
                                    <LunaTable title="Show">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Medicine
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Batch ID
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Quantity
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Rate
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Discount
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="item in invoice.items" :key="item.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ item.medicine?.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ item.medicine?.generic_name }}</div>
                                                    </div>
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
                                                {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(item.discount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
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

                        <!-- Notes -->
                        <div v-if="invoice.details" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                                <p class="text-gray-700">{{ invoice.details }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Financial Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ (invoice.total_amount - invoice.total_tax).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="font-medium">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(invoice.total_tax).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium">-{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(invoice.total_discount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total Amount:</span>
                                        <span>{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(invoice.total_amount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Paid Amount:</span>
                                        <span class="font-medium text-green-600">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(invoice.paid_amount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Due Amount:</span>
                                        <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'"
                                              class="font-medium">
                                            {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(invoice.due_amount).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Bank Information -->
                                <div v-if="invoice.bank" class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Bank Information</h4>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Bank:</span>
                                            <span class="font-medium">{{ invoice.bank.name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Created By -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Created By</h4>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">User:</span>
                                            <span class="font-medium">{{ invoice.user?.name || 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Created:</span>
                                            <span class="font-medium">{{ formatDate(invoice.created_at) }}</span>
                                        </div>
                                    </div>
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
    invoice: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
