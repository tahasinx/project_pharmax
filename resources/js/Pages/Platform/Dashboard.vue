<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({
    settings: Object,
    metrics: Object,
});
</script>

<template>
    <Head title="Platform" />
    <Layout>
        <h1 class="text-2xl font-semibold">{{ settings.name }}</h1>
        <p class="mt-1 text-sm text-[#5c6b63]">{{ settings.tagline }}</p>
        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div v-for="[label, value] in [
                ['Pharmacies', metrics.pharmacies],
                ['Active', metrics.active],
                ['Locked', metrics.locked],
                ['Provision failed', metrics.failed],
                ['Monthly subscriptions', metrics.mrr],
                ['Unpaid invoices', metrics.unpaid],
            ]" :key="label" class="rounded border border-[#e4ddd0] bg-white p-4">
                <p class="text-xs uppercase tracking-wide text-[#5c6b63]">{{ label }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ value }}</p>
            </div>
        </div>
    </Layout>
</template>
