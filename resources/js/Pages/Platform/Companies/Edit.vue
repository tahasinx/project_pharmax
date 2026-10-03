<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import FormScreen from '@/Components/FormScreen.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Layout from '../Layout.vue';

const props = defineProps({ company: Object });
const form = useForm({
    name: props.company.name,
    email: props.company.email || '',
    admin_email: props.company.admin_email || '',
    phone: props.company.phone || '',
    address: props.company.address || '',
    status: props.company.status,
});
const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'locked', label: 'Locked' },
];
</script>

<template>
    <Head title="Edit pharmacy" />
    <Layout>
        <FormScreen :title="`Edit ${company.name}`" :close-href="route('platform.companies.show', company.id)">
            <form id="platform-company-edit" class="edit-form" @submit.prevent="form.put(`/platform/companies/${company.id}`)">
                <section class="edit-panel">
                    <header class="edit-panel-head">
                        <h2>Pharmacy details</h2>
                        <p>
                            Slug <code>{{ company.slug }}</code> and database <code>{{ company.database_name }}</code> stay fixed after provisioning.
                        </p>
                    </header>
                    <div class="edit-grid">
                        <label class="edit-field edit-field-wide">
                            <span>Pharmacy name <em>*</em></span>
                            <input v-model="form.name" type="text" required>
                        </label>
                        <label class="edit-field">
                            <span>Contact email</span>
                            <input v-model="form.email" type="email">
                        </label>
                        <label class="edit-field">
                            <span>Admin email</span>
                            <input v-model="form.admin_email" type="email">
                        </label>
                        <label class="edit-field">
                            <span>Phone</span>
                            <input v-model="form.phone" type="tel">
                        </label>
                        <label class="edit-field">
                            <span>Status</span>
                            <SearchableSelect v-model="form.status" :options="statusOptions" placeholder="Status…" />
                        </label>
                        <label class="edit-field edit-field-wide">
                            <span>Address</span>
                            <textarea v-model="form.address" rows="2" />
                        </label>
                    </div>
                </section>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button form="platform-company-edit" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save' }}
                </button>
            </template>
        </FormScreen>
    </Layout>
</template>

<style scoped>
.edit-form {
    max-width: 40rem;
    margin: 0 auto;
}

.edit-panel {
    border: 1px solid var(--pf-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    background: var(--pf-surface, #fff);
    padding: 1rem 1.1rem 1.15rem;
}

.edit-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.55rem;
    border-bottom: 1px solid var(--pf-border, #eef0f5);
}

.edit-panel-head h2 {
    margin: 0 !important;
    font-size: 0.86rem !important;
    font-weight: 600 !important;
    color: var(--pf-text, #343747) !important;
}

.edit-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: #74788d;
}

.edit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.edit-field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin: 0;
}

.edit-field-wide {
    grid-column: 1 / -1;
}

.edit-field > span {
    font-size: 0.72rem;
    font-weight: 600;
    color: #495057;
}

.edit-field em {
    color: #f46a6a;
    font-style: normal;
}

.edit-field input,
.edit-field select,
.edit-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ced4da;
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    background: #fff;
    color: #343747;
    font-size: 0.82rem;
}

.edit-field input,
.edit-field select {
    height: 2rem;
    min-height: 2rem;
    padding: 0 0.55rem;
    line-height: calc(2rem - 2px);
}

.edit-field textarea {
    min-height: 4rem;
    padding: 0.4rem 0.55rem;
    line-height: 1.35;
    resize: vertical;
}

.edit-field input:focus,
.edit-field select:focus,
.edit-field textarea:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

@media (max-width: 767.98px) {
    .edit-grid {
        grid-template-columns: 1fr;
    }
}
</style>
