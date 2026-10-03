<template>
    <Head title="Edit Menu Item" />
    <AuthenticatedLayout>
        <FormScreen title="Edit menu item" :close-href="route('menus.index')">
            <template #header-actions>
                <button
                    type="button"
                    class="status-chip"
                    :class="{ 'is-on': form.is_active }"
                    role="switch"
                    :aria-checked="form.is_active"
                    @click="form.is_active = !form.is_active"
                >
                    <span class="status-dot" />
                    {{ form.is_active ? 'Active' : 'Inactive' }}
                </button>
            </template>

            <form id="menu-edit-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Menu information</h6>
                            <p>Route, icon, and display order</p>
                        </header>

                        <div class="med-field">
                            <label class="field-label" for="menu-name">Menu name <span class="req">*</span></label>
                            <input
                                id="menu-name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Dashboard, Medicines"
                                class="field"
                                required
                            >
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="menu-route">Route name <span class="req">*</span></label>
                            <input
                                id="menu-route"
                                v-model="form.route"
                                type="text"
                                placeholder="e.g., dashboard, medicines.index"
                                class="field"
                                required
                            >
                            <p v-if="form.errors.route" class="field-error">{{ form.errors.route }}</p>
                        </div>

                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="menu-icon">Icon (emoji or class)</label>
                                <input
                                    id="menu-icon"
                                    v-model="form.icon"
                                    type="text"
                                    placeholder="e.g., 🏠, 📊, 💊"
                                    class="field"
                                >
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="menu-order">Display order <span class="req">*</span></label>
                                <input
                                    id="menu-order"
                                    v-model.number="form.order"
                                    type="number"
                                    min="0"
                                    class="field"
                                    required
                                >
                            </div>
                        </div>

                        <div class="med-field">
                            <label class="field-label" for="menu-permission">Permission required</label>
                            <select id="menu-permission" v-model="form.permission" class="field">
                                <option value="">No Permission Required</option>
                                <option value="view-dashboard">View Dashboard</option>
                                <option value="manage-medicines">Manage Medicines</option>
                                <option value="manage-customers">Manage Customers</option>
                                <option value="manage-invoices">Manage Invoices</option>
                                <option value="manage-purchases">Manage Purchases</option>
                                <option value="manage-accounts">Manage Accounts</option>
                                <option value="manage-categories">Manage Categories</option>
                                <option value="manage-manufacturers">Manage Manufacturers</option>
                                <option value="manage-banks">Manage Banks</option>
                                <option value="manage-users">Manage Users</option>
                                <option value="manage-menus">Manage Menus</option>
                                <option value="pos-access">POS Access</option>
                                <option value="view-reports">View Reports</option>
                            </select>
                        </div>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Assign to roles</h6>
                            <p>Select which roles see this menu item</p>
                        </header>

                        <div class="med-field">
                            <span class="field-label">Select roles <span class="req">*</span></span>
                            <div class="role-list">
                                <label v-for="role in roles" :key="role.id" class="role-check">
                                    <input
                                        :id="`role-${role.id}`"
                                        v-model="form.roles"
                                        :value="role.id"
                                        type="checkbox"
                                    >
                                    {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                </label>
                            </div>
                            <p v-if="form.errors.roles" class="field-error">{{ form.errors.roles }}</p>
                        </div>

                        <div class="role-hint">
                            <h6>Current roles</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span
                                    v-for="menuRole in menu.roles"
                                    :key="menuRole.id"
                                    class="badge bg-primary-subtle text-primary"
                                >
                                    {{ menuRole.name }}
                                </span>
                            </div>
                        </div>
                    </section>
                </div>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="menu-edit-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Update menu item' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    menu: Object,
    roles: Array,
})

const form = useForm({
    name: props.menu.name,
    route: props.menu.route,
    icon: props.menu.icon || '',
    order: props.menu.order,
    is_active: !!props.menu.is_active,
    permission: props.menu.permission || '',
    roles: props.menu.roles.map((role) => role.id),
})

const submitForm = () => {
    form.put(route('menus.update', props.menu.id))
}
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-form-grid {
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
    margin-bottom: 0.55rem;
}

.med-field:last-child {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
    margin-bottom: 0.55rem;
}

.med-row:last-child {
    margin-bottom: 0;
}

.med-row-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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

.field-error {
    margin: 0.2rem 0 0;
    font-size: 0.7rem;
    color: #f46a6a;
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

.role-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.role-check {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: var(--shell-panel-text, #343747);
    margin: 0;
}

.role-hint {
    margin-top: 0.75rem;
    padding: 0.65rem 0.75rem;
    border-radius: var(--pf-radius, 0.35rem);
    background: rgba(var(--pf-accent-rgb, 81, 86, 190), 0.06);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
}

.role-hint h6 {
    margin: 0 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--shell-panel-text, #343747);
}

@media (max-width: 991.98px) {
    .med-form-grid,
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
