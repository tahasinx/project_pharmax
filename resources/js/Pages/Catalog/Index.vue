<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ title }}</h2>
                <p class="text-sm text-gray-500">One {{ kind }} can be shared by many medicines.</p>
            </div>
        </template>
        <div class="mx-auto max-w-3xl space-y-6 px-4 py-8">
            <form class="flex gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" @submit.prevent="save">
                <input v-model="name" class="flex-1 rounded-lg border-gray-200 text-sm" placeholder="Name" required>
                <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Save</button>
            </form>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div v-if="!rows.length" class="p-6 text-sm text-gray-500">Nothing saved yet.</div>
                <div v-for="row in rows" :key="row.id" class="flex items-center justify-between border-b px-5 py-3 last:border-b-0">
                    <span class="font-medium text-gray-900">{{ row.name }}</span>
                    <span class="text-sm text-gray-500">{{ row.medicines_count }} medicines</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({ title: String, kind: String, rows: Array, storeRoute: String })
const name = ref('')
const save = () => router.post(route(props.storeRoute), { name: name.value }, { onSuccess: () => { name.value = '' } })
</script>
