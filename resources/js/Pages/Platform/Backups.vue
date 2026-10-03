<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import LunaTable from '@/Components/LunaTable.vue';
import OpsNav from '@/Components/Platform/OpsNav.vue';
import Layout from './Layout.vue';

defineProps({ rows: Array });
</script>

<template>
    <Head title="Backups" />
    <Layout>
        <OpsNav />
        <div class="pf-page-head">
            <div>
                <h1>Database backups</h1>
                <p class="pf-page-sub">Create and download dumps for each pharmacy tenant database.</p>
            </div>
        </div>

        <section class="pf-card">
            <LunaTable title="Backups">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>Latest files</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!rows.length">
                            <td colspan="3" class="py-5 text-center text-muted">No pharmacies yet, so there is nothing to back up.</td>
                        </tr>
                        <tr v-for="row in rows" :key="row.id">
                            <td class="fw-semibold">{{ row.name }}</td>
                            <td>
                                <p v-if="!row.files.length" class="text-muted mb-0 small">No backups</p>
                                <ul v-else class="list-unstyled mb-0 small">
                                    <li v-for="file in row.files" :key="file.name" class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="font-monospace">{{ file.name }}</span>
                                        <span class="text-muted">{{ file.created_at }}</span>
                                        <Link :href="`/platform/backups/${row.id}/${file.name}`" class="btn btn-link btn-sm px-0">Download</Link>
                                        <button class="btn btn-link btn-sm px-0 text-danger" @click="router.delete(`/platform/backups/${row.id}/${file.name}`)">Delete</button>
                                    </li>
                                </ul>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-primary btn-sm" @click="router.post(`/platform/backups/${row.id}`)">Create</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
