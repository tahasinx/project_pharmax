<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">{{ title }}</h4>
        </template>
        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-2 align-items-center" @submit.prevent="save">
                    <div class="col-md-8 col-lg-6">
                        <input v-model="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-content-save-outline me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
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
    </AuthenticatedLayout>
</template>
<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({ title: String, kind: String, rows: Array, storeRoute: String })
const name = ref('')
const save = () => router.post(route(props.storeRoute), { name: name.value }, { onSuccess: () => { name.value = '' } })
</script>
