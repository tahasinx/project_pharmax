<template>
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Branches</h2>
                <p class="text-sm text-gray-500">Each branch gets a warehouse and a counter.</p>
            </div>
        </template>
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
            <form class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3" @submit.prevent="save">
                <input v-model="form.name" class="rounded-lg border-gray-200 text-sm" placeholder="Branch name" required>
                <input v-model="form.code" class="rounded-lg border-gray-200 text-sm" placeholder="Code">
                <button class="rounded-lg bg-emerald-700 text-sm font-medium text-white">Add branch</button>
            </form>
            <article v-for="branch in branches" :key="branch.id" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2">
                    <h3 class="font-medium text-gray-900">{{ branch.name }}</h3>
                    <span v-if="branch.is_head_office" class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-800">Head office</span>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    {{ (branch.warehouses || []).map(row => row.name).join(', ') || 'No warehouse' }}
                    · {{ (branch.counters || []).map(row => row.name).join(', ') || 'No counter' }}
                </p>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ branches: Array })
const form = reactive({ name: '', code: '' })
const save = () => router.post(route('branches.store'), form, { onSuccess: () => { form.name = ''; form.code = '' } })
</script>
