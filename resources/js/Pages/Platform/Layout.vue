<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import LunaShell from '@/Layouts/LunaShell.vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});
const path = computed(() => (page.url || '').split('?')[0]);

const groups = computed(() => [
    {
        label: 'Operate',
        items: [
            ['Overview', '/platform'],
            ['Pharmacies', '/platform/companies'],
        ],
    },
    {
        label: 'Billing',
        items: [
            ['Plans', '/platform/plans'],
            ['Subscriptions', '/platform/subscriptions'],
            ['Invoices', '/platform/invoices'],
            ['Billing', '/platform/billing'],
        ],
    },
    {
        label: 'System',
        items: [
            ['Schema', '/platform/schema'],
            ['Backups', '/platform/backups'],
            ['Commands', '/platform/commands'],
            ...(page.props.platform?.deploy ? [['Deploy', '/platform/deploy']] : []),
            ['Settings', '/platform/settings'],
        ],
    },
].map((group) => ({
    label: group.label,
    items: group.items.map(([name, href]) => ({
        key: href,
        name,
        href,
        active: href === '/platform' ? path.value === '/platform' : path.value === href || path.value.startsWith(`${href}/`),
    })),
})));
</script>

<template>
    <LunaShell :groups="groups" meta="Platform" home-href="/platform">
        <template v-if="$slots.header" #header>
            <slot name="header" />
        </template>
        <p
            v-for="(message, key) in errors"
            :key="key"
            class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700"
        >
            {{ message }}
        </p>
        <slot />
    </LunaShell>
</template>
