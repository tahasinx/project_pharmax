<template>
    <Head title="Manufacturers" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Manufacturers</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('manufacturers.create')" class="btn btn-primary btn-sm">Add Manufacturer</Link>
            </div>
        </template>

        <LunaTable title="Manufacturers" :pagination="manufacturers" empty-text="No manufacturers found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Manufacturer</th>
                        <th>Contact</th>
                        <th>Medicines</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="manufacturer in manufacturers.data" :key="manufacturer.id">
                        <td>
                            <div class="fw-semibold">{{ manufacturer.name }}</div>
                        </td>
                        <td>
                            <div>{{ manufacturer.mobile || '—' }}</div>
                            <div class="text-muted font-size-12">{{ manufacturer.email || '—' }}</div>
                        </td>
                        <td>{{ manufacturer.medicines_count || 0 }}</td>
                        <td>
                            <span class="status-chip" :class="{ 'is-on': manufacturer.status }">
                                <span class="status-dot" />
                                {{ manufacturer.status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-actions">
                                <Link :href="route('manufacturers.show', manufacturer.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                <Link :href="route('manufacturers.edit', manufacturer.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteManufacturer(manufacturer.id)"><i class="bi bi-trash"></i></button>
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
    manufacturers: Object,
    filters: Object,
})

const deleteManufacturer = (id) => {
    destroyRecord('manufacturers.destroy', id, 'Delete this manufacturer?', 'The manufacturer has been deleted.')
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
