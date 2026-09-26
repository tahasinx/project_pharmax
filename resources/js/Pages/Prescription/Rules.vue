<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Clinical rules</h2></template>
        <div class="py-8 max-w-3xl mx-auto sm:px-6 space-y-4">
            <p class="text-sm bg-amber-50 border border-amber-200 rounded p-3">{{ notice }}</p>
            <form class="bg-white p-4 rounded shadow space-y-2" @submit.prevent="save">
                <select v-model="form.medicine_id" class="w-full border rounded px-3 py-2">
                    <option value="">Medicine</option>
                    <option v-for="m in medicines" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
                <select v-model="form.other_medicine_id" class="w-full border rounded px-3 py-2">
                    <option value="">Second medicine, if this is an interaction note</option>
                    <option v-for="m in medicines" :key="'b'+m.id" :value="m.id">{{ m.name }}</option>
                </select>
                <input v-model="form.rule_type" class="w-full border rounded px-3 py-2" placeholder="Type, for example interaction or allergy" required>
                <textarea v-model="form.message" class="w-full border rounded px-3 py-2" placeholder="Warning text" required></textarea>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Save rule</button>
            </form>
            <div class="bg-white rounded shadow divide-y text-sm">
                <div v-for="rule in rules" :key="rule.id" class="p-3">
                    <div class="font-medium">{{ rule.rule_type }}</div>
                    <div>{{ rule.message }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ rules: Array, medicines: Array, notice: String })
const form = reactive({ medicine_id: '', other_medicine_id: '', rule_type: '', message: '' })
const save = () => router.post(route('clinical-rules.store'), form)
</script>
