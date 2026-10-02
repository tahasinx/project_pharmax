<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Manufacturer</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('manufacturers.create')" class="btn btn-primary btn-sm">Add Manufacturer</Link>
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
                                       @keyup.enter="applyFilters"
                                       @input="debounceSearch"
                                       type="text"
                                       placeholder="Search manufacturers..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="statusFilter"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Manufacturers</option>
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

                <!-- Manufacturer List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="manufacturers.data.length === 0" class="text-center py-8 text-gray-500">
                            No manufacturers found
                        </div>
                        <div v-else class="overflow-x-auto">
                            <LunaTable title="Manufacturers" :pagination="manufacturers">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Manufacturer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Medicines Count
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
                                    <tr v-for="manufacturer in manufacturers.data" :key="manufacturer.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ manufacturer.name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ manufacturer.id }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm text-gray-900">{{ manufacturer.mobile || 'No mobile' }}</div>
                                                <div class="text-sm text-gray-500">{{ manufacturer.email || 'No email' }}</div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ manufacturer.medicines_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="manufacturer.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ manufacturer.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <div class="dt-actions">
                                                <Link :href="route('manufacturers.show', manufacturer.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                                <Link :href="route('manufacturers.edit', manufacturer.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteManufacturer(manufacturer.id)"><i class="bi bi-trash"></i></button>
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

const props = defineProps({
    manufacturers: Object, // Paginated data
    filters: Object
})

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')

const applyFilters = () => {
    router.get(route('manufacturers.index'), {
        search: search.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    search.value = ''
    statusFilter.value = ''
    router.get(route('manufacturers.index'), {}, {
        preserveState: true,
        replace: true
    })
}

// Debounce search
let searchTimeout = null
const debounceSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

const deleteManufacturer = (id) => {
    destroyRecord('manufacturers.destroy', id, 'Delete this manufacturer?', 'The manufacturer has been deleted.')
}
</script>
