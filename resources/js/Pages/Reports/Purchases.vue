<template>
    <Head title="Purchases report" />
    <ReportShell
        title="Purchases"
        subtitle="Supplier spend, tax and discounts"
        :metrics="[
            { label: 'Orders', value: summary.count },
            { label: 'Total', value: money(summary.total), accent: true },
            { label: 'Tax', value: money(summary.tax) },
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
        <LunaTable title="By manufacturer" empty-text="No purchases in this range">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead><tr><th>Manufacturer</th><th>Orders</th><th>Total</th></tr></thead>
                <tbody>
                    <tr v-for="row in bySupplier" :key="row.name">
                        <td>{{ row.name }}</td>
                        <td>{{ row.count }}</td>
                        <td>{{ money(row.total) }}</td>
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

const props = defineProps({ filters: Object, summary: Object, bySupplier: Array })
const { money } = useMoney()
const form = reactive({ from: props.filters?.from || '', to: props.filters?.to || '' })
const filterChips = computed(() => {
    const chips = []
    if (form.from) chips.push({ key: 'from', label: 'From', value: form.from })
    if (form.to) chips.push({ key: 'to', label: 'To', value: form.to })
    return chips
})
const apply = () => router.get(route('reports.purchases'), { ...form }, { preserveState: true, replace: true })
const reset = () => { form.from = ''; form.to = ''; apply() }
const removeFilter = (key) => { if (key in form) form[key] = ''; apply() }
const exportUrl = computed(() => route('reports.purchases', { ...form, export: 1 }))
</script>

<style scoped>
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; color: var(--shell-panel-muted, #495057); }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); background: #fff; padding: 0.32rem 0.55rem; font-size: 0.82rem; }
</style>
