<script setup>
defineProps({
    /** @type {{ key: string, label: string, value: string }[]} */
    chips: { type: Array, default: () => [] },
    buttonLabel: { type: String, default: 'Filters' },
    showButton: { type: Boolean, default: true },
})

defineEmits(['open', 'clear', 'remove'])
</script>

<template>
    <div v-if="showButton || chips.length" class="filter-summary">
        <button
            v-if="showButton"
            type="button"
            class="btn btn-outline-secondary btn-sm filter-summary-btn"
            @click="$emit('open')"
        >
            <i class="bi bi-funnel me-1" />
            {{ buttonLabel }}
            <span v-if="chips.length" class="badge bg-primary ms-1">{{ chips.length }}</span>
        </button>

        <div v-if="chips.length" class="filter-summary-line">
            <span class="filter-summary-label">Filtered by</span>
            <span
                v-for="chip in chips"
                :key="chip.key"
                class="filter-chip"
                :title="`${chip.label}: ${chip.value}`"
            >
                <span class="filter-chip-text">{{ chip.label }}: {{ chip.value }}</span>
                <button type="button" class="filter-chip-x" :aria-label="`Remove ${chip.label}`" @click="$emit('remove', chip.key)">
                    <i class="bi bi-x" />
                </button>
            </span>
            <button type="button" class="btn btn-link btn-sm px-1 filter-summary-clear" @click="$emit('clear')">
                Clear
            </button>
        </div>
    </div>
</template>

<style scoped>
.filter-summary {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem 0.65rem;
    min-width: 0;
}

.filter-summary-btn {
    flex-shrink: 0;
}

.filter-summary-line {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
    min-width: 0;
}

.filter-summary-label {
    font-size: 0.75rem;
    color: var(--shell-panel-muted, #74788d);
    white-space: nowrap;
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    max-width: 16rem;
    padding: 0.15rem 0.2rem 0.15rem 0.5rem;
    border-radius: 999px;
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.1);
    color: var(--shell-panel-text, #343747);
    font-size: 0.75rem;
    font-weight: 550;
}

.filter-chip-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-chip-x {
    border: 0;
    background: transparent;
    color: inherit;
    line-height: 1;
    padding: 0 0.2rem;
    opacity: 0.7;
}

.filter-chip-x:hover {
    opacity: 1;
}

.filter-summary-clear {
    font-size: 0.75rem;
    text-decoration: none;
}
</style>
