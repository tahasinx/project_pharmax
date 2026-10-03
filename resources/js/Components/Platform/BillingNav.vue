<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const path = computed(() => (page.url || '').split('?')[0]);

const tabs = computed(() => [
    { href: '/platform/plans', label: 'Plans', icon: 'bi-tags' },
    { href: '/platform/subscriptions', label: 'Subscriptions', icon: 'bi-arrow-repeat' },
    { href: '/platform/invoices', label: 'Invoices', icon: 'bi-receipt' },
    { href: '/platform/billing', label: 'Overview', icon: 'bi-graph-up' },
].map((tab) => ({
    ...tab,
    active: path.value === tab.href || path.value.startsWith(`${tab.href}/`),
})));
</script>

<template>
    <nav class="pf-ops-nav" aria-label="Billing">
        <Link
            v-for="tab in tabs"
            :key="tab.href"
            :href="tab.href"
            class="pf-ops-tab"
            :class="{ active: tab.active }"
        >
            <i :class="['bi', tab.icon]" />
            <span>{{ tab.label }}</span>
        </Link>
    </nav>
</template>
