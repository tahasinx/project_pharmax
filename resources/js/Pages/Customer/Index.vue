<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Customers</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('customers.create')" class="btn btn-primary btn-sm">Add Customer</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <div class="listing-filters bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search by name, mobile, email..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="statusFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Customers</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
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

                <!-- Customer List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="!customers?.data || customers.data.length === 0" class="text-center py-8 text-gray-500">
                            No customers found
                        </div>
                        <div v-else class="overflow-x-auto">
                            <LunaTable title="Customers" :pagination="customers">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Location
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Invoices
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="customer in filteredCustomers" :key="customer.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ customer.id }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm text-gray-900">{{ customer.mobile }}</div>
                                                <div class="text-sm text-gray-500">{{ customer.email || 'No email' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm text-gray-900">{{ customer.city || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ customer.state || 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="customer.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ customer.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ customer.invoices_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('customers.show', customer.id)"
                                                      class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </Link>
                                                <Link :href="route('customers.edit', customer.id)"
                                                      class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </Link>
                                                <button @click="deleteCustomer(customer.id)"
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

defineOptions({
    title: 'Customers'
})

const props = defineProps({
    customers: Object
})

const search = ref('')
const statusFilter = ref('')

const filteredCustomers = computed(() => {
    let filtered = props.customers?.data || []

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(customer =>
            customer.name.toLowerCase().includes(searchLower) ||
            customer.mobile.toLowerCase().includes(searchLower) ||
            (customer.email && customer.email.toLowerCase().includes(searchLower))
        )
    }

    if (statusFilter.value !== '') {
        filtered = filtered.filter(customer => customer.status == statusFilter.value)
    }

    return filtered
})

const clearFilters = () => {
    search.value = ''
    statusFilter.value = ''
}

const deleteCustomer = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('customers.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Customer has been deleted.',
                        'success'
                    )
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Something went wrong while deleting.',
                        'error'
                    )
                }
            })
        }
    })
}
</script>
