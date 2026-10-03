<template>
    <Head title="Create User" />
    <AuthenticatedLayout>
        <FormScreen title="Create user" :close-href="route('users.index')">
            <form id="user-create-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Basic information</h6>
                            <p>Account credentials</p>
                        </header>
                        <div class="med-field">
                            <label class="field-label" for="user-name">Full name <span class="req">*</span></label>
                            <input id="user-name" v-model="form.name" type="text" class="field" required autocomplete="name">
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div class="med-field">
                            <label class="field-label" for="user-email">Email address <span class="req">*</span></label>
                            <input id="user-email" v-model="form.email" type="email" class="field" required autocomplete="email">
                            <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="user-password">Password <span class="req">*</span></label>
                                <input id="user-password" v-model="form.password" type="password" class="field" required autocomplete="new-password">
                                <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="user-password-confirm">Confirm password <span class="req">*</span></label>
                                <input id="user-password-confirm" v-model="form.password_confirmation" type="password" class="field" required autocomplete="new-password">
                            </div>
                        </div>
                    </section>
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Assign roles</h6>
                            <p>Permissions for this user</p>
                        </header>
                        <div class="med-field">
                            <span class="field-label">Select roles <span class="req">*</span></span>
                            <div class="role-list">
                                <label v-for="role in roles" :key="role.id" class="role-option" :for="`role-${role.id}`">
                                    <input
                                        :id="`role-${role.id}`"
                                        v-model="form.roles"
                                        :value="role.name"
                                        type="checkbox"
                                    >
                                    <span>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.roles" class="field-error">{{ form.errors.roles }}</p>
                        </div>
                        <div class="role-hints med-panel-note">
                            <h6>Role descriptions</h6>
                            <ul>
                                <li><strong>Admin:</strong> Full system access</li>
                                <li><strong>Manager:</strong> Management and reporting access</li>
                                <li><strong>Cashier:</strong> POS and customer management</li>
                                <li><strong>Pharmacist:</strong> Medicine and prescription management</li>
                            </ul>
                        </div>
                    </section>
                </div>
                <section v-if="permissionGroups" class="med-panel">
                    <header class="med-panel-head">
                        <h6>Module permissions</h6>
                        <p>Individual grants on top of selected roles</p>
                    </header>
                    <div v-for="(perms, group) in permissionGroups" :key="group" class="perm-group">
                        <div class="fw-semibold font-size-13 mb-2">{{ group }}</div>
                        <div class="perm-list">
                            <label v-for="perm in perms" :key="perm" class="perm-option">
                                <input v-model="form.permissions" type="checkbox" :value="perm">
                                <span>{{ perm }}</span>
                            </label>
                        </div>
                    </div>
                </section>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="user-create-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Create user' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
defineProps({
    roles: Array,
    permissionGroups: Object,
})
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    permissions: [],
})
const submitForm = () => {
    form.post(route('users.store'))
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
.role-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.role-option {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.82rem;
    color: var(--shell-panel-text, #343747);
    margin: 0;
    cursor: pointer;
}
.med-panel-note {
    margin-top: 0.75rem;
    padding: 0.65rem 0.75rem;
    border-radius: var(--pf-radius, 0.35rem);
    background: rgba(var(--pf-accent-rgb, 81, 86, 190), 0.06);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
}
.med-panel-note h6 {
    margin: 0 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}
.med-panel-note ul {
    margin: 0;
    padding-left: 1.1rem;
    font-size: 0.75rem;
    color: var(--shell-panel-muted, #74788d);
}
.perm-group { margin-bottom: 0.85rem; }
.perm-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); gap: 0.35rem 0.75rem; }
.perm-option { display: flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; margin: 0; cursor: pointer; }
@media (max-width: 991.98px) {
    .med-form-grid,
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>