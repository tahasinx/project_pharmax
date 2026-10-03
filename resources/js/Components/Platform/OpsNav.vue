<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const path = computed(() => (page.url || '').split('?')[0]);
const deployEnabled = computed(() => !!page.props.platform?.deploy);

const tabs = computed(() => {
    const items = [
        { href: '/platform/schema', label: 'Schema sync', icon: 'bi-database-gear' },
        { href: '/platform/backups', label: 'Database backups', icon: 'bi-hdd-stack' },
        { href: '/platform/commands', label: 'Artisan commands', icon: 'bi-terminal' },
    ];
    if (deployEnabled.value) {
        items.push({ href: '/platform/deploy', label: 'Deploy production', icon: 'bi-rocket-takeoff' });
    }
    return items.map((tab) => ({
        ...tab,
        active: path.value === tab.href || path.value.startsWith(`${tab.href}/`),
    }));
});
</script>

<template>
    <nav class="pf-ops-nav" aria-label="Server operations">
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
