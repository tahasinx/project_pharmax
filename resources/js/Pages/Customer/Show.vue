<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Customer Details - {{ customer.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('customers.edit', customer.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Customer
                    </Link>
                    <Link :href="route('customers.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Customers
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Customer Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Customer Name</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Mobile Number</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.mobile }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.email || 'Not provided' }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.phone || 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Fax Number</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.fax || 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Status</label>
                                            <span :class="customer.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ customer.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Address</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ customer.address || 'Not provided' }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">City</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.city || 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">State</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.state || 'Not provided' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">ZIP Code</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ customer.zip || 'Not provided' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Country</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ customer.country || 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Invoices -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Invoices</h3>
                                <div v-if="customer.invoices && customer.invoices.length === 0" class="text-center py-8 text-gray-500">
                                    No invoices found for this customer
                                </div>
                                <div v-else class="overflow-x-auto">
                                    <LunaTable title="Show">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Invoice #
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total Amount
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Paid Amount
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Due Amount
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
                                            <tr v-for="invoice in customer.invoices?.slice(0, 10)" :key="invoice.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ invoice.invoice_no }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ formatDate(invoice.date) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ parseFloat(invoice.total_amount).toFixed(2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ parseFloat(invoice.paid_amount).toFixed(2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                                        ${{ parseFloat(invoice.due_amount).toFixed(2) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="invoice.due_amount > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                        {{ invoice.due_amount > 0 ? 'Pending' : 'Paid' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <Link :href="route('invoices.show', invoice.id)"
                                                          class="text-blue-600 hover:text-blue-900">
                                                        View
                                                    </Link>
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
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Summary</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Invoices:</span>
                                        <span class="font-medium">{{ customer.invoices?.length || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Amount:</span>
                                        <span class="font-medium">${{ totalAmount.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Paid Amount:</span>
                                        <span class="font-medium text-green-600">${{ paidAmount.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Due Amount:</span>
                                        <span :class="dueAmount > 0 ? 'text-red-600' : 'text-green-600'"
                                              class="font-medium">
                                            ${{ dueAmount.toFixed(2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Quick Actions</h4>
                                    <div class="space-y-2">
                                        <Link :href="route('invoices.create', { customer_id: customer.id })"
                                              class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Create Invoice
                                        </Link>
                                        <Link :href="route('pos', { customer_id: customer.id })"
                                              class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            POS Sale
                                        </Link>
                                    </div>
                                </div>

                                <!-- Customer Since -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Customer Since</h4>
                                    <p class="text-sm text-gray-600">{{ formatDate(customer.created_at) }}</p>
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

import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    customer: Object
})

const totalAmount = computed(() => {
    return props.customer.invoices?.reduce((sum, invoice) => sum + parseFloat(invoice.total_amount), 0) || 0
})

const paidAmount = computed(() => {
    return props.customer.invoices?.reduce((sum, invoice) => sum + parseFloat(invoice.paid_amount), 0) || 0
})

const dueAmount = computed(() => {
    return props.customer.invoices?.reduce((sum, invoice) => sum + parseFloat(invoice.due_amount), 0) || 0
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
