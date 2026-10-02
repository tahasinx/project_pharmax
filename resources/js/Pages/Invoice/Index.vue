<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Invoices</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('invoices.create')" class="btn btn-primary btn-sm">Create Invoice</Link>
                <Link :href="route('pos')" class="btn btn-success btn-sm">POS Sales</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <div class="listing-filters bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search by invoice number, customer..."
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

                <!-- Invoice List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="invoices.data.length === 0" class="text-center py-8 text-gray-500">
                            No invoices found
                        </div>
                        <div v-else class="overflow-x-auto">
                            <LunaTable title="Invoices" :pagination="invoices">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Invoice #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Discount
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
                                    <tr v-for="invoice in filteredInvoices" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ invoice.invoice_no }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ invoice.customer?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(invoice.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ props.ui.currency_position === 'before' ? props.ui.currency_symbol : '' }}{{ parseFloat(invoice.total_amount).toFixed(2) }}{{ props.ui.currency_position === 'after' ? props.ui.currency_symbol : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ props.ui.currency_position === 'before' ? props.ui.currency_symbol : '' }}{{ parseFloat(invoice.invoice_discount || 0).toFixed(2) }}{{ props.ui.currency_position === 'after' ? props.ui.currency_symbol : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ props.ui.currency_position === 'before' ? props.ui.currency_symbol : '' }}{{ parseFloat(invoice.paid_amount).toFixed(2) }}{{ props.ui.currency_position === 'after' ? props.ui.currency_symbol : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                                {{ props.ui.currency_position === 'before' ? props.ui.currency_symbol : '' }}{{ parseFloat(invoice.due_amount).toFixed(2) }}{{ props.ui.currency_position === 'after' ? props.ui.currency_symbol : '' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="invoice.due_amount > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ invoice.due_amount > 0 ? 'Pending' : 'Paid' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('invoices.show', invoice.id)"
                                                      class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </Link>
                                                <Link :href="route('invoices.edit', invoice.id)"
                                                      class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </Link>
                                                <Link :href="route('invoices.print', invoice.id)"
                                                      class="text-green-600 hover:text-green-900">
                                                    Print
                                                </Link>
                                                <button @click="deleteInvoice(invoice.id)"
                                                        class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
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
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({
    title: 'Invoices'
})

const props = defineProps({
    invoices: Object,
    ui: Object
})

const search = ref('')
const dateFrom = ref('')
const dateTo = ref('')

const filteredInvoices = computed(() => {
    let filtered = props.invoices.data

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(invoice =>
            invoice.invoice_no.toLowerCase().includes(searchLower) ||
            invoice.customer?.name.toLowerCase().includes(searchLower)
        )
    }

    if (dateFrom.value) {
        filtered = filtered.filter(invoice => invoice.date >= dateFrom.value)
    }

    if (dateTo.value) {
        filtered = filtered.filter(invoice => invoice.date <= dateTo.value)
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

const deleteInvoice = (id) => {
    destroyRecord('invoices.destroy', id, 'Delete this invoice?', 'The invoice has been deleted.')
}
</script>
