<template>
    <AuthenticatedLayout>
        <FormScreen title="Add employee" :close-href="route('hrm.employees.index')">
            <form id="employee-create-form" class="med-form" @submit.prevent="submit">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head"><h6>Profile</h6><p>Identity and compensation</p></header>
                        <div class="med-field">
                            <label class="field-label">Full name <span class="req">*</span></label>
                            <input v-model="form.full_name" class="field" required>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label">Code</label>
                                <input v-model="form.employee_code" class="field" placeholder="Auto">
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
                                <input v-model.number="form.salary" type="number" min="0" step="0.01" class="field">
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
                            <input v-model="form.email" type="email" class="field">
                        </div>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head"><h6>Login account</h6><p>Optional linked user + role/permissions</p></header>
                        <label class="switch-row">
                            <input v-model="form.create_user" type="checkbox">
                            <span>Create user login for this employee</span>
                        </label>
                        <template v-if="form.create_user">
                            <div class="med-field">
                                <label class="field-label">Login email <span class="req">*</span></label>
                                <input v-model="form.user_email" type="email" class="field" required>
                            </div>
                            <div class="med-field">
                                <label class="field-label">Password <span class="req">*</span></label>
                                <input v-model="form.user_password" type="password" class="field" required>
                            </div>
                            <div class="med-field">
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
                <button type="submit" form="employee-create-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Create employee' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

defineProps({ departments: Array, roles: Array })

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
    permissions: [],
})

const submit = () => form.post(route('hrm.employees.store'))
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
.field { width: 100%; border-radius: var(--pf-radius, 0.35rem); border: 1px solid var(--shell-panel-border, #ced4da); background: #fff; padding: 0.32rem 0.55rem; font-size: 0.82rem; }
.switch-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; font-size: 0.82rem; }
@media (max-width: 991.98px) { .med-form-grid, .med-row-2 { grid-template-columns: 1fr; } }
</style>
