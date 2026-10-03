<template>
    <Head title="Departments" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Departments</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary btn-sm" @click="openCreate">Add department</button>
            </div>
        </template>

        <div v-if="flash.error" class="alert alert-danger py-2">{{ flash.error }}</div>
        <div v-if="flash.success" class="alert alert-success py-2">{{ flash.success }}</div>

        <LunaTable title="Departments" empty-text="No departments found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Employees</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="dept in departments" :key="dept.id">
                        <td class="fw-semibold">{{ dept.name }}</td>
                        <td>{{ dept.code }}</td>
                        <td>{{ dept.employees_count }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': dept.status === 'active' }">{{ dept.status }}</span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <button type="button" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit" @click="openEdit(dept)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="remove(dept)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <FormScreen v-if="modalOpen" :title="modalTitle" size="md" @close="closeModal">
            <form id="department-form" class="med-form" @submit.prevent="save">
                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Department details</h6>
                        <p>Pharmacy / store / counter groupings</p>
                    </header>
                    <div class="med-field mb-2">
                        <label class="field-label">Name <span class="req">*</span></label>
                        <input v-model="form.name" class="field" :class="{ 'has-error': form.errors.name }" required autocomplete="off">
                        <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                    </div>
                    <div class="med-row med-row-2">
                        <div class="med-field">
                            <label class="field-label">Code</label>
                            <input v-model="form.code" class="field" :class="{ 'has-error': form.errors.code }" :placeholder="editingId ? '' : 'Auto'">
                            <p v-if="form.errors.code" class="field-error">{{ form.errors.code }}</p>
                        </div>
                        <div class="med-field">
                            <label class="field-label">Status</label>
                            <select v-model="form.status" class="field">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="med-field mt-2">
                        <label class="field-label">Description</label>
                        <textarea v-model="form.description" class="field" rows="3"></textarea>
                        <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
                    </div>
                </section>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="department-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : (editingId ? 'Update' : 'Save') }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

defineProps({ departments: Array })

const page = usePage()
const flash = computed(() => page.props.flash || {})

const modalOpen = ref(false)
const editingId = ref(null)
const form = useForm({
    name: '',
    code: '',
    description: '',
    status: 'active',
})

const modalTitle = computed(() => (editingId.value ? 'Edit department' : 'Add department'))

const openCreate = () => {
    editingId.value = null
    form.clearErrors()
    form.reset()
    form.status = 'active'
    modalOpen.value = true
}

const openEdit = (dept) => {
    editingId.value = dept.id
    form.clearErrors()
    form.name = dept.name || ''
    form.code = dept.code || ''
    form.description = dept.description || ''
    form.status = dept.status || 'active'
    modalOpen.value = true
}

const closeModal = () => {
    modalOpen.value = false
    editingId.value = null
    form.clearErrors()
    form.reset()
    form.status = 'active'
}

const save = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    }
    if (editingId.value) {
        form.put(route('hrm.departments.update', editingId.value), options)
        return
    }
    form.post(route('hrm.departments.store'), options)
}

const remove = (dept) => destroyRecord('hrm.departments.destroy', dept.id, 'Delete this department?', 'Department deleted.')
</script>

<style scoped>
.med-form { display: flex; flex-direction: column; gap: 0.85rem; }
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
.med-row { display: grid; gap: 0.55rem; }
.med-row-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.req { color: #f46a6a; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); padding: 0 0.65rem; font-size: 0.82rem; background: #fff; height: 2rem; }
.field.has-error { border-color: #f46a6a; }
.field-error { margin: 0.2rem 0 0; font-size: 0.7rem; color: #f46a6a; }
.status-chip { display: inline-flex; align-items: center; border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 999px); padding: 0.22rem 0.6rem; font-size: 0.72rem; font-weight: 600; text-transform: capitalize; }
.status-chip.is-on { border-color: var(--shell-panel-accent-border, #b7ebd6); background: var(--shell-panel-accent-bg, #e8f8f1); color: var(--shell-panel-accent-text, #1e8f68); }
@media (max-width: 767.98px) { .med-row-2 { grid-template-columns: 1fr; } }
</style>
