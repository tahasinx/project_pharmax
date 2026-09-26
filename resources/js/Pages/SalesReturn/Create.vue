<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Sales return</h2>
                <p class="text-sm text-gray-500">The original batch stays quarantined unless you choose to restock it.</p>
            </div>
        </template>
        <div class="mx-auto max-w-3xl px-4 py-8">
            <form class="space-y-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm" @submit.prevent="save">
                <select v-model="invoiceId" class="w-full rounded-lg border-gray-200 text-sm" required>
                    <option value="">Invoice</option>
                    <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.id">{{ invoice.invoice_no }} · {{ invoice.customer?.name }}</option>
                </select>
                <select v-model="form.invoice_item_id" class="w-full rounded-lg border-gray-200 text-sm" required>
                    <option value="">Line</option>
                    <option v-for="item in lines" :key="item.id" :value="item.id">{{ item.medicine?.name }} · sold {{ item.quantity }}</option>
                </select>
                <input v-model.number="form.quantity" type="number" min="1" class="w-full rounded-lg border-gray-200 text-sm" required>
                <select v-model="form.condition" class="w-full rounded-lg border-gray-200 text-sm">
                    <option value="resalable">Looks resalable</option>
                    <option value="damaged">Damaged</option>
                    <option value="expired">Expired</option>
                    <option value="opened">Opened</option>
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-700"><input v-model="form.restock" type="checkbox"> Put it back into available stock</label>
                <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Record return</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { computed, reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({ invoices: Array })
const invoiceId = ref('')
const lines = computed(() => props.invoices.find(invoice => invoice.id === invoiceId.value)?.items || [])
const form = reactive({ invoice_item_id: '', quantity: 1, condition: 'resalable', restock: false })
const save = () => router.post(route('sales-returns.store'), {
    ...form,
    invoice_id: invoiceId.value,
    return_date: new Date().toISOString().slice(0, 10),
})
</script>
