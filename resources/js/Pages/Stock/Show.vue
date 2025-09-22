<template>
    <Head title="Stock Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Stock Details: {{ stock.medicine?.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('stocks.edit', stock.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Stock
                    </Link>
                    <Link :href="route('stocks.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Stock
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
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine</label>
                                        <p class="text-sm text-gray-900">{{ stock.medicine?.name }}</p>
                                        <p class="text-xs text-gray-500">{{ stock.medicine?.generic_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                                        <p class="text-sm text-gray-900">{{ stock.batch_number || 'Not provided' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Quantity</label>
                                        <p class="text-sm text-gray-900 font-medium">{{ stock.quantity }} units</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                                        <div v-if="stock.expiry_date" class="flex items-center">
                                            <p class="text-sm text-gray-900">{{ formatDate(stock.expiry_date) }}</p>
                                            <span v-if="isExpired(stock)" class="ml-2 text-xs text-red-600">❌ Expired</span>
                                            <span v-else-if="isExpiringSoon(stock)" class="ml-2 text-xs text-orange-600">⏰ Expiring Soon</span>
                                        </div>
                                        <p v-else class="text-sm text-gray-500">Not set</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level</label>
                                        <p class="text-sm text-gray-900">{{ stock.min_stock_level }} units</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Stock Level</label>
                                        <p class="text-sm text-gray-900">{{ stock.max_stock_level || 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Supplier</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price</label>
                                        <p class="text-sm text-gray-900">${{ parseFloat(stock.purchase_price || 0).toFixed(2) }} per unit</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                                        <p class="text-sm text-gray-900">${{ parseFloat(stock.selling_price || 0).toFixed(2) }} per unit</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                                        <p class="text-sm text-gray-900">{{ stock.supplier || 'Not provided' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <span :class="stock.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ stock.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="stock.notes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                                <p class="text-sm text-gray-900">{{ stock.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Status</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Stock Level:</span>
                                        <span :class="getStockStatusColor()" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getStockStatusText() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Expiry Status:</span>
                                        <span :class="getExpiryStatusColor()" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getExpiryStatusText() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Days Until Expiry:</span>
                                        <span class="font-medium">{{ daysUntilExpiry(stock) || 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <Link :href="route('stocks.edit', stock.id)"
                                          class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Edit Stock
                                    </Link>
                                    <button @click="deleteStock(stock.id)"
                                            class="block w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Delete Stock
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock History</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Created:</span>
                                        <span class="font-medium">{{ formatDate(stock.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Updated:</span>
                                        <span class="font-medium">{{ formatDate(stock.updated_at) }}</span>
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
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    stock: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const isExpired = (stock) => {
    if (!stock.expiry_date) return false
    return new Date(stock.expiry_date) < new Date()
}

const isExpiringSoon = (stock) => {
    if (!stock.expiry_date) return false
    const expiryDate = new Date(stock.expiry_date)
    const now = new Date()
    const thirtyDaysFromNow = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000))
    return expiryDate >= now && expiryDate <= thirtyDaysFromNow
}

const isLowStock = (stock) => {
    return stock.quantity <= stock.min_stock_level
}

const daysUntilExpiry = (stock) => {
    if (!stock.expiry_date) return null
    const expiryDate = new Date(stock.expiry_date)
    const now = new Date()
    const diffTime = expiryDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays
}

const getStockStatusColor = () => {
    if (isLowStock(props.stock)) return 'bg-red-100 text-red-800'
    return 'bg-green-100 text-green-800'
}

const getStockStatusText = () => {
    if (isLowStock(props.stock)) return 'Low Stock'
    return 'Good Stock'
}

const getExpiryStatusColor = () => {
    if (isExpired(props.stock)) return 'bg-red-100 text-red-800'
    if (isExpiringSoon(props.stock)) return 'bg-orange-100 text-orange-800'
    return 'bg-green-100 text-green-800'
}

const getExpiryStatusText = () => {
    if (isExpired(props.stock)) return 'Expired'
    if (isExpiringSoon(props.stock)) return 'Expiring Soon'
    return 'Good'
}

const deleteStock = (id) => {
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
            router.delete(route('stocks.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Stock entry has been deleted.',
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
