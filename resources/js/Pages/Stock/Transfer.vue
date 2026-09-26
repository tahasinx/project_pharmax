<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Branch transfer</h2>
                <p class="text-sm text-gray-500">Request, dispatch, then receive. The batch number stays the same.</p>
            </div>
        </template>
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
            <form class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2" @submit.prevent="save">
                <select v-model="form.stock_id" class="rounded-lg border-gray-200 text-sm" required>
                    <option value="">Batch</option>
                    <option v-for="stock in stocks" :key="stock.id" :value="stock.id">{{ stock.medicine?.name }} · {{ stock.batch_number }} · {{ stock.quantity }}</option>
                </select>
                <select v-model="form.to_branch_id" class="rounded-lg border-gray-200 text-sm" required>
                    <option value="">Destination</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <input v-model.number="form.quantity" type="number" min="1" class="rounded-lg border-gray-200 text-sm" required>
                <button class="rounded-lg bg-emerald-700 text-sm font-medium text-white">Request transfer</button>
            </form>
            <form class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2" @submit.prevent="adjust">
                <select v-model="adj.stock_id" class="rounded-lg border-gray-200 text-sm" required>
                    <option value="">Batch to adjust</option>
                    <option v-for="stock in stocks" :key="'a'+stock.id" :value="stock.id">{{ stock.medicine?.name }} · {{ stock.batch_number }}</option>
                </select>
                <select v-model="adj.type" class="rounded-lg border-gray-200 text-sm">
                    <option value="opening">Opening</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="damage">Damage</option>
                    <option value="write_off">Write-off</option>
                </select>
                <input v-model.number="adj.quantity_delta" type="number" class="rounded-lg border-gray-200 text-sm" placeholder="Quantity change, use a minus to reduce" required>
                <input v-model="adj.reason" class="rounded-lg border-gray-200 text-sm" placeholder="Reason" required>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white md:col-span-2 md:w-fit">Save adjustment</button>
            </form>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div v-for="row in transfers" :key="row.id" class="flex items-center justify-between gap-4 border-b px-5 py-4 text-sm last:border-b-0">
                    <div>
                        <div class="font-medium">{{ row.stock?.medicine?.name }} · {{ row.stock?.batch_number }}</div>
                        <div class="text-gray-500">{{ row.quantity }} from {{ row.from_branch?.name }} to {{ row.to_branch?.name }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs uppercase">{{ row.status }}</span>
                        <button v-if="row.status === 'requested'" class="text-emerald-700" @click="router.post(route('stock-transfers.dispatch', row.id))">Dispatch</button>
                        <button v-if="row.status === 'dispatched'" class="text-emerald-700" @click="router.post(route('stock-transfers.receive', row.id))">Receive</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ transfers: Array, branches: Array, stocks: Array })
const form = reactive({ stock_id: '', to_branch_id: '', quantity: 1 })
const adj = reactive({ stock_id: '', quantity_delta: -1, type: 'adjustment', reason: '' })
const save = () => router.post(route('stock-transfers.store'), form)
const adjust = () => router.post(route('stocks.adjust'), adj)
</script>
