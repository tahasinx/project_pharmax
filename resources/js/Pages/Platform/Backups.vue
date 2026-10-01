<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, Link } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({ rows: Array });
</script>

<template>
    <Head title="Backups" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Backups</h1>
                <p class="mt-1 text-sm text-[#71717a]">Database dumps for each pharmacy. Create one only after a pharmacy exists.</p>
            </div>
            <LunaTable title="Backups">
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Pharmacy</th>
                        <th>Latest files</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!rows.length">
                        <td colspan="3" class="py-10 text-center text-sm text-[#71717a]">No pharmacies yet, so there is nothing to back up.</td>
                    </tr>
                    <tr v-for="row in rows" :key="row.id">
                        <td class="font-medium">{{ row.name }}</td>
                        <td>
                            <p v-if="!row.files.length" class="text-[#71717a]">No backups</p>
                            <ul v-else class="space-y-1">
                                <li v-for="file in row.files" :key="file.name" class="flex flex-wrap items-center gap-3">
                                    <span class="font-mono text-[13px]">{{ file.name }}</span>
                                    <span class="text-[#71717a]">{{ file.created_at }}</span>
                                    <Link :href="`/platform/backups/${row.id}/${file.name}`" class="text-sm font-medium text-[#17342b]">Download</Link>
                                    <button class="text-sm text-[#b91c1c]" @click="$inertia.delete(`/platform/backups/${row.id}/${file.name}`)">Delete</button>
                                </li>
                            </ul>
                        </td>
                        <td class="text-right">
                            <button class="rounded-md bg-[#17342b] px-3 py-1.5 text-sm font-medium text-white" @click="$inertia.post(`/platform/backups/${row.id}`)">Create</button>
                        </td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
        </section>
    </Layout>
</template>
