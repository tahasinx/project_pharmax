<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">{{ title }}</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary btn-sm" @click="open = true">Add {{ title.slice(0, -1) }}</button>
            </div>
        </template>
        <div v-if="flash.error" class="alert alert-danger">{{ flash.error }}</div>
        <div v-if="flash.success" class="alert alert-success">{{ flash.success }}</div>
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
        <div v-if="open" class="screen-modal" @click.self="open = false">
            <form class="screen-modal-panel" @submit.prevent="save">
                <div class="screen-modal-head">
                    <h4 class="mb-0">Add {{ title.slice(0, -1) }}</h4>
                    <button type="button" class="btn btn-light btn-sm" @click="open = false">Close</button>
                </div>
                <div class="screen-modal-body">
                    <label class="form-label" for="catalog-name">Name</label>
                    <input id="catalog-name" v-model="name" class="form-control" required>
                    <div v-if="formError" class="text-danger small mt-2">{{ formError }}</div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light" @click="open = false">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    title: String,
    kind: String,
    rows: Array,
    storeRoute: String,
    updateRoute: String,
    destroyRoute: String,
})
const page = usePage()
const flash = computed(() => page.props.flash || {})
const formError = computed(() => page.props.errors?.name || '')
const name = ref('')
const open = ref(false)
const editingId = ref(null)
const editName = ref('')

const save = () => router.post(route(props.storeRoute), { name: name.value }, {
    onSuccess: () => { name.value = ''; open.value = false },
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
