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
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">{{ settings.name || 'Epharma' }} platform</h4>
                <p class="text-muted mb-0 font-size-13">{{ settings.tagline || 'Pharmacy platform' }}</p>
            </div>
            <div class="page-title-right">
                <Link href="/platform/companies/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1" />
                    New pharmacy
                </Link>
            </div>
        </template>

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

        <section
            v-if="settings.support_email || settings.support_phone || settings.address"
            class="pf-card mb-3"
        >
            <div class="pf-card-head">
                <div>
                    <h2>Support</h2>
                    <p>Shown from platform identity settings.</p>
                </div>
            </div>
            <div class="pf-card-body small d-flex flex-wrap gap-4">
                <div v-if="settings.support_email">
                    <div class="text-muted">Email</div>
                    <a :href="`mailto:${settings.support_email}`">{{ settings.support_email }}</a>
                </div>
                <div v-if="settings.support_phone">
                    <div class="text-muted">Phone</div>
                    <span>{{ settings.support_phone }}</span>
                </div>
                <div v-if="settings.address" class="flex-grow-1" style="min-width: 12rem;">
                    <div class="text-muted">Address</div>
                    <span style="white-space: pre-line;">{{ settings.address }}</span>
                </div>
            </div>
        </section>

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
                                    <td>
                                        <StatusBadge
                                            :status="pharmacy.status"
                                            :provision="pharmacy.provision_status"
                                        />
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
                    <div class="pf-card-body">
                        <div class="d-flex flex-column gap-2">
                            <Link
                                v-for="[title, text, href] in steps"
                                :key="href"
                                :href="href"
                                class="pf-start-link text-decoration-none"
                            >
                                <div class="fw-semibold text-body" style="font-size: 0.86rem;">{{ title }}</div>
                                <div class="small text-muted mb-0">{{ text }}</div>
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </Layout>
</template>
