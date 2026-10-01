<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, Link } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({
    settings: Object,
    metrics: Object,
    pharmacies: Array,
    baseDomain: String,
});

const steps = [
    ['Create a plan', 'Pricing used when a pharmacy is billed.', '/platform/plans'],
    ['Add a pharmacy', 'Each pharmacy gets its own database and host.', '/platform/companies/create'],
    ['Check schema', 'See which pharmacies are behind the current migrations.', '/platform/schema'],
];
</script>

<template>
    <Head title="Platform" />
    <Layout>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div v-for="[label, value, hint] in [
                ['Pharmacies', metrics.pharmacies, 'Companies on this platform'],
                ['Active', metrics.active, 'Provisioned and unlocked'],
                ['Locked', metrics.locked, 'Sign-in blocked'],
                ['Provision failed', metrics.failed, 'Needs a retry'],
                ['Monthly subscriptions', metrics.mrr, settings.default_currency || 'BDT'],
                ['Unpaid invoices', metrics.unpaid, 'Open balance'],
            ]" :key="label" class="rounded-xl border border-[#e4e4e7] bg-white px-4 py-3.5">
                <div class="flex items-baseline justify-between gap-3">
                    <p class="text-[13px] font-medium text-[#3f3f46]">{{ label }}</p>
                    <p class="text-[12px] text-[#a1a1aa]">{{ hint }}</p>
                </div>
                <p class="mt-2 text-[1.75rem] font-semibold leading-none tracking-tight">{{ value }}</p>
            </div>
        </div>

        <div class="mt-3 grid gap-3 lg:grid-cols-5">
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5 lg:col-span-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1>{{ settings.name || 'Epharma' }}</h1>
                        <p class="mt-1 max-w-md text-sm leading-6 text-[#71717a]">{{ settings.tagline || 'Pharmacy platform' }}</p>
                    </div>
                    <Link href="/platform/companies/create" class="shrink-0 rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">New pharmacy</Link>
                </div>
                <div v-if="!pharmacies.length" class="mt-8 rounded-lg border border-dashed border-[#d4d4d8] bg-[#fafafa] px-4 py-10 text-center">
                    <p class="text-sm font-medium">No pharmacies yet</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-[#71717a]">The first pharmacy you add will show here, with its host, database, and provision status.</p>
                </div>
                <LunaTable v-else title="Pharmacies">
<table class="table table-striped table-hover mt-5">
                    <thead>
                        <tr><th>Pharmacy</th><th>Host</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="pharmacy in pharmacies" :key="pharmacy.id">
                            <td><Link :href="`/platform/companies/${pharmacy.id}`" class="font-medium text-[#17342b]">{{ pharmacy.name }}</Link></td>
                            <td>{{ pharmacy.slug }}.{{ baseDomain }}</td>
                            <td>{{ pharmacy.status }} · {{ pharmacy.provision_status }}</td>
                        </tr>
                    </tbody>
                </table>
</LunaTable>
            </section>
            <section class="rounded-xl border border-[#e4e4e7] bg-white p-5 lg:col-span-2">
                <h2 class="text-sm font-semibold">Start here</h2>
                <ol class="mt-3 divide-y divide-[#f4f4f5]">
                    <li v-for="[title, copy, href] in steps" :key="href">
                        <Link :href="href" class="block py-3">
                            <span class="block text-sm font-medium">{{ title }}</span>
                            <span class="mt-0.5 block text-[13px] leading-5 text-[#71717a]">{{ copy }}</span>
                        </Link>
                    </li>
                </ol>
            </section>
        </div>
    </Layout>
</template>
