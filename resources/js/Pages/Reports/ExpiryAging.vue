<template>
    <Head title="Expiry aging" />
    <ReportShell title="Expiry aging" subtitle="Batches by days to expiry">
        <div class="report-metrics mb-3">
            <div v-for="item in summary" :key="item.key" class="med-metric" :class="{ accent: item.key === 'expired' || item.key === 'd0_30' }">
                <span class="med-metric-label">{{ labels[item.key] }}</span>
                <strong>{{ item.count }} · {{ item.units }} units</strong>
            </div>
        </div>
        <section v-for="(rows, key) in buckets" :key="key" class="med-panel mb-3">
            <header class="med-panel-head">
                <h6>{{ labels[key] }}</h6>
                <p>{{ rows.length }} batch{{ rows.length === 1 ? '' : 'es' }}</p>
            </header>
            <p v-if="!rows.length" class="text-danger text-center mb-0 font-size-13">No batches in this window</p>
            <div v-else class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Medicine</th><th>Batch</th><th>Qty</th><th>Expiry</th><th>Days</th></tr></thead>
                    <tbody>
                        <tr v-for="(row, idx) in rows" :key="idx">
                            <td>{{ row.medicine }}</td>
                            <td>{{ row.batch || '—' }}</td>
                            <td>{{ row.quantity }}</td>
                            <td>{{ row.expiry }}</td>
                            <td :class="{ 'text-danger': row.days < 0 }">{{ row.days }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </ReportShell>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import ReportShell from '@/Components/ReportShell.vue'

defineProps({ summary: Array, buckets: Object, labels: Object })
</script>

<style scoped>
.report-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: 0.65rem; }
.med-metric { border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.45rem); background: #fff; padding: 0.7rem 0.85rem; }
.med-metric.accent { border-color: #f5c2c7; background: #fff1f3; }
.med-metric-label { display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--shell-panel-muted, #74788d); }
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
</style>
