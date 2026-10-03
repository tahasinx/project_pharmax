<template>
    <Head title="Employees" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Employees</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary btn-sm" @click="openCreate">Add employee</button>
            </div>
        </template>

        <div v-if="flash.error" class="alert alert-danger py-2">{{ flash.error }}</div>
        <div v-if="flash.success" class="alert alert-success py-2">{{ flash.success }}</div>

        <LunaTable title="Employees" empty-text="No employees found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Salary</th>
                        <th>User</th>
                        <th>Docs</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="employee in employees" :key="employee.id">
                        <td>
                            <div class="fw-semibold">{{ employee.full_name }}</div>
                            <div class="text-muted font-size-12">{{ employee.job_title || '—' }}</div>
                        </td>
                        <td>{{ employee.employee_code }}</td>
                        <td>{{ employee.department?.name || '—' }}</td>
                        <td>{{ money(employee.salary) }}</td>
                        <td>{{ employee.user?.email || '—' }}</td>
                        <td>{{ employee.documents_count ?? 0 }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': employee.status === 'active' }">{{ employee.status }}</span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <button type="button" class="btn btn-sm btn-icon btn-soft-primary" title="Documents" @click="openDocs(employee)">
                                    <i class="bi bi-file-earmark-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit" @click="openEdit(employee)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="remove(employee)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <FormScreen v-if="modalOpen" :title="modalTitle" size="lg" @close="closeModal">
            <form id="employee-form" class="med-form" @submit.prevent="saveEmployee">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head"><h6>Profile</h6><p>Identity and compensation</p></header>
                        <div class="med-field">
                            <label class="field-label">Full name <span class="req">*</span></label>
                            <input v-model="form.full_name" class="field" :class="{ 'has-error': form.errors.full_name }" required>
                            <p v-if="form.errors.full_name" class="field-error">{{ form.errors.full_name }}</p>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label">Code</label>
                                <input v-model="form.employee_code" class="field" :class="{ 'has-error': form.errors.employee_code }" :placeholder="editingId ? '' : 'Auto'">
                                <p v-if="form.errors.employee_code" class="field-error">{{ form.errors.employee_code }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label">Department</label>
                                <select v-model="form.department_id" class="field">
                                    <option value="">—</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label">Job title</label>
                                <input v-model="form.job_title" class="field">
                            </div>
                            <div class="med-field">
                                <label class="field-label">Salary</label>
                                <input v-model.number="form.salary" type="number" min="0" step="0.01" class="field" :class="{ 'has-error': form.errors.salary }">
                                <p v-if="form.errors.salary" class="field-error">{{ form.errors.salary }}</p>
                            </div>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label">Employment</label>
                                <select v-model="form.employment_type" class="field">
                                    <option value="full_time">Full time</option>
                                    <option value="part_time">Part time</option>
                                    <option value="contract">Contract</option>
                                </select>
                            </div>
                            <div class="med-field">
                                <label class="field-label">Status</label>
                                <select v-model="form.status" class="field">
                                    <option value="active">Active</option>
                                    <option value="on_leave">On leave</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label">Phone</label>
                                <input v-model="form.phone" class="field">
                            </div>
                            <div class="med-field">
                                <label class="field-label">Hire date</label>
                                <input v-model="form.hire_date" type="date" class="field">
                            </div>
                        </div>
                        <div class="med-field">
                            <label class="field-label">Email</label>
                            <input v-model="form.email" type="email" class="field" :class="{ 'has-error': form.errors.email }">
                            <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="med-field">
                            <label class="field-label">Emergency contact</label>
                            <input v-model="form.emergency_contact" class="field">
                        </div>
                        <div class="med-field">
                            <label class="field-label">Address</label>
                            <textarea v-model="form.address" class="field" rows="2"></textarea>
                        </div>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head"><h6>Login account</h6><p>Optional linked user</p></header>
                        <template v-if="!editingId">
                            <label class="switch-row">
                                <input v-model="form.create_user" type="checkbox">
                                <span>Create user login for this employee</span>
                            </label>
                            <template v-if="form.create_user">
                                <div class="med-field">
                                    <label class="field-label">Login email <span class="req">*</span></label>
                                    <input v-model="form.user_email" type="email" class="field" :class="{ 'has-error': form.errors.user_email }" required>
                                    <p v-if="form.errors.user_email" class="field-error">{{ form.errors.user_email }}</p>
                                </div>
                                <div class="med-field">
                                    <label class="field-label">Password <span class="req">*</span></label>
                                    <input v-model="form.user_password" type="password" class="field" :class="{ 'has-error': form.errors.user_password }" required minlength="8">
                                    <p v-if="form.errors.user_password" class="field-error">{{ form.errors.user_password }}</p>
                                </div>
                                <div class="med-field">
                                    <label class="field-label">Role</label>
                                    <select v-model="form.user_role" class="field">
                                        <option value="">—</option>
                                        <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                                    </select>
                                </div>
                            </template>
                        </template>
                        <template v-else>
                            <p class="text-muted font-size-13 mb-2">
                                {{ editingUserEmail ? `Linked login: ${editingUserEmail}` : 'No login linked.' }}
                            </p>
                            <div v-if="editingUserEmail" class="med-field">
                                <label class="field-label">Role</label>
                                <select v-model="form.user_role" class="field">
                                    <option value="">—</option>
                                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                                </select>
                            </div>
                        </template>
                    </section>
                </div>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="employee-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : (editingId ? 'Update employee' : 'Create employee') }}
                </button>
            </template>
        </FormScreen>

        <FormScreen v-if="docsOpen" :title="docsTitle" size="md" @close="closeDocs">
            <form id="employee-doc-form" class="med-form" @submit.prevent="uploadDoc">
                <section class="med-panel">
                    <header class="med-panel-head"><h6>Upload document</h6><p>PDF, image, or Word · max 10 MB</p></header>
                    <div class="med-field mb-2">
                        <label class="field-label">Title <span class="req">*</span></label>
                        <input v-model="docForm.title" class="field" :class="{ 'has-error': docForm.errors.title }" required>
                        <p v-if="docForm.errors.title" class="field-error">{{ docForm.errors.title }}</p>
                    </div>
                    <div class="med-field">
                        <label class="field-label">File <span class="req">*</span></label>
                        <input type="file" class="field field-file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*" @change="onDocFile">
                        <p v-if="docForm.errors.file" class="field-error">{{ docForm.errors.file }}</p>
                    </div>
                </section>
            </form>

            <section class="med-panel mt-3">
                <header class="med-panel-head"><h6>Files</h6><p>{{ docsList.length }} attached</p></header>
                <p v-if="!docsList.length" class="text-muted font-size-13 mb-0">No documents yet.</p>
                <ul v-else class="doc-list">
                    <li v-for="doc in docsList" :key="doc.id">
                        <div class="min-w-0">
                            <a :href="doc.url" target="_blank" rel="noopener" class="fw-semibold text-truncate d-block">{{ doc.title }}</a>
                            <span class="text-muted font-size-12">{{ doc.original_name }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Remove" @click="removeDoc(doc)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </li>
                </ul>
            </section>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
                <button type="submit" form="employee-doc-form" class="btn btn-primary" :disabled="docForm.processing || !docForm.file">
                    {{ docForm.processing ? 'Uploading…' : 'Upload' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { destroyRecord, confirmDelete, notify } from '@/Composables/confirmDelete'
import { useMoney } from '@/Composables/useMoney'

const props = defineProps({
    employees: Array,
    departments: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const { money } = useMoney()

const modalOpen = ref(false)
const editingId = ref(null)
const editingUserEmail = ref('')
const form = useForm({
    full_name: '',
    employee_code: '',
    department_id: '',
    email: '',
    phone: '',
    job_title: '',
    employment_type: 'full_time',
    status: 'active',
    hire_date: '',
    salary: 0,
    address: '',
    emergency_contact: '',
    create_user: false,
    user_email: '',
    user_password: '',
    user_role: '',
})

const modalTitle = computed(() => (editingId.value ? 'Edit employee' : 'Add employee'))

const blankForm = () => {
    form.clearErrors()
    form.reset()
    form.employment_type = 'full_time'
    form.status = 'active'
    form.salary = 0
    form.create_user = false
    form.department_id = ''
    form.user_role = ''
}

const openCreate = () => {
    editingId.value = null
    editingUserEmail.value = ''
    blankForm()
    modalOpen.value = true
}

const openEdit = (employee) => {
    editingId.value = employee.id
    editingUserEmail.value = employee.user?.email || ''
    form.clearErrors()
    form.full_name = employee.full_name || ''
    form.employee_code = employee.employee_code || ''
    form.department_id = employee.department?.id || employee.department_id || ''
    form.email = employee.email || ''
    form.phone = employee.phone || ''
    form.job_title = employee.job_title || ''
    form.employment_type = employee.employment_type || 'full_time'
    form.status = employee.status || 'active'
    form.hire_date = employee.hire_date ? String(employee.hire_date).slice(0, 10) : ''
    form.salary = Number(employee.salary || 0)
    form.address = employee.address || ''
    form.emergency_contact = employee.emergency_contact || ''
    form.create_user = false
    form.user_email = ''
    form.user_password = ''
    form.user_role = employee.user?.roles?.[0]?.name || ''
    modalOpen.value = true
}

const closeModal = () => {
    modalOpen.value = false
    editingId.value = null
    editingUserEmail.value = ''
    blankForm()
}

const saveEmployee = () => {
    const options = { preserveScroll: true, onSuccess: () => closeModal() }
    if (editingId.value) {
        form.put(route('hrm.employees.update', editingId.value), options)
        return
    }
    form.post(route('hrm.employees.store'), options)
}

const remove = (employee) => destroyRecord('hrm.employees.destroy', employee.id, 'Delete this employee?', 'Employee deleted.')

const docsOpen = ref(false)
const docsEmployeeId = ref(null)
const docsList = ref([])
const docsTitle = computed(() => {
    const row = props.employees.find((e) => e.id === docsEmployeeId.value)
    return row ? `Documents · ${row.full_name}` : 'Documents'
})
const docForm = useForm({ title: '', file: null })

const openDocs = (employee) => {
    docsEmployeeId.value = employee.id
    docsList.value = [...(employee.documents || [])]
    docForm.clearErrors()
    docForm.reset()
    docsOpen.value = true
}

const closeDocs = () => {
    docsOpen.value = false
    docsEmployeeId.value = null
    docsList.value = []
    docForm.reset()
    docForm.clearErrors()
}

const onDocFile = (event) => {
    docForm.file = event.target.files?.[0] || null
}

const syncDocsFromProps = () => {
    const row = props.employees.find((e) => e.id === docsEmployeeId.value)
    docsList.value = [...(row?.documents || [])]
}

watch(() => props.employees, () => {
    if (docsOpen.value) syncDocsFromProps()
})

const uploadDoc = () => {
    if (!docsEmployeeId.value || !docForm.file) return
    docForm.post(route('hrm.employees.documents.store', docsEmployeeId.value), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            docForm.reset()
            docForm.clearErrors()
            syncDocsFromProps()
        },
    })
}

const removeDoc = (doc) => {
    confirmDelete({
        title: 'Remove this document?',
        text: `"${doc.title}" will be deleted.`,
    }).then((result) => {
        if (!result.isConfirmed) return
        router.delete(route('hrm.employees.documents.destroy', [docsEmployeeId.value, doc.id]), {
            preserveScroll: true,
            onSuccess: () => {
                syncDocsFromProps()
                notify({ title: 'Removed', text: 'Document deleted.' })
            },
        })
    })
}
</script>

<style scoped>
.med-form { display: flex; flex-direction: column; gap: 0.85rem; }
.med-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
.med-field { margin-bottom: 0.55rem; }
.med-row { display: grid; gap: 0.55rem; margin-bottom: 0.55rem; }
.med-row-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.field-label { display: block; margin-bottom: 0.2rem; font-size: 0.72rem; font-weight: 600; }
.req { color: #f46a6a; }
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); background: #fff; padding: 0 0.65rem; font-size: 0.82rem; height: 2rem; }
.field.has-error { border-color: #f46a6a; }
.field-error { margin: 0.2rem 0 0; font-size: 0.7rem; color: #f46a6a; }
.switch-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; font-size: 0.82rem; }
.status-chip { display: inline-flex; border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 999px); padding: 0.22rem 0.6rem; font-size: 0.72rem; font-weight: 600; text-transform: capitalize; }
.status-chip.is-on { border-color: var(--shell-panel-accent-border, #b7ebd6); background: var(--shell-panel-accent-bg, #e8f8f1); color: var(--shell-panel-accent-text, #1e8f68); }
.doc-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.55rem; }
.doc-list li { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.45rem 0; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.doc-list li:last-child { border-bottom: 0; }
@media (max-width: 991.98px) { .med-form-grid, .med-row-2 { grid-template-columns: 1fr; } }
</style>
