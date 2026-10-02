<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">{{ title }}</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary btn-sm" @click="open = true">Add {{ title.slice(0, -1) }}</button>
            </div>
        </template>
        <LunaTable :title="title">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-end">Medicines</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.id">
                        <td>{{ row.name }}</td>
                        <td class="text-end">{{ row.medicines_count }}</td>
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({ title: String, kind: String, rows: Array, storeRoute: String })
const name = ref('')
const open = ref(false)
const save = () => router.post(route(props.storeRoute), { name: name.value }, { onSuccess: () => { name.value = ''; open.value = false } })
</script>
