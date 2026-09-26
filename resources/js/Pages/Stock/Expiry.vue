<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Expiry</h2>
                <p class="text-sm text-gray-500">Lock stops a sale. Recall blocks the batch everywhere it still exists.</p>
            </div>
        </template>
        <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
            <section v-for="(rows, key) in buckets" :key="key" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <h3 class="border-b bg-gray-50 px-5 py-3 text-sm font-medium text-gray-700">{{ labels[key] }} · {{ rows.length }}</h3>
                <div v-if="!rows.length" class="px-5 py-4 text-sm text-gray-400">None</div>
                <div v-for="row in rows" :key="row.id" class="flex items-center justify-between border-b px-5 py-3 text-sm last:border-b-0">
                    <div>
                        <div class="font-medium">{{ row.medicine }}</div>
                        <div class="text-gray-500">{{ row.batch }} · {{ row.expiry }} · qty {{ row.quantity }} · {{ row.status }}</div>
                    </div>
                    <div class="space-x-3">
                        <button class="text-amber-700" @click="router.post(route('stocks.lock', row.id))">Lock</button>
                        <button class="text-red-700" @click="router.post(route('stocks.recall', row.id))">Recall</button>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ buckets: Object })
const labels = { expired: 'Expired', d0_30: '0–30 days', d31_60: '31–60 days', d61_90: '61–90 days', d91_180: '91–180 days', d180: 'More than 180 days' }
</script>
