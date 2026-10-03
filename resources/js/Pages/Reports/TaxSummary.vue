<template>
    <Head title="Tax summary" />
    <ReportShell
        title="Tax summary"
        subtitle="Tax collected by period"
        :metrics="[
            { label: 'Invoices', value: summary.invoices },
            { label: 'Taxable sales', value: money(summary.taxable) },
            { label: 'Tax', value: money(summary.tax), accent: true },
            { label: 'Discount', value: money(summary.discount) },
        ]"
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
        <LunaTable title="Daily tax" empty-text="No tax data">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Date</th><th>Sales</th><th>Tax</th></tr></thead>
                <tbody>
                    <tr v-for="row in daily" :key="row.date">
                        <td>{{ row.date }}</td>
                        <td>{{ money(row.sales) }}</td>
                        <td>{{ money(row.tax) }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </ReportShell>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ReportShell from '@/Components/ReportShell.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

const props = defineProps({ filters: Object, summary: Object, daily: Array, rows: Array })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const apply = () => router.get(route('reports.tax-summary'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.tax-summary', { ...form, export: 1 }))
</script>

<style scoped>
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0.32rem 0.55rem; font-size: 0.82rem; }
</style>
