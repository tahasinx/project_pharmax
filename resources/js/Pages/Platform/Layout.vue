<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});
const items = computed(() => {
    const rows = [
        ['Overview', '/platform'],
        ['Pharmacies', '/platform/companies'],
        ['Plans', '/platform/plans'],
        ['Subscriptions', '/platform/subscriptions'],
        ['Invoices', '/platform/invoices'],
        ['Billing', '/platform/billing'],
        ['Schema', '/platform/schema'],
        ['Backups', '/platform/backups'],
        ['Commands', '/platform/commands'],
        ['Settings', '/platform/settings'],
    ];
    if (page.props.platform?.deploy) {
        rows.push(['Deploy', '/platform/deploy']);
    }
    return rows;
});
</script>

<template>
    <div class="min-h-screen bg-[#f4f1ea] text-[#1c2b24]">
        <div class="flex min-h-screen">
            <aside class="hidden w-56 shrink-0 border-r border-[#e4ddd0] bg-[#1f3d32] p-4 text-[#f4f1ea] md:block">
                <p class="px-2 text-xs uppercase tracking-[0.2em] text-[#c7d7cf]">Epharma</p>
                <p class="mt-1 px-2 text-lg font-semibold">Platform</p>
                <nav class="mt-6 space-y-1">
                    <Link
                        v-for="[label, href] in items"
                        :key="href"
                        :href="href"
                        class="block rounded px-2 py-1.5 text-sm hover:bg-white/10"
                    >
                        {{ label }}
                    </Link>
                </nav>
                <Link href="/logout" method="post" as="button" class="mt-8 px-2 text-xs text-[#c7d7cf]">Sign out</Link>
            </aside>
            <main class="min-w-0 flex-1 p-6">
                <div class="mb-4 flex gap-3 overflow-x-auto md:hidden">
                    <Link v-for="[label, href] in items" :key="href" :href="href" class="shrink-0 text-sm underline">{{ label }}</Link>
                </div>
                <p v-for="(message, key) in errors" :key="key" class="mb-2 text-sm text-red-700">{{ message }}</p>
                <slot />
            </main>
        </div>
    </div>
</template>
