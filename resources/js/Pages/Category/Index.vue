<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Categories</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('categories.create')" class="btn btn-primary btn-sm">Add Category</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="listing-filters bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search categories..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="statusFilter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Categories</option>
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

                <!-- Category List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="categories.length === 0" class="text-center py-8 text-gray-500">
                            No categories found
                        </div>
                        <div v-else class="overflow-x-auto">
                            <LunaTable title="Category">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
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
                                    <tr v-for="category in filteredCategories" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ category.description || 'No description' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ category.medicines_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="category.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ category.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('categories.edit', category.id)"
                                                      class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </Link>
                                                <button @click="deleteCategory(category.id)"
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

const props = defineProps({
    categories: Array
})

const search = ref('')
const statusFilter = ref('')

const filteredCategories = computed(() => {
    let filtered = props.categories

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(category =>
            category.name.toLowerCase().includes(searchLower) ||
            (category.description && category.description.toLowerCase().includes(searchLower))
        )
    }

    if (statusFilter.value !== '') {
        filtered = filtered.filter(category => category.status == statusFilter.value)
    }

    return filtered
})

const clearFilters = () => {
    search.value = ''
    statusFilter.value = ''
}

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('categories.destroy', id))
    }
}
</script>
