<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex flex-column gap-2 min-w-0">
                <div>
                    <h4 class="mb-0 font-size-18">{{ title }}</h4>
                    <p v-if="subtitle" class="text-muted font-size-13 mb-0 mt-1">{{ subtitle }}</p>
                </div>
                <FilterSummary
                    v-if="$slots.filters"
                    :chips="filterChips"
                    @open="filtersOpen = true"
                    @clear="onClear"
                    @remove="onRemove"
                />
            </div>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <slot name="actions" />
                <Link :href="route('reports.index')" class="btn btn-outline-secondary btn-sm">All reports</Link>
            </div>
        </template>

        <FilterDrawer
            v-if="$slots.filters"
            :show="filtersOpen"
            title="Report filters"
            @close="filtersOpen = false"
            @apply="onApply"
            @clear="onClear"
        >
            <slot name="filters" />
        </FilterDrawer>

        <div class="report-page">
            <div v-if="metrics?.length" class="report-metrics">
                <div v-for="metric in metrics" :key="metric.label" class="med-metric" :class="{ accent: metric.accent }">
                    <span class="med-metric-label">{{ metric.label }}</span>
                    <strong>{{ metric.value }}</strong>
                </div>
            </div>

            <slot />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterDrawer from '@/Components/FilterDrawer.vue'
import FilterSummary from '@/Components/FilterSummary.vue'

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    metrics: { type: Array, default: () => [] },
    filterChips: { type: Array, default: () => [] },
})

const emit = defineEmits(['apply-filters', 'clear-filters', 'remove-filter'])

const filtersOpen = ref(false)

const onApply = () => {
    filtersOpen.value = false
    emit('apply-filters')
}
const onClear = () => {
    filtersOpen.value = false
    emit('clear-filters')
}
const onRemove = (key) => emit('remove-filter', key)
</script>

<style scoped>
.report-page {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.report-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.65rem;
}

.med-metric {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 0.75rem 0.9rem;
}

.med-metric.accent {
    border-color: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.35);
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.06);
}

.med-metric-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 650;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
    margin-bottom: 0.2rem;
}

.med-metric strong {
    font-size: 1.15rem;
    color: var(--shell-panel-text, #343747);
}
</style>
