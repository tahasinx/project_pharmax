<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Dispense</h2></template>
        <div class="py-8 max-w-4xl mx-auto sm:px-6 space-y-4">
            <p class="text-sm bg-amber-50 border border-amber-200 rounded p-3">{{ notice }}</p>
            <div class="text-sm text-gray-600">{{ prescription.customer?.name }} · {{ prescription.doctor_name }} · {{ prescription.status }}</div>
            <form v-for="line in lines" :key="line.id" class="bg-white p-4 rounded shadow space-y-2" @submit.prevent="dispense(line)">
                <div class="font-medium">{{ line.name }} · remaining {{ line.quantity - line.dispensed_quantity }}</div>
                <div class="text-sm text-gray-500">{{ line.dose }} {{ line.frequency }} {{ line.duration }}</div>
                <div v-if="line.warnings.length" class="text-sm text-red-700">{{ line.warnings.join(' ') }}</div>
                <div v-if="line.alternatives.length" class="text-sm">
                    Same generic:
                    <select v-model="subs[line.id]" class="border rounded ml-2">
                        <option value="">Keep prescribed medicine</option>
                        <option v-for="alt in line.alternatives" :key="alt.id" :value="alt.id">{{ alt.name }} {{ alt.strength }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <input v-model.number="qty[line.id]" type="number" min="1" class="border rounded px-2 py-1 w-24">
                    <button class="bg-blue-600 text-white px-3 rounded" :disabled="line.dispensed_quantity >= line.quantity">Dispense</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({ prescription: Object, lines: Array, notice: String })
const qty = reactive({})
const subs = reactive({})
props.lines.forEach(line => { qty[line.id] = Math.max(line.quantity - line.dispensed_quantity, 1); subs[line.id] = '' })
const dispense = (line) => router.post(route('prescriptions.dispense', props.prescription.id), {
    prescription_item_id: line.id,
    quantity: qty[line.id],
    substitute_medicine_id: subs[line.id] || null,
})
</script>
