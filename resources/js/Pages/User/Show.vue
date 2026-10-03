<template>
    <Head title="User Details" />
    <AuthenticatedLayout>
        <FormScreen :title="user.name" :close-href="route('users.index')">
            <template #header-actions>
                <span class="status-chip is-on">
                    <span class="status-dot" />
                    Active
                </span>
                <Link :href="route('users.edit', user.id)" class="btn btn-primary btn-sm">Edit</Link>
            </template>

            <div class="med-view">
                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Basic information</h6>
                            <p>Account details</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Full name</dt>
                                <dd>{{ user.name }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Email</dt>
                                <dd>{{ user.email }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>User ID</dt>
                                <dd>{{ user.id }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Email verified</dt>
                                <dd>
                                    <span class="status-chip" :class="{ 'is-on': !!user.email_verified_at }">
                                        <span class="status-dot" />
                                        {{ user.email_verified_at ? 'Verified' : 'Not verified' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Assigned roles</h6>
                            <p>Permissions granted to this user</p>
                        </header>
                        <div v-if="!user.roles?.length" class="text-muted font-size-13">No roles assigned.</div>
                        <div v-else class="role-tags">
                            <span
                                v-for="role in user.roles"
                                :key="role.id"
                                class="role-tag"
                                :class="`role-tag-${role.name}`"
                            >
                                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                            </span>
                        </div>
                        <dl v-if="user.roles?.length" class="med-facts med-facts-roles">
                            <div v-for="role in user.roles" :key="`desc-${role.id}`" class="med-fact med-fact-full">
                                <dt>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</dt>
                                <dd class="role-desc">{{ getRoleDescription(role.name) }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Account statistics</h6>
                        <p>Timestamps and activity</p>
                    </header>
                    <dl class="med-facts">
                        <div class="med-fact">
                            <dt>Created at</dt>
                            <dd>{{ formatDate(user.created_at) }}</dd>
                        </div>
                        <div class="med-fact">
                            <dt>Last updated</dt>
                            <dd>{{ formatDate(user.updated_at) }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
                <button
                    type="button"
                    class="btn btn-danger"
                    :disabled="user.id === $page.props.auth.user.id"
                    @click="deleteUser(user.id)"
                >
                    Delete user
                </button>
                <Link :href="route('users.edit', user.id)" class="btn btn-primary">Edit</Link>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

defineProps({
    user: Object,
})

const getRoleDescription = (roleName) => {
    const descriptions = {
        admin: 'Full system access and user management',
        manager: 'Management and reporting capabilities',
        cashier: 'POS operations and customer management',
        pharmacist: 'Medicine and prescription management',
    }
    return descriptions[roleName] || 'Standard user access'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const deleteUser = (id) => {
    destroyRecord('users.destroy', id, 'Delete this user?', 'The user has been deleted.')
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem 1rem;
    margin: 0;
}

.med-facts-roles {
    margin-top: 0.85rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-fact {
    min-width: 0;
}

.med-fact-full {
    grid-column: 1 / -1;
}

.med-fact dt {
    margin: 0 0 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-fact dd {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    word-break: break-word;
}

.med-fact dd.role-desc {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--shell-panel-muted, #74788d);
}

.role-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.role-tag {
    display: inline-flex;
    padding: 0.22rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    color: var(--shell-panel-text, #343747);
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

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts {
        grid-template-columns: 1fr;
    }
}
</style>

