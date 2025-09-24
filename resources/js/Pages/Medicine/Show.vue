<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Medicine Details - {{ medicine.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.edit', medicine.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Medicine
                    </Link>
                    <Link :href="route('medicines.codes', medicine.id)"
                          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Generate Codes
                    </Link>
                    <Link :href="route('medicines.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Medicines
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Medicine Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Medicine Name</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Generic Name</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.generic_name || 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Strength</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.strength || 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Product ID</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.product_id }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Category</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.category?.name || 'Not assigned' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Manufacturer</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.manufacturer?.name || 'Not assigned' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Unit</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.unit || 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Status</label>
                                            <span :class="medicine.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ medicine.status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Selling Price</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(medicine.price).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Manufacturer Price</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ parseFloat(medicine.manufacturer_price).toFixed(2) }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Box Size</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ medicine.box_size }} {{ medicine.unit || 'units' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Profit Margin</label>
                                            <p class="mt-1 text-lg font-semibold text-green-600">
                                                {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ profitAmount }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                                ({{ profitPercentage }}%)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Product Location</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.product_location || 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Total Purchases</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.purchase_items_count || 0 }} times</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Total Sales</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ medicine.invoice_items_count || 0 }} times</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(medicine.updated_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="medicine.details" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                                <p class="text-gray-700 whitespace-pre-line">{{ medicine.details }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Summary & Actions -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Medicine Summary</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Product ID:</span>
                                        <span class="font-medium">{{ medicine.product_id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Category:</span>
                                        <span class="font-medium">{{ medicine.category?.name || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Manufacturer:</span>
                                        <span class="font-medium">{{ medicine.manufacturer?.name || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span :class="medicine.status ? 'text-green-600' : 'text-red-600'"
                                              class="font-medium">
                                            {{ medicine.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Quick Actions</h4>
                                    <div class="space-y-2">
                                        <Link :href="route('medicines.edit', medicine.id)"
                                              class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Edit Medicine
                                        </Link>
                                        <Link :href="route('medicines.codes', medicine.id)"
                                              class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded block text-center">
                                            Generate Codes
                                        </Link>
                                    </div>
                                </div>

                                <!-- Created Info -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Created</h4>
                                    <p class="text-sm text-gray-600">{{ formatDate(medicine.created_at) }}</p>
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
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    medicine: Object
})

const profitAmount = computed(() => {
    const price = parseFloat(props.medicine.price)
    const manufacturerPrice = parseFloat(props.medicine.manufacturer_price)
    return (price - manufacturerPrice).toFixed(2)
})

const profitPercentage = computed(() => {
    const price = parseFloat(props.medicine.price)
    const manufacturerPrice = parseFloat(props.medicine.manufacturer_price)
    if (manufacturerPrice === 0) return '0.0'
    return (((price - manufacturerPrice) / manufacturerPrice) * 100).toFixed(1)
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
