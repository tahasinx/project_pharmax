<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">{{ title }}</h4>
        </template>
        <div v-if="flash.error" class="alert alert-danger">{{ flash.error }}</div>
        <div v-if="flash.success" class="alert alert-success">{{ flash.success }}</div>
        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-2 align-items-center" @submit.prevent="save">
                    <div class="col-md-8 col-lg-6">
                        <input v-model="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-plus me-1"></i>Add</button>
                    </div>
                </form>
                <div v-if="formError" class="text-danger small mt-2">{{ formError }}</div>
            </div>
        </div>
        <LunaTable :title="title">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-end">Medicines</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!rows.length">
                        <td colspan="3" class="text-center text-muted">Nothing saved yet.</td>
                    </tr>
                    <tr v-for="row in rows" :key="row.id">
                        <td>
                            <input v-if="editingId === row.id" v-model="editName" class="form-control form-control-sm" required>
                            <span v-else>{{ row.name }}</span>
                        </td>
                        <td class="text-end">{{ row.medicines_count ?? 0 }}</td>
                        <td class="text-end">
                            <button v-if="editingId !== row.id" type="button" class="btn btn-sm btn-outline-primary me-1" @click="startEdit(row)">Edit</button>
                            <button v-if="editingId === row.id" type="button" class="btn btn-sm btn-primary me-1" @click="update(row)">Save</button>
                            <button v-if="editingId === row.id" type="button" class="btn btn-sm btn-outline-secondary me-1" @click="editingId = null">Cancel</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(row)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>
<script setup>
import LunaTable from '@/Components/LunaTable.vue'
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    title: String,
    rows: Array,
    storeRoute: String,
    updateRoute: String,
    destroyRoute: String,
})
const page = usePage()
const flash = computed(() => page.props.flash || {})
const name = ref('')
const editingId = ref(null)
const editName = ref('')
const formError = computed(() => page.props.errors?.name || '')

const save = () => router.post(route(props.storeRoute), { name: name.value }, {
    onSuccess: () => { name.value = '' },
})
const startEdit = (row) => {
    editingId.value = row.id
    editName.value = row.name
}
const update = (row) => router.put(route(props.updateRoute, row.id), { name: editName.value }, {
    onSuccess: () => { editingId.value = null },
})
const remove = (row) => {
    if (confirm(`Delete "${row.name}"?`)) {
        router.delete(route(props.destroyRoute, row.id))
    }
}
</script>
