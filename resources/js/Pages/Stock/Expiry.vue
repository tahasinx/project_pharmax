<template>
    <Head title="Expiry" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h4 class="mb-sm-0 font-size-18">Expiry</h4>
                <p class="text-muted font-size-13 mb-0 mt-1">Lock stops a sale. Recall blocks the batch everywhere it still exists.</p>
            </div>
        </template>

        <div class="expiry-page">
            <section v-for="(rows, key) in buckets" :key="key" class="med-panel expiry-section">
                <header class="med-panel-head expiry-section-head">
                    <div>
                        <h6>{{ labels[key] }}</h6>
                        <p>{{ rows.length }} batch{{ rows.length === 1 ? '' : 'es' }}</p>
                    </div>
                </header>

                <p v-if="!rows.length" class="dt-empty text-danger text-center mb-0">No batches in this window</p>

                <ul v-else class="expiry-list">
                    <li v-for="row in rows" :key="row.id" class="expiry-row">
                        <div class="expiry-row-main">
                            <div class="fw-semibold">{{ row.medicine }}</div>
                            <div class="text-muted font-size-12">
                                {{ row.batch }} · {{ row.expiry }} · qty {{ row.quantity }} · {{ row.status }}
                            </div>
                        </div>
                        <div class="dt-actions">
                            <button type="button" class="btn btn-sm btn-outline-warning" @click="router.post(route('stocks.lock', row.id))">
                                Lock
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="router.post(route('stocks.recall', row.id))">
                                Recall
                            </button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({ buckets: Object })

const labels = {
    expired: 'Expired',
    d0_30: '0–30 days',
    d31_60: '31–60 days',
    d61_90: '61–90 days',
    d91_180: '91–180 days',
    d180: 'More than 180 days',
}
</script>

<style scoped>
.expiry-page {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.65rem;
    padding-bottom: 0.45rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.expiry-section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.expiry-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.expiry-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
    font-size: 0.85rem;
}

.expiry-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.expiry-row-main {
    min-width: 0;
}

.dt-empty {
    padding: 1rem 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>
