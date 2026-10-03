<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h4 class="mb-sm-0 font-size-18">Reports</h4>
                <p class="text-muted font-size-13 mb-0 mt-1">Pharmacy operations and finance insights · {{ hub.period_label }}</p>
            </div>
        </template>

        <div class="report-hub">
            <div class="report-metrics">
                <div class="med-metric accent">
                    <span class="med-metric-label">Sales MTD</span>
                    <strong>{{ money(hub.sales) }}</strong>
                </div>
                <div class="med-metric">
                    <span class="med-metric-label">Purchases MTD</span>
                    <strong>{{ money(hub.purchases) }}</strong>
                </div>
                <div class="med-metric" :class="{ accent: hub.gross_profit >= 0 }">
                    <span class="med-metric-label">Gross (sales − purchases)</span>
                    <strong>{{ money(hub.gross_profit) }}</strong>
                </div>
                <div class="med-metric">
                    <span class="med-metric-label">Open dues</span>
                    <strong>{{ money(hub.dues) }}</strong>
                </div>
                <div class="med-metric">
                    <span class="med-metric-label">Stock at cost</span>
                    <strong>{{ money(hub.stock_value) }}</strong>
                </div>
            </div>

            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Report library</h6>
                    <p>Open a report to filter, chart, and export</p>
                </header>
                <div class="report-grid">
                    <Link
                        v-for="item in links"
                        :key="item.route"
                        :href="route(item.route)"
                        class="report-card"
                    >
                        <div class="fw-semibold">{{ item.title }}</div>
                        <div class="text-muted font-size-12">{{ item.desc }}</div>
                    </Link>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useMoney } from '@/Composables/useMoney'

defineProps({
    hub: Object,
    links: Array,
})

const { money } = useMoney()
</script>

<style scoped>
.report-hub {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.report-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
    gap: 0.65rem;
}

.med-metric {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.45rem);
    background: var(--shell-panel-surface, #fff);
    padding: 0.75rem 0.9rem;
}

.med-metric.accent {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
}

.med-metric-label {
    display: block;
    margin-bottom: 0.15rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-metric strong {
    font-size: 1.05rem;
    color: var(--shell-panel-text, #343747);
}

.med-metric.accent strong {
    color: var(--shell-panel-accent-text, #1e8f68);
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.75rem;
    padding-bottom: 0.45rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.med-panel-head p {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
    gap: 0.65rem;
}

.report-card {
    display: block;
    padding: 0.85rem 0.95rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.45rem);
    background: var(--shell-panel-surface, #fff);
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.report-card:hover {
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.12);
}
</style>
