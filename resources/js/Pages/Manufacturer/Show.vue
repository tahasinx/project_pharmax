<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manufacturer Details
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('manufacturers.edit', manufacturer.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Manufacturer
                    </Link>
                    <Link :href="route('manufacturers.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Manufacturers
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer Name</label>
                                        <p class="text-sm text-gray-900">{{ manufacturer.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <span :class="manufacturer.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ manufacturer.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                                        <p class="text-sm text-gray-900">{{ manufacturer.mobile || 'Not provided' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <p class="text-sm text-gray-900">{{ manufacturer.email || 'Not provided' }}</p>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Address & Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                        <p class="text-sm text-gray-900">{{ manufacturer.address || 'Not provided' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                                        <p class="text-sm text-gray-900">{{ manufacturer.details || 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Medicines:</span>
                                        <span class="font-medium">{{ manufacturer.medicines?.length || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Created:</span>
                                        <span class="font-medium">{{ formatDate(manufacturer.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Updated:</span>
                                        <span class="font-medium">{{ formatDate(manufacturer.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <Link :href="route('medicines.create', { manufacturer_id: manufacturer.id })"
                                          class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Add Medicine
                                    </Link>
                                    <Link :href="route('purchases.create', { manufacturer_id: manufacturer.id })"
                                          class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Create Purchase
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medicines List -->
                <div v-if="manufacturer.medicines && manufacturer.medicines.length > 0" class="mt-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Medicines by this Manufacturer</h3>
                            <div class="overflow-x-auto">
                                <LunaTable title="Show">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Medicine
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Category
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Price
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
                                        <tr v-for="medicine in manufacturer.medicines" :key="medicine.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ medicine.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ medicine.generic_name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ medicine.category?.name || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ medicine.price }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="medicine.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                    {{ medicine.status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link :href="route('medicines.show', medicine.id)"
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    manufacturer: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>
