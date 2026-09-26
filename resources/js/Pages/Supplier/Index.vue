<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Suppliers</h2>
                <p class="text-sm text-gray-500">Credit terms sit on the supplier. Orders and returns count against them.</p>
            </div>
        </template>
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
            <form class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2" @submit.prevent="save">
                <input v-model="form.name" class="rounded-lg border-gray-200 text-sm" placeholder="Name" required>
                <input v-model="form.phone" class="rounded-lg border-gray-200 text-sm" placeholder="Phone">
                <input v-model="form.credit_limit" type="number" step="0.01" class="rounded-lg border-gray-200 text-sm" placeholder="Credit limit">
                <input v-model="form.credit_days" type="number" class="rounded-lg border-gray-200 text-sm" placeholder="Credit days">
                <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white md:col-span-2 md:w-fit">Save supplier</button>
            </form>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div v-for="row in suppliers" :key="row.id" class="flex justify-between border-b px-5 py-4 last:border-b-0">
                    <div>
                        <div class="font-medium text-gray-900">{{ row.name }}</div>
                        <div class="text-sm text-gray-500">{{ row.phone || 'No phone' }} · {{ row.credit_days }} day terms · limit {{ row.credit_limit }}</div>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div>{{ row.purchase_orders_count }} orders</div>
                        <div>{{ row.purchase_returns_count }} returns</div>
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
defineProps({ suppliers: Array })
const form = reactive({ name: '', phone: '', credit_limit: 0, credit_days: 0 })
const save = () => router.post(route('suppliers.store'), form)
</script>
