<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import LunaTable from '@/Components/LunaTable.vue';
import OpsNav from '@/Components/Platform/OpsNav.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import Layout from './Layout.vue';

defineProps({ rows: Array, central: Object, summary: Object });
const form = useForm({ password: '', company_id: '', central: false, all: false });

function run(extra) {
    form.central = !!extra.central;
    form.all = !!extra.all;
    form.company_id = extra.company_id || '';
    form.post('/platform/schema');
}
</script>

<template>
    <Head title="Schema" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Schema sync</h4>
                <p class="text-muted mb-0 font-size-13">{{ summary.in_sync }} in sync · {{ summary.needs_update }} behind · {{ summary.db_missing }} missing</p>
            </div>
            <div class="page-title-right">
                <form class="d-flex flex-wrap align-items-end gap-2" @submit.prevent>
                    <label class="pf-field mb-0" style="min-width: 11rem;">
                        <span>Platform password</span>
                        <input v-model="form.password" type="password" placeholder="Your password" autocomplete="current-password">
                    </label>
                    <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="form.processing" @click="run({ central: true })">Migrate central</button>
                    <button type="button" class="btn btn-primary btn-sm" :disabled="form.processing" @click="run({ all: true })">Migrate every pharmacy</button>
                </form>
            </div>
        </template>

        <OpsNav />

        <section class="pf-card mb-3">
            <div class="pf-card-body d-flex flex-wrap justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">Central registry</div>
                    <div class="small text-muted">{{ (central.pending || []).join(', ') || 'No pending migrations' }}</div>
                </div>
                <StatusBadge :status="central.status" kind="provision" />
            </div>
        </section>

        <section class="pf-card">
            <LunaTable title="Pharmacy schemas">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>Status</th>
                            <th>Pending</th>
                            <th class="text-end" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!rows.length">
                            <td colspan="4" class="py-5 text-center text-muted">No pharmacies to compare.</td>
                        </tr>
                        <tr v-for="row in rows" :key="row.company_id">
                            <td class="fw-semibold">{{ row.name }}</td>
                            <td><StatusBadge :status="row.status" kind="provision" /></td>
                            <td class="small text-muted">{{ (row.pending || []).join(', ') || '—' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" :disabled="!form.password || form.processing" @click="run({ company_id: row.company_id })">
                                    Migrate
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
