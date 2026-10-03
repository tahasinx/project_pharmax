<template>
    <Head title="Branches" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Branches</h4>
        </template>

        <form class="med-form mb-3" @submit.prevent="save">
            <section class="med-panel">
                <header class="med-panel-head">
                    <h6>Add branch</h6>
                    <p>Each branch gets a warehouse and a counter.</p>
                </header>
                <div class="med-row med-row-3">
                    <div class="med-field">
                        <label class="field-label" for="br-name">Branch name <span class="req">*</span></label>
                        <input id="br-name" v-model="form.name" type="text" class="field" required>
                    </div>
                    <div class="med-field">
                        <label class="field-label" for="br-code">Code</label>
                        <input id="br-code" v-model="form.code" type="text" class="field">
                    </div>
                    <div class="med-field d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Add branch</button>
                    </div>
                </div>
            </section>
        </form>

        <LunaTable title="Branches" empty-text="No branches found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Warehouse</th>
                        <th>Counter</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="branch in branches" :key="branch.id">
                        <td>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold">{{ branch.name }}</span>
                                <span v-if="branch.is_head_office" class="status-chip is-on">
                                    <span class="status-dot" />
                                    Head office
                                </span>
                            </div>
                        </td>
                        <td>{{ (branch.warehouses || []).map(row => row.name).join(', ') || '—' }}</td>
                        <td>{{ (branch.counters || []).map(row => row.name).join(', ') || '—' }}</td>
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

defineProps({ branches: Array })

const form = reactive({ name: '', code: '' })
const save = () => router.post(route('branches.store'), form, {
    onSuccess: () => {
        form.name = ''
        form.code = ''
    },
})
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
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
}

.med-row-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    .med-row-3 {
        grid-template-columns: 1fr;
    }
}
</style>
