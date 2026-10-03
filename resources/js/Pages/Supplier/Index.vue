<template>
    <Head title="Suppliers" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Suppliers</h4>
        </template>

        <form class="med-form mb-3" @submit.prevent="save">
            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Add supplier</h6>
                    <p>Credit terms sit on the supplier. Orders and returns count against them.</p>
                </header>
                <div class="med-row med-row-2">
                    <div class="med-field">
                        <label class="field-label" for="sup-name">Name <span class="req">*</span></label>
                        <input id="sup-name" v-model="form.name" type="text" class="field" required>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="sup-phone">Phone</label>
                        <input id="sup-phone" v-model="form.phone" type="text" class="field" autocomplete="tel">
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="sup-limit">Credit limit</label>
                        <input id="sup-limit" v-model="form.credit_limit" type="number" step="0.01" class="field">
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="sup-days">Credit days</label>
                        <input id="sup-days" v-model="form.credit_days" type="number" class="field">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save supplier</button>
                </div>
            </section>
        </form>

        <LunaTable title="Suppliers" empty-text="No suppliers found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Terms</th>
                        <th>Orders</th>
                        <th>Returns</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in suppliers" :key="row.id">
                        <td>
                            <div class="fw-semibold">{{ row.name }}</div>
                            <div class="text-muted font-size-12">{{ row.phone || 'No phone' }}</div>
                        </td>
                        <td>
                            <div>{{ row.credit_days }} day terms</div>
                            <div class="text-muted font-size-12">Limit {{ row.credit_limit }}</div>
                        </td>
                        <td>{{ row.purchase_orders_count }}</td>
                        <td>{{ row.purchase_returns_count }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({ suppliers: Array })

const form = reactive({ name: '', phone: '', credit_limit: 0, credit_days: 0 })
const save = () => router.post(route('suppliers.store'), form)
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
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

@media (max-width: 991.98px) {
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
