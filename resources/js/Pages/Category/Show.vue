<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Category Details - {{ category.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('categories.edit', category.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Category
                    </Link>
                    <Link :href="route('categories.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Categories
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Category Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Category Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Category Name</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ category.name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Status</label>
                                            <span :class="category.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ category.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Medicines Count</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ category.medicines_count || 0 }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Created</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(category.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="category.description" class="mt-6">
                                    <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                                    <p class="text-gray-700 whitespace-pre-line">{{ category.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Medicines in this Category -->
                        <div v-if="category.medicines && category.medicines.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Medicines in this Category</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Medicine
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Manufacturer
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Price
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="medicine in category.medicines" :key="medicine.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ medicine.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ medicine.generic_name || 'No generic name' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ medicine.manufacturer?.name || 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ parseFloat(medicine.price).toFixed(2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="medicine.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                        {{ medicine.status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Summary & Actions -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Category Summary</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Name:</span>
                                        <span class="font-medium">{{ category.name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Medicines:</span>
                                        <span class="font-medium">{{ category.medicines_count || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span :class="category.status ? 'text-green-600' : 'text-red-600'"
                                              class="font-medium">
                                            {{ category.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Quick Actions</h4>
                                    <div class="space-y-2">
                                        <Link :href="route('categories.edit', category.id)"
                                              class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Edit Category
                                        </Link>
                                        <Link :href="route('medicines.create')"
                                              class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Add Medicine
                                        </Link>
                                    </div>
                                </div>

                                <!-- Created Info -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Created</h4>
                                    <p class="text-sm text-gray-600">{{ formatDate(category.created_at) }}</p>
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
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    category: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
