<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LunaTable from '@/Components/LunaTable.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
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
        <div class="pf-page-head">
            <div>
                <h1>{{ settings.name || 'Epharma' }} platform</h1>
                <p class="pf-page-sub">{{ settings.tagline || 'Server-level control over pharmacies, schema, backups, and deploy.' }}</p>
            </div>
            <Link href="/platform/companies/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1" />
                New pharmacy
            </Link>
        </div>

        <div class="pf-metric-grid mb-3">
            <div v-for="[label, value, hint] in [
                ['Pharmacies', metrics.pharmacies, 'Companies on this platform'],
                ['Active', metrics.active, 'Provisioned and unlocked'],
                ['Locked', metrics.locked, 'Sign-in blocked'],
                ['Provision failed', metrics.failed, 'Needs a retry'],
                ['Monthly subscriptions', metrics.mrr, settings.default_currency || 'BDT'],
                ['Unpaid invoices', metrics.unpaid, 'Open balance'],
            ]" :key="label" class="pf-metric">
                <span class="pf-metric-label">{{ label }}</span>
                <span class="pf-metric-value">{{ value }}</span>
                <span class="pf-metric-hint">{{ hint }}</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <section class="pf-card h-100">
                    <div class="pf-card-head">
                        <h2>Recent pharmacies</h2>
                        <Link href="/platform/companies" class="btn btn-sm btn-outline-secondary">View all</Link>
                    </div>
                    <div v-if="!pharmacies.length" class="pf-card-body text-center py-5">
                        <p class="mb-1 fw-semibold">No pharmacies yet</p>
                        <p class="pf-page-sub mb-0">The first pharmacy you add will show here with host, database, and provision status.</p>
                    </div>
                    <LunaTable v-else title="Pharmacies">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr><th>Pharmacy</th><th>Host</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="pharmacy in pharmacies" :key="pharmacy.id">
                                    <td>
                                        <Link :href="`/platform/companies/${pharmacy.id}`" class="fw-semibold text-decoration-none">
                                            {{ pharmacy.name }}
                                        </Link>
                                    </td>
                                    <td class="font-monospace small">{{ pharmacy.slug }}.{{ baseDomain }}</td>
                                    <td class="d-flex flex-wrap gap-1">
                                        <StatusBadge :status="pharmacy.status" />
                                        <StatusBadge :status="pharmacy.provision_status" kind="provision" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </LunaTable>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="pf-card h-100">
                    <div class="pf-card-head"><h2>Start here</h2></div>
                    <ol class="list-unstyled mb-0">
                        <li v-for="[title, copy, href] in steps" :key="href" class="border-bottom">
                            <Link :href="href" class="d-block px-3 py-3 text-decoration-none">
                                <span class="d-block fw-semibold text-body">{{ title }}</span>
                                <span class="d-block small text-muted mt-1">{{ copy }}</span>
                            </Link>
                        </li>
                    </ol>
                </section>
            </div>
        </div>
    </Layout>
</template>
