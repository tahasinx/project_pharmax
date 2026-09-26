<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Prescription queue</h2></template>
        <div class="py-8 max-w-5xl mx-auto sm:px-6 space-y-4">
            <form class="bg-white p-4 rounded shadow space-y-2" @submit.prevent="save">
                <div class="grid md:grid-cols-2 gap-2">
                    <select v-model="form.customer_id" class="border rounded px-3 py-2" required>
                        <option value="">Patient</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <input v-model="form.doctor_name" class="border rounded px-3 py-2" placeholder="Doctor" required>
                </div>
                <div v-for="(item, i) in form.items" :key="i" class="grid md:grid-cols-4 gap-2">
                    <select v-model="item.medicine_id" class="border rounded px-3 py-2" required>
                        <option value="">Medicine</option>
                        <option v-for="m in medicines" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                    <input v-model="item.dose" class="border rounded px-3 py-2" placeholder="Dose">
                    <input v-model="item.frequency" class="border rounded px-3 py-2" placeholder="Frequency">
                    <input v-model.number="item.quantity" type="number" min="1" class="border rounded px-3 py-2">
                </div>
                <label class="text-sm flex gap-2 items-center"><input v-model="form.items[0].substitution_allowed" type="checkbox"> Substitution allowed</label>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Queue</button>
                <a :href="route('clinical-rules.index')" class="text-sm text-blue-700">Clinical rules</a>
            </form>
            <div class="bg-white rounded shadow divide-y">
                <div v-for="rx in prescriptions" :key="rx.id" class="flex justify-between items-center p-4">
                    <a :href="route('prescriptions.show', rx.id)" class="block">
                        <div class="font-medium">{{ rx.customer?.name }} · {{ rx.doctor_name }}</div>
                        <div class="text-sm text-gray-500">{{ rx.status }}</div>
                    </a>
                    <div class="space-x-2 text-sm">
                        <button class="text-blue-600" @click="setStatus(rx, 'preparing')">Preparing</button>
                        <button class="text-green-700" @click="setStatus(rx, 'ready')">Ready</button>
                        <button class="text-red-600" @click="setStatus(rx, 'cancelled')">Cancel</button>
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
defineProps({ prescriptions: Array, customers: Array, medicines: Array })
const form = reactive({
    customer_id: '', doctor_name: '',
    items: [{ medicine_id: '', quantity: 1, dose: '', frequency: '', substitution_allowed: false }],
})
const setStatus = (rx, status) => router.post(route('prescriptions.status', rx.id), { status })
const save = () => router.post(route('prescriptions.store'), form)
</script>
