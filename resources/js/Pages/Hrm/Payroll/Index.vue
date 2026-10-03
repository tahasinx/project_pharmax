<template>
    <Head title="Payroll" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Payroll</h4>
        </template>

        <section class="med-panel mb-3">
            <header class="med-panel-head">
                <h6>Generate salary run</h6>
                <p>Creates payslips for all active employees with salary</p>
            </header>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-sm" @click="openGenerate">Generate payroll</button>
            </div>
        </section>

        <FormScreen v-if="modalOpen" title="Generate payroll" size="md" @close="closeGenerate">
            <form id="payroll-generate-form" class="med-form" @submit.prevent="generate">
                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Period</h6>
                        <p>One draft run per year/month</p>
                    </header>
                    <div class="med-row med-row-2">
                        <div class="med-field">
                            <label class="field-label">Year <span class="req">*</span></label>
                            <input v-model.number="form.year" type="number" class="field" :class="{ 'has-error': form.errors.year }" required>
                            <p v-if="form.errors.year" class="field-error">{{ form.errors.year }}</p>
                        </div>
                        <div class="med-field">
                            <label class="field-label">Month <span class="req">*</span></label>
                            <select v-model.number="form.month" class="field" :class="{ 'has-error': form.errors.month }" required>
                                <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                            </select>
                            <p v-if="form.errors.month" class="field-error">{{ form.errors.month }}</p>
                        </div>
                    </div>
                </section>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="payroll-generate-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Generating…' : 'Generate' }}
                </button>
            </template>
        </FormScreen>

        <LunaTable title="Payroll runs" empty-text="No payroll runs yet">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Run #</th>
                        <th>Status</th>
                        <th>Gross</th>
                        <th>Net</th>
                        <th>Payslips</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="run in runs" :key="run.id">
                        <td class="fw-semibold">{{ run.period_label }}</td>
                        <td>{{ run.run_number }}</td>
                        <td><span class="status-chip" :class="{ 'is-on': run.status === 'paid' }">{{ run.status }}</span></td>
                        <td>{{ money(run.total_gross) }}</td>
                        <td>{{ money(run.total_net) }}</td>
                        <td>{{ run.payslips_count }}</td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('hrm.payroll.show', run.id)" class="btn btn-sm btn-icon btn-soft-primary" title="Open"><i class="bi bi-eye"></i></Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { useMoney } from '@/Composables/useMoney'

defineProps({ runs: Array })
const { money } = useMoney()
const now = new Date()
const modalOpen = ref(false)
const form = useForm({ year: now.getFullYear(), month: now.getMonth() + 1 })
const openGenerate = () => {
    form.clearErrors()
    modalOpen.value = true
}
const closeGenerate = () => {
    modalOpen.value = false
    form.clearErrors()
}
const generate = () => form.post(route('hrm.payroll.generate'), {
    preserveScroll: true,
    onSuccess: () => closeGenerate(),
})
</script>

<style scoped>
.med-form { display: flex; flex-direction: column; gap: 0.85rem; }
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
.med-row { display: grid; gap: 0.55rem; }
.med-row-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.med-field { margin-bottom: 0; }
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.req { color: #f46a6a; }
.field { width: 100%; height: 2rem; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0 0.65rem; font-size: 0.82rem; background: #fff; }
.field.has-error { border-color: #f46a6a; }
.field-error { margin: 0.2rem 0 0; font-size: 0.7rem; color: #f46a6a; }
.status-chip { display: inline-flex; border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 999px); padding: 0.22rem 0.6rem; font-size: 0.72rem; font-weight: 600; text-transform: capitalize; }
.status-chip.is-on { border-color: var(--shell-panel-accent-border, #b7ebd6); background: var(--shell-panel-accent-bg, #e8f8f1); color: var(--shell-panel-accent-text, #1e8f68); }
</style>
