<template>
    <Head title="Payment mix" />
    <ReportShell
        title="Payment mix"
        subtitle="Cash, card, mobile and credit breakdown"
        :metrics="[
            { label: 'Invoices', value: summary.invoices },
            { label: 'Total', value: money(summary.total), accent: true },
            { label: 'Paid', value: money(summary.paid) },
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
        <LunaTable title="By payment type" empty-text="No invoices in this range">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Type</th><th>Count</th><th>Total</th><th>Paid</th></tr></thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.type">
                        <td class="text-uppercase">{{ row.type }}</td>
                        <td>{{ row.count }}</td>
                        <td>{{ money(row.total) }}</td>
                        <td>{{ money(row.paid) }}</td>
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

const props = defineProps({ filters: Object, summary: Object, rows: Array })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const apply = () => router.get(route('reports.payment-mix'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.payment-mix', { ...form, export: 1 }))
</script>

<style scoped>
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0.32rem 0.55rem; font-size: 0.82rem; }
</style>
