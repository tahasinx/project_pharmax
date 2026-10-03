<template>
    <Head title="Prescription queue" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Prescription queue</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('clinical-rules.index')" class="btn btn-soft-secondary btn-sm">Clinical rules</Link>
            </div>
        </template>

        <form class="med-form mb-3" @submit.prevent="save">
            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Queue prescription</h6>
                    <p>Add patient, doctor, and line items</p>
                </header>
                <div class="med-row med-row-2">
                    <div class="med-field">
                        <label class="field-label">Patient <span class="req">*</span></label>
                        <select v-model="form.customer_id" class="field" required>
                            <option value="">Select patient</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Doctor <span class="req">*</span></label>
                        <input v-model="form.doctor_name" type="text" class="field" placeholder="Doctor name" required>
                    </div>
                </div>
                <div v-for="(item, i) in form.items" :key="i" class="line-row mt-2">
                    <div class="med-field flex-grow-1">
                        <label v-if="i === 0" class="field-label">Medicine</label>
                        <select v-model="item.medicine_id" class="field" required>
                            <option value="">Medicine</option>
                            <option v-for="m in medicines" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                    <div class="med-field line-sm">
                        <label v-if="i === 0" class="field-label">Dose</label>
                        <input v-model="item.dose" type="text" class="field" placeholder="Dose">
                    </div>
                    <div class="med-field line-sm">
                        <label v-if="i === 0" class="field-label">Frequency</label>
                        <input v-model="item.frequency" type="text" class="field" placeholder="Frequency">
                    </div>
                    <div class="med-field line-sm">
                        <label v-if="i === 0" class="field-label">Qty</label>
                        <input v-model.number="item.quantity" type="number" min="1" class="field">
                    </div>
                </div>
                <label class="check-row font-size-13 mt-2">
                    <input v-model="form.items[0].substitution_allowed" type="checkbox">
                    Substitution allowed
                </label>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">Queue prescription</button>
                </div>
            </section>
        </form>

        <LunaTable title="Queue" empty-text="No prescriptions in queue">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rx in prescriptions" :key="rx.id">
                        <td>
                            <Link :href="route('prescriptions.show', rx.id)" class="fw-semibold text-body">
                                {{ rx.customer?.name }}
                            </Link>
                        </td>
                        <td>{{ rx.doctor_name }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': rx.status === 'ready' || rx.status === 'preparing' }">
                                <span class="status-dot" />
                                {{ rx.status }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('prescriptions.show', rx.id)" class="btn btn-sm btn-icon btn-soft-primary" title="Dispense">
                                    <i class="bi bi-eye"></i>
                                </Link>
                                <button type="button" class="btn btn-sm btn-soft-primary" @click="setStatus(rx, 'preparing')">Preparing</button>
                                <button type="button" class="btn btn-sm btn-soft-success" @click="setStatus(rx, 'ready')">Ready</button>
                                <button type="button" class="btn btn-sm btn-soft-danger" @click="setStatus(rx, 'cancelled')">Cancel</button>
                            </div>
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

defineProps({ prescriptions: Array, customers: Array, medicines: Array })

const form = reactive({
    customer_id: '',
    doctor_name: '',
    items: [{ medicine_id: '', quantity: 1, dose: '', frequency: '', substitution_allowed: false }],
})

const setStatus = (rx, status) => router.post(route('prescriptions.status', rx.id), { status })
const save = () => router.post(route('prescriptions.store'), form)
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.65rem;
    padding-bottom: 0.45rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-field {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
}

.med-row-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.line-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    align-items: flex-end;
}

.line-sm {
    width: 6.5rem;
    flex-shrink: 0;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.req {
    color: #f46a6a;
}

.field {
    width: 100%;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0.32rem 0.55rem;
    font-size: 0.82rem;
    line-height: 1.3;
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.check-row {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--shell-panel-muted, #74788d);
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: var(--pf-radius, 999px);
    padding: 0.22rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
    text-transform: capitalize;
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 999px;
    background: var(--shell-panel-muted, #adb5bd);
}

.status-chip.is-on .status-dot {
    background: #34c38f;
}

@media (max-width: 991.98px) {
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
