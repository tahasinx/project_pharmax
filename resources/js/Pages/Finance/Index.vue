<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Finance</h2></template>
        <div class="py-8 max-w-3xl mx-auto sm:px-6">
            <div class="bg-white rounded shadow p-4 mb-4 space-y-3">
                <div>Period profit after cost and sales returns: {{ profit }}</div>
                <form class="grid md:grid-cols-4 gap-2 text-sm" @submit.prevent="receive">
                    <select v-model="receipt.invoice_id" class="border rounded px-2 py-1" required>
                        <option value="">Open invoice</option>
                        <option v-for="inv in openInvoices" :key="inv.id" :value="inv.id">{{ inv.invoice_no }} · due {{ inv.due_amount }}</option>
                    </select>
                    <input v-model.number="receipt.amount" type="number" min="0.01" step="0.01" class="border rounded px-2 py-1" placeholder="Amount" required>
                    <select v-model="receipt.method" class="border rounded px-2 py-1">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                    </select>
                    <button class="bg-blue-600 text-white rounded">Customer receipt</button>
                </form>
                <form class="grid md:grid-cols-4 gap-2 text-sm" @submit.prevent="paySupplier">
                    <select v-model="payment.supplier_id" class="border rounded px-2 py-1" required>
                        <option value="">Supplier</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <input v-model.number="payment.amount" type="number" min="0.01" step="0.01" class="border rounded px-2 py-1" placeholder="Amount" required>
                    <select v-model="payment.method" class="border rounded px-2 py-1">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                    </select>
                    <button class="bg-gray-800 text-white rounded">Pay supplier</button>
                </form>
            </div>
            <LunaTable title="Finance">
<table class="table table-striped table-hover bg-white w-full text-sm rounded shadow">
                <thead><tr class="text-left"><th class="p-2">Code</th><th>Account</th><th>Type</th><th>Balance</th></tr></thead>
                <tbody>
                    <tr v-for="row in accounts" :key="row.code" class="border-t">
                        <td class="p-2">{{ row.code }}</td><td>{{ row.name }}</td><td>{{ row.type }}</td><td>{{ row.balance }}</td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ accounts: Array, profit: Number, openInvoices: { type: Array, default: () => [] }, suppliers: { type: Array, default: () => [] } })
const receipt = reactive({ invoice_id: '', amount: '', method: 'cash' })
const payment = reactive({ supplier_id: '', amount: '', method: 'cash' })
const receive = () => router.post(route('finance.customer-receipt'), receipt)
const paySupplier = () => router.post(route('finance.supplier-payment'), payment)
</script>
