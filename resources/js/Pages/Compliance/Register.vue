<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Controlled medicine register</h2></template>
        <div class="py-8 max-w-5xl mx-auto sm:px-6 bg-white rounded shadow divide-y text-sm">
            <div v-for="row in rows" :key="row.id" class="p-3">
                {{ row.dispensed_at }} · {{ row.medicine?.name }} · qty {{ row.quantity }} · {{ row.customer?.name }} · {{ row.user?.name }}
            </div>
            <div v-if="rows.length === 0" class="p-4 text-gray-500">No controlled dispenses recorded.</div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ rows: Array })
</script>
