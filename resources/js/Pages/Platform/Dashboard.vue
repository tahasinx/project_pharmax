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
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#17342b]">{{ settings.name }}</h1>
            <p class="mt-1 text-sm text-[#5c6b63]">{{ settings.tagline }}</p>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div v-for="[label, value] in [
                ['Pharmacies', metrics.pharmacies],
                ['Active', metrics.active],
                ['Locked', metrics.locked],
                ['Provision failed', metrics.failed],
                ['Monthly subscriptions', metrics.mrr],
                ['Unpaid invoices', metrics.unpaid],
            ]" :key="label" class="rounded-xl border border-[#e6e1d6] bg-white px-5 py-4 shadow-sm">
                <p class="text-[11px] font-medium uppercase tracking-[0.14em] text-[#7d8b84]">{{ label }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-[#17342b]">{{ value }}</p>
            </div>
        </div>
    </Layout>
</template>
