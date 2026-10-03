<template>
    <Head title="Stock valuation" />
    <ReportShell
        title="Stock valuation"
        subtitle="On-hand value at cost and MRP"
        :metrics="[
            { label: 'Batches', value: summary.batches },
            { label: 'Units', value: summary.units },
            { label: 'Value at cost', value: money(summary.value_cost), accent: true },
            { label: 'Value at MRP', value: money(summary.value_mrp) },
        ]"
    >
        <template #actions>
            <a :href="route('reports.stock-valuation', { export: 1 })" class="btn btn-outline-primary btn-sm">Export CSV</a>
        </template>
        <LunaTable title="Batches" empty-text="No stock on hand">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Medicine</th><th>Batch</th><th>Qty</th><th>Cost</th><th>MRP</th><th>Value cost</th><th>Value MRP</th><th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td>{{ row.medicine }}</td>
                        <td>{{ row.batch || '—' }}</td>
                        <td>{{ row.quantity }}</td>
                        <td>{{ money(row.cost) }}</td>
                        <td>{{ money(row.mrp) }}</td>
                        <td>{{ money(row.value_cost) }}</td>
                        <td>{{ money(row.value_mrp) }}</td>
                        <td>{{ row.expiry || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </ReportShell>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import ReportShell from '@/Components/ReportShell.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

defineProps({ summary: Object, rows: Array })
const { money } = useMoney()
</script>
