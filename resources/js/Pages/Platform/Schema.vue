<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, useForm } from '@inertiajs/vue3';
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
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-[#f4f4f5] px-5 py-4">
                <div>
                    <h1>Schema</h1>
                    <p class="mt-1 text-sm text-[#71717a]">{{ summary.in_sync }} in sync · {{ summary.needs_update }} behind · {{ summary.db_missing }} missing</p>
                </div>
                <form class="flex flex-wrap items-end gap-2" @submit.prevent>
                    <label class="text-sm"><span class="mb-1 block text-[#71717a]">Password</span>
                        <input v-model="form.password" type="password" placeholder="Your password">
                    </label>
                    <button type="button" class="rounded-md border border-[#e4e4e7] px-3 py-2 text-sm" @click="run({ central: true })">Migrate central</button>
                    <button type="button" class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white" @click="run({ all: true })">Migrate every pharmacy</button>
                </form>
            </div>
            <p class="border-b border-[#f4f4f5] px-5 py-3 text-sm">Central database: {{ central.status }} <span v-if="central.pending?.length" class="text-[#71717a]">{{ central.pending.join(', ') }}</span></p>
            <LunaTable title="Schema">
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Pharmacy</th>
                        <th>Status</th>
                        <th>Pending</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!rows.length">
                        <td colspan="4" class="py-10 text-center text-sm text-[#71717a]">No pharmacies to compare.</td>
                    </tr>
                    <tr v-for="row in rows" :key="row.company_id">
                        <td class="font-medium">{{ row.name }}</td>
                        <td>{{ row.status }}</td>
                        <td class="text-[#71717a]">{{ (row.pending || []).join(', ') || '—' }}</td>
                        <td class="text-right"><button class="text-sm font-medium text-[#17342b]" @click="run({ company_id: row.company_id })">Migrate</button></td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
        </section>
    </Layout>
</template>
