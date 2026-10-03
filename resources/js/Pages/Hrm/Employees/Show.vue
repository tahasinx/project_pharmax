<template>
    <AuthenticatedLayout>
        <FormScreen :title="employee.full_name" :close-href="route('hrm.employees.index')">
            <div class="med-view-grid">
                <section class="med-panel">
                    <header class="med-panel-head"><h6>Employee</h6><p>Profile details</p></header>
                    <dl class="med-facts">
                        <div class="med-fact"><dt>Code</dt><dd>{{ employee.employee_code }}</dd></div>
                        <div class="med-fact"><dt>Department</dt><dd>{{ employee.department?.name || '—' }}</dd></div>
                        <div class="med-fact"><dt>Title</dt><dd>{{ employee.job_title || '—' }}</dd></div>
                        <div class="med-fact"><dt>Salary</dt><dd>{{ money(employee.salary) }}</dd></div>
                        <div class="med-fact"><dt>Status</dt><dd class="text-capitalize">{{ employee.status }}</dd></div>
                        <div class="med-fact"><dt>Type</dt><dd class="text-capitalize">{{ employee.employment_type?.replace('_', ' ') }}</dd></div>
                    </dl>
                </section>
                <section class="med-panel">
                    <header class="med-panel-head"><h6>Contact & login</h6><p>Reachability</p></header>
                    <dl class="med-facts">
                        <div class="med-fact"><dt>Phone</dt><dd>{{ employee.phone || '—' }}</dd></div>
                        <div class="med-fact"><dt>Email</dt><dd>{{ employee.email || '—' }}</dd></div>
                        <div class="med-fact"><dt>User</dt><dd>{{ employee.user?.email || 'Not linked' }}</dd></div>
                        <div class="med-fact"><dt>Roles</dt><dd>{{ employee.user?.roles?.map(r => r.name).join(', ') || '—' }}</dd></div>
                    </dl>
                </section>
                <section class="med-panel med-span-2">
                    <header class="med-panel-head"><h6>Documents</h6><p>{{ employee.documents?.length || 0 }} files</p></header>
                    <p v-if="!employee.documents?.length" class="text-muted font-size-13 mb-0">No documents uploaded.</p>
                    <ul v-else class="doc-list">
                        <li v-for="doc in employee.documents" :key="doc.id">
                            <a :href="doc.url" target="_blank" rel="noopener">{{ doc.title }}</a>
                            <span class="text-muted font-size-12">{{ doc.original_name }}</span>
                        </li>
                    </ul>
                </section>
            </div>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import { useMoney } from '@/Composables/useMoney'

defineProps({ employee: Object })
const { money } = useMoney()
</script>

<style scoped>
.med-view-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
.med-panel { background: var(--shell-panel-bg, #f8f9fc); border: 1px solid var(--shell-panel-border, #e6e8ee); border-radius: var(--pf-radius, 0.65rem); padding: 1rem 1.1rem; }
.med-span-2 { grid-column: 1 / -1; }
.med-panel-head { margin-bottom: 0.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--shell-panel-border, #e6e8ee); }
.med-panel-head h6 { margin: 0; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.med-panel-head p { margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--shell-panel-muted, #74788d); }
.med-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.65rem; margin: 0; }
.med-fact dt { font-size: 0.68rem; text-transform: uppercase; color: var(--shell-panel-muted, #74788d); margin-bottom: 0.15rem; }
.med-fact dd { margin: 0; font-weight: 600; }
.doc-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.4rem; }
.doc-list li { display: flex; flex-direction: column; gap: 0.1rem; }
@media (max-width: 991.98px) { .med-view-grid, .med-facts { grid-template-columns: 1fr; } }
</style>
