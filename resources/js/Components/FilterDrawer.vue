<script setup>
import { nextTick, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Filters' },
    applyLabel: { type: String, default: 'Apply filters' },
    clearLabel: { type: String, default: 'Clear all' },
})

const emit = defineEmits(['close', 'apply', 'clear'])

const onKey = (event) => {
    if (event.key === 'Escape' && props.show) emit('close')
}

watch(() => props.show, async (open) => {
    document.body.classList.toggle('filter-drawer-open', open)
    if (open) {
        await nextTick()
        document.querySelector('.filter-drawer-panel .field, .filter-drawer-panel .ss-input, .filter-drawer-panel input')?.focus?.()
    }
})

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => {
    window.removeEventListener('keydown', onKey)
    document.body.classList.remove('filter-drawer-open')
})
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="filter-drawer-root" role="dialog" aria-modal="true" :aria-label="title">
            <div class="filter-drawer-backdrop" @click="emit('close')" />
            <aside class="filter-drawer-panel">
                <header class="filter-drawer-head">
                    <div>
                        <h5 class="mb-0">{{ title }}</h5>
                        <p class="text-muted font-size-12 mb-0 mt-1">Narrow this list</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" aria-label="Close filters" @click="emit('close')">
                        <i class="bi bi-x-lg" />
                    </button>
                </header>
                <form class="filter-drawer-body" @submit.prevent="emit('apply')">
                    <slot />
                    <div class="filter-drawer-foot">
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="emit('clear')">{{ clearLabel }}</button>
                        <button type="submit" class="btn btn-primary btn-sm">{{ applyLabel }}</button>
                    </div>
                </form>
            </aside>
        </div>
    </Teleport>
</template>

<style scoped>
.filter-drawer-root {
    position: fixed;
    inset: 0;
    z-index: 1080;
    display: flex;
    justify-content: flex-end;
}

.filter-drawer-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.42);
}

.filter-drawer-panel {
    position: relative;
    z-index: 1;
    width: min(22.5rem, 100vw);
    height: 100%;
    display: flex;
    flex-direction: column;
    background: var(--shell-panel-bg, #fff);
    border-left: 1px solid var(--shell-panel-border, #e6e8ee);
    box-shadow: -12px 0 40px rgba(15, 23, 42, 0.12);
    animation: filter-drawer-in 180ms ease-out;
}

.filter-drawer-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1.1rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.filter-drawer-head h5 {
    font-size: 1.05rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.filter-drawer-body {
    flex: 1;
    overflow-x: hidden;
    overflow-y: auto;
    padding: 1rem 1.1rem 10rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-drawer-body :deep(.comp-filter),
.filter-drawer-body :deep(.filter-field) {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    position: relative;
}

.filter-drawer-body :deep(.filter-field:has(.ss.is-open)) {
    z-index: 6;
}

.filter-drawer-body :deep(.field-label) {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 650;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

/* Match app standard field / SearchableSelect height */
.filter-drawer-body :deep(.field),
.filter-drawer-body :deep(.form-control),
.filter-drawer-body :deep(.form-select),
.filter-drawer-body :deep(.ss-input) {
    width: 100%;
    box-sizing: border-box;
    height: 2rem;
    min-height: 2rem;
    border: 1px solid var(--shell-panel-border, #ced4da);
    border-radius: var(--pf-radius, 0.35rem);
    background: var(--shell-panel-surface, #fff);
    padding: 0 0.55rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
    color: var(--shell-panel-text, #343747);
}

.filter-drawer-body :deep(.ss-input) {
    padding-right: 1.8rem;
}

.filter-drawer-body :deep(.ss-menu) {
    z-index: 30;
}

.filter-drawer-foot {
    margin-top: auto;
    padding-top: 1rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
}

@keyframes filter-drawer-in {
    from { transform: translateX(100%); opacity: 0.85; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
