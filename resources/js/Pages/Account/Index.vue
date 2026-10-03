<template>
    <Head title="Accounts" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Accounts</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('accounts.create')" class="btn btn-primary btn-sm">New account</Link>
            </div>
        </template>

        <LunaTable title="Accounts" empty-text="No accounts found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="account in accounts" :key="account.id">
                        <td>
                            <div class="fw-semibold">{{ account.name }}</div>
                        </td>
                        <td>{{ account.code || '—' }}</td>
                        <td>{{ account.type || '—' }}</td>
                        <td>{{ money(account.balance) }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': account.status }">
                                <span class="status-dot" />
                                {{ account.status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('accounts.show', account.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('accounts.edit', account.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteAccount(account.id)"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({
    accounts: Array,
})

const pageStore = usePage()

const money = (amount) => {
    const ui = pageStore.props.ui || {}
    const value = Number(amount || 0).toFixed(2)
    return ui.currency_position === 'after' ? `${value}${ui.currency_symbol || ''}` : `${ui.currency_symbol || ''}${value}`
}

const deleteAccount = (id) => {
    destroyRecord('accounts.destroy', id, 'Delete this account?', 'The account has been deleted.')
}
</script>

<style scoped>
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
