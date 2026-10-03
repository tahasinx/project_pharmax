<template>
    <Head title="Fast / slow movers" />
    <ReportShell title="Fast / slow movers" subtitle="Top and bottom sellers by quantity"
        :filter-chips="filterChips"
        @apply-filters="apply"
        @clear-filters="reset"
        @remove-filter="removeFilter"
    >
        <template #actions>
            <a :href="exportUrl" class="btn btn-outline-primary btn-sm">Export CSV</a>
        </template>
        <template #filters>
            <div class="filter-field">
                <label class="field-label">From</label>
                <input v-model="form.from" type="date" class="field">
            </div>
            <div class="filter-field">
                <label class="field-label">To</label>
                <input v-model="form.to" type="date" class="field">
            </div>
        </template>
        <div class="row g-3">
            <div class="col-lg-6">
                <LunaTable title="Fast movers" empty-text="No sales in this range">
                    <table class="table table-striped table-hover mb-0 w-100">
                        <thead><tr><th>Medicine</th><th>Qty</th><th>Sales</th></tr></thead>
                        <tbody>
                            <tr v-for="row in fast" :key="'f'+row.medicine">
                                <td>{{ row.medicine }}</td>
                                <td>{{ row.quantity }}</td>
                                <td>{{ money(row.sales) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </LunaTable>
            </div>
            <div class="col-lg-6">
                <LunaTable title="Slow movers" empty-text="No sales in this range">
                    <table class="table table-striped table-hover mb-0 w-100">
                        <thead><tr><th>Medicine</th><th>Qty</th><th>Sales</th></tr></thead>
                        <tbody>
                            <tr v-for="row in slow" :key="'s'+row.medicine">
                                <td>{{ row.medicine }}</td>
                                <td>{{ row.quantity }}</td>
                                <td>{{ money(row.sales) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </LunaTable>
            </div>
        </div>
    </ReportShell>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ReportShell from '@/Components/ReportShell.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

const props = defineProps({ filters: Object, fast: Array, slow: Array })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const apply = () => router.get(route('reports.movers'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.movers', { ...form, export: 1 }))
</script>

<style scoped>
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0.32rem 0.55rem; font-size: 0.82rem; }
</style>
