<template>
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl">Audit log</h2></template>
        <div class="py-8 max-w-5xl mx-auto sm:px-6 bg-white rounded shadow divide-y text-sm">
            <div v-for="log in logs" :key="log.id" class="p-3">
                <div>{{ log.created_at }} · {{ log.user?.name || 'System' }} · {{ log.module }} · {{ log.action }}</div>
                <div class="text-gray-500">{{ log.record_type }} #{{ log.record_id }} · {{ log.ip }}</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineProps({ logs: Array })
</script>
