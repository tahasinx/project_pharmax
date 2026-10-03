<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Users</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('users.create')" class="btn btn-primary btn-sm">Add User</Link>
            </div>
        </template>

        <LunaTable title="Users" :pagination="users" empty-text="No users found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="user-avatar">{{ user.name.charAt(0).toUpperCase() }}</span>
                                <span class="fw-semibold">{{ user.name }}</span>
                            </div>
                        </td>
                        <td>{{ user.email }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <span v-for="role in user.roles" :key="role.id" class="status-chip is-on">{{ role.name }}</span>
                                <span v-if="!user.roles?.length">—</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-chip is-on">
                                <span class="status-dot" />
                                Active
                            </span>
                        </td>
                        <td>{{ formatDate(user.created_at) }}</td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('users.show', user.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('users.edit', user.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon btn-soft-danger"
                                    title="Delete"
                                    :disabled="user.id === $page.props.auth.user.id"
                                    @click="deleteUser(user.id)"
                                >
                                    <i class="bi bi-trash"></i>
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
import { Head, Link } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({
    users: Object,
})

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const deleteUser = (id) => {
    destroyRecord('users.destroy', id, 'Delete this user?', 'The user has been deleted.')
}
</script>

<style scoped>
.user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: var(--pf-radius, 999px);
    background: rgba(var(--pf-accent-rgb, 81, 86, 190), 0.12);
    color: var(--pf-accent, #5156be);
    font-size: 0.72rem;
    font-weight: 700;
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
</style>
