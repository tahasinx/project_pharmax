<template>
    <Head title="Clinical rules" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Clinical rules</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('prescriptions.index')" class="btn btn-soft-secondary btn-sm">Prescription queue</Link>
            </div>
        </template>

        <div v-if="notice" class="alert alert-warning py-2 font-size-13 mb-3">{{ notice }}</div>

        <form class="med-form mb-3" @submit.prevent="save">
            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>{{ editingId ? 'Edit rule' : 'New rule' }}</h6>
                    <p>Interaction notes and allergy warnings at dispense</p>
                </header>
                <div class="med-row">
                    <div class="med-field">
                        <label class="field-label">Medicine</label>
                        <select v-model="form.medicine_id" class="field">
                            <option value="">Any / optional</option>
                            <option v-for="m in medicines" :key="m.id" :value="m.id">{{ m.name }}{{ m.generic_name ? ` (${m.generic_name})` : '' }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Second medicine</label>
                        <select v-model="form.other_medicine_id" class="field">
                            <option value="">Optional — for interaction notes</option>
                            <option v-for="m in medicines" :key="'b' + m.id" :value="m.id">{{ m.name }}{{ m.generic_name ? ` (${m.generic_name})` : '' }}</option>
                        </select>
                    </div>
                </div>
                <div class="med-field mb-2">
                    <label class="field-label">Generic reference (local MedEx cache)</label>
                    <select class="field" @change="hintFromGeneric">
                        <option value="">Browse generics to draft a warning…</option>
                        <option v-for="g in generics" :key="g.id" :value="g.name">{{ g.name }}{{ g.segment ? ` · ${g.segment}` : '' }}</option>
                    </select>
                </div>
                <div class="med-row">
                    <div class="med-field">
                        <label class="field-label">Rule type <span class="req">*</span></label>
                        <input v-model="form.rule_type" type="text" class="field" list="rule-types" placeholder="e.g. interaction or allergy" required>
                        <datalist id="rule-types">
                            <option value="interaction" />
                            <option value="allergy" />
                            <option value="pregnancy" />
                            <option value="dose" />
                            <option value="other" />
                        </datalist>
                    </div>
                    <div class="med-field grow">
                        <label class="field-label">Warning text <span class="req">*</span></label>
                        <textarea v-model="form.message" rows="2" class="field" placeholder="Warning text" required />
                    </div>
                </div>
                <div class="med-actions">
                    <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                        {{ editingId ? 'Update rule' : 'Save rule' }}
                    </button>
                    <button v-if="editingId" type="button" class="btn btn-outline-secondary btn-sm" @click="cancelEdit">Cancel</button>
                </div>
            </section>
        </form>

        <LunaTable title="Rules" empty-text="No clinical rules found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Medicines</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rule in rules" :key="rule.id">
                        <td class="fw-semibold">{{ rule.rule_type }}</td>
                        <td>
                            <div>{{ rule.medicine?.name || 'Any' }}</div>
                            <div v-if="rule.other_medicine" class="text-muted font-size-12">× {{ rule.other_medicine.name }}</div>
                        </td>
                        <td>{{ rule.message }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': rule.is_active }">
                                <span class="status-dot" />
                                {{ rule.is_active ? 'Active' : 'Off' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <button type="button" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit" @click="startEdit(rule)">
                                    <i class="bi bi-pencil" />
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-primary" :title="rule.is_active ? 'Deactivate' : 'Activate'" @click="toggle(rule)">
                                    <i class="bi" :class="rule.is_active ? 'bi-pause-circle' : 'bi-play-circle'" />
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="remove(rule)">
                                    <i class="bi bi-trash" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

defineProps({
    rules: { type: Array, default: () => [] },
    medicines: { type: Array, default: () => [] },
    generics: { type: Array, default: () => [] },
    notice: String,
})

const editingId = ref(null)
const form = useForm({
    medicine_id: '',
    other_medicine_id: '',
    rule_type: '',
    message: '',
    is_active: true,
})

const hintFromGeneric = (event) => {
    const name = event.target?.value
    if (!name) return
    if (!form.rule_type) form.rule_type = 'interaction'
    if (!form.message) {
        form.message = `Check interactions and counseling notes for ${name} before dispense.`
    }
    event.target.value = ''
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
    form.is_active = true
    editingId.value = null
}

const cancelEdit = () => resetForm()

const startEdit = (rule) => {
    editingId.value = rule.id
    form.medicine_id = rule.medicine?.id || ''
    form.other_medicine_id = rule.other_medicine?.id || ''
    form.rule_type = rule.rule_type || ''
    form.message = rule.message || ''
    form.is_active = !!rule.is_active
}

const save = () => {
    if (editingId.value) {
        form.put(route('clinical-rules.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        })
        return
    }
    form.post(route('clinical-rules.store'), {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    })
}

const toggle = (rule) => {
    router.post(route('clinical-rules.toggle', rule.id), {}, { preserveScroll: true })
}

const remove = (rule) => {
    destroyRecord('clinical-rules.destroy', rule.id, 'Delete this clinical rule?', 'The rule has been deleted.')
}
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

.med-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    margin-bottom: 0.55rem;
}

.med-field.grow {
    grid-column: span 1;
}

.med-field {
    margin-bottom: 0;
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

.med-actions {
    display: flex;
    gap: 0.4rem;
    margin-top: 0.35rem;
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

@media (max-width: 767px) {
    .med-row {
        grid-template-columns: 1fr;
    }
}
</style>
