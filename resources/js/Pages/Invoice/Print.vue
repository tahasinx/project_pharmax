<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Print Header -->
        <div class="bg-white shadow-sm border-b mb-6 p-4 print:hidden">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Invoice Print Preview</h1>
                <div class="flex space-x-2">
                    <button @click="printInvoice"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Print Invoice
                    </button>
                    <Link :href="route('invoices.show', invoice.id)"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Invoice
                    </Link>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="max-w-4xl mx-auto bg-white shadow-lg print:shadow-none print:max-w-none">
            <!-- Invoice Header -->
            <div class="p-8 border-b">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">PharmaCare</h1>
                        <p class="text-gray-600">Modern Pharmacy Management System</p>
                        <div class="mt-4 text-sm text-gray-600">
                            <p>123 Pharmacy Street</p>
                            <p>Medical City, MC 12345</p>
                            <p>Phone: (555) 123-4567</p>
                            <p>Email: info@pharmacare.com</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">INVOICE</h2>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Invoice #:</strong> {{ invoice.invoice_no }}</p>
                            <p><strong>Date:</strong> {{ formatDate(invoice.date) }}</p>
                            <p><strong>Payment:</strong> {{ invoice.payment_type.toUpperCase() }}</p>
                            <p><strong>Status:</strong>
                                <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ invoice.due_amount > 0 ? 'PENDING' : 'PAID' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="p-8 border-b">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Bill To:</h3>
                        <div class="text-gray-700">
                            <p class="font-semibold">{{ invoice.customer?.name || 'Walk-in Customer' }}</p>
                            <p>{{ invoice.customer?.mobile || 'N/A' }}</p>
                            <p>{{ invoice.customer?.email || 'N/A' }}</p>
                            <p>{{ invoice.customer?.address || 'N/A' }}</p>
                            <p>{{ invoice.customer?.city || '' }} {{ invoice.customer?.state || '' }} {{ invoice.customer?.zip || '' }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Invoice Details:</h3>
                        <div class="text-gray-700 space-y-1">
                            <p><strong>Invoice ID:</strong> {{ invoice.invoice_id }}</p>
                            <p><strong>Created By:</strong> {{ invoice.user?.name || 'System' }}</p>
                            <p><strong>Created:</strong> {{ formatDate(invoice.created_at) }}</p>
                            <p v-if="invoice.bank"><strong>Bank:</strong> {{ invoice.bank.name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Invoice Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">#</th>
                                <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Medicine</th>
                                <th class="border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700">Batch ID</th>
                                <th class="border border-gray-300 px-4 py-2 text-center font-semibold text-gray-700">Qty</th>
                                <th class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-700">Rate</th>
                                <th class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-700">Discount</th>
                                <th class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in invoice.items" :key="item.id">
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ index + 1 }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <div>
                                        <div class="font-medium">{{ item.medicine?.name }}</div>
                                        <div class="text-sm text-gray-500">{{ item.medicine?.generic_name }}</div>
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">{{ item.batch_id }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ item.quantity }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">${{ parseFloat(item.rate).toFixed(2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">${{ parseFloat(item.discount).toFixed(2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right font-medium">${{ parseFloat(item.total_amount).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="p-8 border-t">
                <div class="flex justify-end">
                    <div class="w-80">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">${{ (invoice.total_amount - invoice.total_tax).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (10%):</span>
                                <span class="font-medium">${{ parseFloat(invoice.total_tax).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium">-${{ parseFloat(invoice.total_discount).toFixed(2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total Amount:</span>
                                <span>${{ parseFloat(invoice.total_amount).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Paid Amount:</span>
                                <span class="font-medium text-green-600">${{ parseFloat(invoice.paid_amount).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Due Amount:</span>
                                <span :class="invoice.due_amount > 0 ? 'text-red-600' : 'text-green-600'"
                                      class="font-medium">
                                    ${{ parseFloat(invoice.due_amount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="invoice.details" class="p-8 border-t">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Notes</h3>
                <p class="text-gray-700">{{ invoice.details }}</p>
            </div>

            <!-- Footer -->
            <div class="p-8 border-t bg-gray-50">
                <div class="text-center text-gray-600">
                    <p class="mb-2">Thank you for your business!</p>
                    <p class="text-sm">This invoice was generated by PharmaCare Modern Pharmacy Management System</p>
                    <p class="text-sm">Generated on: {{ formatDate(new Date()) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const printInvoice = () => {
    window.print()
}
</script>

<style>
@media print {
    .print\\:hidden {
        display: none !important;
    }
    .print\\:shadow-none {
        box-shadow: none !important;
    }
    .print\\:max-w-none {
        max-width: none !important;
    }
    body {
        margin: 0;
        padding: 0;
    }
    .min-h-screen {
        min-height: auto;
    }
}
</style>
