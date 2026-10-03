<template>
    <Head title="Customer dues" />
    <ReportShell
        title="Customer dues"
        subtitle="Outstanding receivables"
        :metrics="[
            { label: 'Customers', value: byCustomer.length },
            { label: 'Total due', value: money(totalDue), accent: true },
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
        <LunaTable title="By customer" empty-text="No outstanding dues">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Customer</th><th>Invoices</th><th>Due</th></tr></thead>
                <tbody>
                    <tr v-for="row in byCustomer" :key="row.name">
                        <td>{{ row.name }}</td>
                        <td>{{ row.invoices }}</td>
                        <td class="text-danger">{{ money(row.due) }}</td>
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

const props = defineProps({ filters: Object, byCustomer: Array })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const totalDue = computed(() => (props.byCustomer || []).reduce((s, r) => s + Number(r.due || 0), 0))
const apply = () => router.get(route('reports.customer-dues'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.customer-dues', { ...form, export: 1 }))
</script>

<style scoped>
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0.32rem 0.55rem; font-size: 0.82rem; }
</style>
