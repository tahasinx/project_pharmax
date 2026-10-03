<template>
    <Head :title="run.period_label" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h4 class="mb-sm-0 font-size-18">{{ run.period_label }}</h4>
                <p class="text-muted font-size-13 mb-0 mt-1">{{ run.run_number }} · {{ run.status }}</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <Link :href="route('hrm.payroll.index')" class="btn btn-outline-secondary btn-sm">Back</Link>
                <button v-if="run.status === 'draft'" type="button" class="btn btn-primary btn-sm" @click="approve">Approve</button>
                <button v-if="run.status === 'approved'" type="button" class="btn btn-success btn-sm" @click="markPaid">Mark paid</button>
            </div>
        </template>

        <div class="report-metrics mb-3">
            <div class="med-metric"><span class="med-metric-label">Gross</span><strong>{{ money(run.total_gross) }}</strong></div>
            <div class="med-metric"><span class="med-metric-label">Deductions</span><strong>{{ money(run.total_deductions) }}</strong></div>
            <div class="med-metric accent"><span class="med-metric-label">Net</span><strong>{{ money(run.total_net) }}</strong></div>
        </div>

        <LunaTable title="Payslips" empty-text="No payslips">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Base</th>
                        <th>Bonus</th>
                        <th>Deductions</th>
                        <th>Net</th>
                        <th>Days</th>
                        <th v-if="run.status === 'draft'">Save</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="slip in run.payslips" :key="slip.id">
                        <td>
                            <div class="fw-semibold">{{ slip.employee?.full_name }}</div>
                            <div class="text-muted font-size-12">{{ slip.employee?.department?.name || '—' }}</div>
                        </td>
                        <td>
                            <input v-if="run.status === 'draft'" v-model.number="draft[slip.id].base_salary" type="number" min="0" step="0.01" class="field field-sm">
                            <span v-else>{{ money(slip.base_salary) }}</span>
                        </td>
                        <td>
                            <input v-if="run.status === 'draft'" v-model.number="draft[slip.id].bonus" type="number" min="0" step="0.01" class="field field-sm">
                            <span v-else>{{ money(slip.bonus) }}</span>
                        </td>
                        <td>
                            <input v-if="run.status === 'draft'" v-model.number="draft[slip.id].deductions" type="number" min="0" step="0.01" class="field field-sm">
                            <span v-else>{{ money(slip.deductions) }}</span>
                        </td>
                        <td>{{ money(run.status === 'draft' ? netOf(slip.id) : slip.net_pay) }}</td>
                        <td>
                            <input v-if="run.status === 'draft'" v-model.number="draft[slip.id].days_worked" type="number" min="0" max="31" class="field field-sm">
                            <span v-else>{{ slip.days_worked }}</span>
                        </td>
                        <td v-if="run.status === 'draft'">
                            <button type="button" class="btn btn-sm btn-soft-primary" @click="saveSlip(slip)">Save</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

const props = defineProps({ run: Object })
const { money } = useMoney()

const draft = reactive({})
for (const slip of props.run.payslips || []) {
    draft[slip.id] = {
        base_salary: Number(slip.base_salary),
        bonus: Number(slip.bonus),
        deductions: Number(slip.deductions),
        days_worked: Number(slip.days_worked),
    }
}

const netOf = (id) => {
    const row = draft[id]
    return Math.max(0, Number(row.base_salary || 0) + Number(row.bonus || 0) - Number(row.deductions || 0))
}

const saveSlip = (slip) => {
    router.put(route('hrm.payslips.update', slip.id), draft[slip.id], { preserveScroll: true })
}
const approve = () => router.post(route('hrm.payroll.approve', props.run.id))
const markPaid = () => router.post(route('hrm.payroll.mark-paid', props.run.id))
</script>

<style scoped>
.report-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: 0.65rem; }
.med-metric { border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.45rem); background: #fff; padding: 0.7rem 0.85rem; }
.med-metric.accent { border-color: var(--shell-panel-accent-border, #b7ebd6); background: var(--shell-panel-accent-bg, #e8f8f1); }
.med-metric-label { display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--shell-panel-muted, #74788d); }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0.25rem 0.4rem; font-size: 0.78rem; background: #fff; }
.field-sm { max-width: 7rem; }
</style>
