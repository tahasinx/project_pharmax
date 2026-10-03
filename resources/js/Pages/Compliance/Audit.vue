<template>
    <Head title="Audit log" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex flex-column gap-2 min-w-0">
                <h4 class="mb-0 font-size-18">Audit log</h4>
                <FilterSummary
                    :chips="filterChips"
                    @open="showFilters = true"
                    @clear="reset"
                    @remove="removeFilter"
                />
            </div>
        </template>

        <FilterDrawer
            :show="showFilters"
            title="Audit filters"
            @close="showFilters = false"
            @apply="apply"
            @clear="reset"
        >
            <div class="filter-field">
                <label class="field-label">Search</label>
                <input v-model="form.q" type="search" class="field" placeholder="Module, action, or user…">
            </div>
            <div class="filter-field">
                <label class="field-label">Module</label>
                <select v-model="form.module" class="field">
                    <option value="">All modules</option>
                    <option v-for="mod in modules" :key="mod" :value="mod">{{ mod }}</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="field-label">Action</label>
                <input v-model="form.action" type="text" class="field" placeholder="e.g. dispense">
            </div>
        </FilterDrawer>

        <LunaTable title="Activity" empty-text="No audit entries yet.">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Record</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs" :key="log.id">
                        <td>
                            <div class="fw-semibold">{{ formatDate(log.created_at) }}</div>
                            <div class="text-muted font-size-12">{{ formatTime(log.created_at) }}</div>
                        </td>
                        <td>
                            <div>{{ log.user?.name || 'System' }}</div>
                            <div class="text-muted font-size-12">{{ log.user?.email || '' }}</div>
                        </td>
                        <td><span class="mod-chip">{{ log.module }}</span></td>
                        <td class="fw-semibold">{{ log.action }}</td>
                        <td>
                            <div class="text-muted font-size-12">{{ shortType(log.record_type) }}</div>
                            <div>#{{ log.record_id }}</div>
                        </td>
                        <td class="text-muted font-size-12">{{ log.ip || '—' }}</td>
                        <td>
                            <button
                                v-if="hasDetail(log)"
                                type="button"
                                class="btn btn-sm btn-soft-secondary"
                                @click="toggle(log.id)"
                            >
                                {{ openId === log.id ? 'Hide' : 'Detail' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <div v-if="activeLog" class="detail-panel mt-3">
            <header class="detail-head">
                <h6>Change detail</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="openId = null">Close</button>
            </header>
            <div class="detail-grid">
                <div>
                    <div class="field-label">Before</div>
                    <pre class="json-block">{{ pretty(activeLog.old_values) }}</pre>
                </div>
                <div>
                    <div class="field-label">After</div>
                    <pre class="json-block">{{ pretty(activeLog.new_values) }}</pre>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import FilterDrawer from '@/Components/FilterDrawer.vue'
import FilterSummary from '@/Components/FilterSummary.vue'

const props = defineProps({
    logs: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const showFilters = ref(false)
const form = reactive({
    q: props.filters?.q || '',
    module: props.filters?.module || '',
    action: props.filters?.action || '',
})

const openId = ref(null)

const filterChips = computed(() => {
    const chips = []
    if (form.q) chips.push({ key: 'q', label: 'Search', value: form.q })
    if (form.module) chips.push({ key: 'module', label: 'Module', value: form.module })
    if (form.action) chips.push({ key: 'action', label: 'Action', value: form.action })
    return chips
})

const apply = () => {
    showFilters.value = false
    router.get(route('audit.index'), { ...form }, { preserveState: true, replace: true })
}
const reset = () => {
    form.q = ''
    form.module = ''
    form.action = ''
    apply()
}
const removeFilter = (key) => {
    if (key in form) form[key] = ''
    apply()
}

const toggle = (id) => {
    openId.value = openId.value === id ? null : id
}

const activeLog = computed(() => props.logs.find((log) => log.id === openId.value) || null)

const hasDetail = (log) => !!(log.old_values || log.new_values)

const pretty = (value) => {
    if (value == null || value === '') return '—'
    try {
        return JSON.stringify(value, null, 2)
    } catch {
        return String(value)
    }
}

const shortType = (type) => {
    if (!type) return '—'
    const parts = String(type).split('\\')
    return parts[parts.length - 1]
}

const formatDate = (value) => {
    if (!value) return '—'
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value).slice(0, 10)
    return d.toLocaleDateString()
}

const formatTime = (value) => {
    if (!value) return ''
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return ''
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.comp-filters {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr auto;
    gap: 0.55rem;
    align-items: end;
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 0.75rem 0.9rem;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.field {
    width: 100%;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0.32rem 0.55rem;
    font-size: 0.82rem;
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.comp-filter-actions {
    display: flex;
    gap: 0.35rem;
}

.mod-chip {
    display: inline-flex;
    border-radius: var(--pf-radius, 999px);
    padding: 0.12rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    background: #eef0fb;
    color: #5156be;
    text-transform: capitalize;
}

.detail-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 0.85rem 1rem;
}

.detail-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.65rem;
}

.detail-head h6 {
    margin: 0;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.json-block {
    margin: 0;
    min-height: 6rem;
    max-height: 18rem;
    overflow: auto;
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.4rem);
    padding: 0.65rem 0.75rem;
    font-size: 0.72rem;
    line-height: 1.4;
    white-space: pre-wrap;
    word-break: break-word;
}

@media (max-width: 991px) {
    .comp-filters,
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
