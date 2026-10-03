<template>
    <Head title="Menu Management" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Menu</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('menus.create')" class="btn btn-primary btn-sm">Add Menu Item</Link>
            </div>
        </template>

        <LunaTable title="Menu" empty-text="No menu items found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Menu item</th>
                        <th>Route</th>
                        <th>Permission</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="menu in menus" :key="menu.id">
                        <td>{{ menu.order }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i v-if="menu.icon && String(menu.icon).startsWith('bi-')" class="bi" :class="menu.icon"></i>
                                <span v-else-if="menu.icon" class="font-size-14">{{ menu.icon }}</span>
                                <div class="fw-semibold">{{ menu.name }}</div>
                            </div>
                        </td>
                        <td>{{ menu.route || '—' }}</td>
                        <td>{{ menu.permission || '—' }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <span v-for="role in menu.roles" :key="role.id" class="status-chip is-on">{{ role.name }}</span>
                                <span v-if="!menu.roles?.length">—</span>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="status-chip" :class="{ 'is-on': menu.is_active }" @click="toggleStatus(menu.id)">
                                <span class="status-dot" />
                                {{ menu.is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('menus.edit', menu.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteMenu(menu.id)"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <div class="med-panel mt-3">
            <header class="med-panel-head">
                <h6>Menu preview by role</h6>
                <p>Active items assigned to each role</p>
            </header>
            <div class="row g-3">
                <div v-for="role in roles" :key="role.id" class="col-md-6 col-xl-3">
                    <div class="role-preview">
                        <div class="fw-semibold mb-2">{{ role.name }}</div>
                        <div v-for="menu in getMenusForRole(role.id)" :key="menu.id" class="text-muted font-size-13 mb-1">
                            {{ menu.name }}
                        </div>
                        <div v-if="!getMenusForRole(role.id).length" class="text-danger font-size-13">No menu items assigned</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    menus: Array,
    roles: Array,
})

const getMenusForRole = (roleId) => {
    return props.menus
        .filter((menu) => menu.is_active && menu.roles?.some((role) => role.id === roleId))
        .sort((a, b) => a.order - b.order)
}

const toggleStatus = (menuId) => {
    router.post(route('menus.toggle-status', menuId))
}

const deleteMenu = (id) => {
    destroyRecord('menus.destroy', id, 'Delete this menu item?', 'The menu item has been deleted.')
}
</script>

<style scoped>
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

.role-preview {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.45rem);
    background: var(--shell-panel-surface, #fff);
    padding: 0.75rem;
    min-height: 7rem;
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
