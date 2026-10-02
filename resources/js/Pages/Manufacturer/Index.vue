<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Manufacturer</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('manufacturers.create')" class="btn btn-primary btn-sm">Add Manufacturer</Link>
            </div>
        </template>

        <LunaTable title="Manufacturers" :pagination="manufacturers">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Manufacturer</th>
                        <th>Contact</th>
                        <th>Medicines Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!manufacturers.data.length">
                        <td colspan="5" class="text-center text-muted">No manufacturers found</td>
                    </tr>
                    <tr v-for="manufacturer in manufacturers.data" :key="manufacturer.id">
                        <td>
                            <div class="fw-medium">{{ manufacturer.name }}</div>
                            <div class="text-muted font-size-12">ID: {{ manufacturer.id }}</div>
                        </td>
                        <td>
                            <div>{{ manufacturer.mobile || 'No mobile' }}</div>
                            <div class="text-muted font-size-12">{{ manufacturer.email || 'No email' }}</div>
                        </td>
                        <td>{{ manufacturer.medicines_count || 0 }}</td>
                        <td>
                            <span class="badge" :class="manufacturer.status ? 'bg-success' : 'bg-danger'">
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
import { Link } from '@inertiajs/vue3'
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
