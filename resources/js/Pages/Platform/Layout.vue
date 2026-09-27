<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});
const user = computed(() => page.props.auth?.user);
const path = computed(() => (page.url || '').split('?')[0]);

const groups = computed(() => {
    const rows = [
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
    ];
    return rows;
});

const links = computed(() => groups.value.flatMap((group) => group.items));
const current = computed(() => links.value.find(([, href]) => isActive(href))?.[0] || 'Platform');

const isActive = (href) => (href === '/platform' ? path.value === '/platform' : path.value === href || path.value.startsWith(`${href}/`));
</script>

<template>
    <div class="min-h-screen bg-[#f6f4ef] text-[#1c2b24]">
        <div class="flex min-h-screen">
            <aside class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col bg-[#17342b] text-[#e7f0eb] md:flex">
                <div class="px-5 pb-4 pt-6">
                    <p class="text-[11px] font-medium uppercase tracking-[0.22em] text-[#9fb5ab]">Epharma</p>
                    <p class="mt-1 text-lg font-semibold tracking-tight text-white">Platform</p>
                </div>
                <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6">
                    <div v-for="group in groups" :key="group.label">
                        <p class="px-3 text-[11px] font-medium uppercase tracking-[0.16em] text-[#8aa399]">{{ group.label }}</p>
                        <div class="mt-2 space-y-0.5">
                            <Link
                                v-for="[label, href] in group.items"
                                :key="href"
                                :href="href"
                                class="block rounded-lg px-3 py-2 text-sm transition"
                                :class="isActive(href) ? 'bg-white/10 font-medium text-white' : 'text-[#d5e4dc] hover:bg-white/10'"
                            >
                                {{ label }}
                            </Link>
                        </div>
                    </div>
                </nav>
                <div class="border-t border-white/10 px-3 py-4">
                    <Link href="/logout" method="post" as="button" class="w-full rounded-lg px-3 py-2 text-left text-sm text-[#d5e4dc] hover:bg-white/10">Sign out</Link>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-[#e6e1d6] bg-white/90 px-4 backdrop-blur sm:px-6">
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-[#7d8b84]">Platform</p>
                        <p class="text-sm font-semibold text-[#17342b]">{{ current }}</p>
                    </div>
                    <p class="truncate text-sm text-[#5c6b63]">{{ user?.name }}</p>
                </header>

                <nav class="flex gap-2 overflow-x-auto border-b border-[#e6e1d6] bg-white px-4 py-2 md:hidden">
                    <Link
                        v-for="[label, href] in links"
                        :key="href"
                        :href="href"
                        class="shrink-0 rounded-full px-3 py-1 text-sm"
                        :class="isActive(href) ? 'bg-[#17342b] text-white' : 'bg-[#efeae1] text-[#1c2b24]'"
                    >
                        {{ label }}
                    </Link>
                </nav>

                <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <p v-for="(message, key) in errors" :key="key" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ message }}</p>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
