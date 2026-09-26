<script setup>
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
        <h1 class="text-2xl font-semibold">Schema</h1>
        <p class="mt-1 text-sm">{{ summary.in_sync }} in sync · {{ summary.needs_update }} behind · {{ summary.db_missing }} missing</p>
        <div class="mt-4 flex flex-wrap items-end gap-2">
            <input v-model="form.password" type="password" class="rounded border px-2 py-1" placeholder="Your password">
            <button class="rounded border px-3 py-1 text-sm" @click="run({ central: true })">Migrate central</button>
            <button class="rounded border px-3 py-1 text-sm" @click="run({ all: true })">Migrate every pharmacy</button>
        </div>
        <div class="mt-4 rounded bg-white p-3 text-sm">Central: {{ central.status }} <span v-if="central.pending?.length">({{ central.pending.join(', ') }})</span></div>
        <table class="mt-4 w-full bg-white text-sm">
            <tr v-for="row in rows" :key="row.company_id" class="border-t">
                <td class="p-2">{{ row.name }}</td>
                <td class="p-2">{{ row.status }}</td>
                <td class="p-2">{{ (row.pending || []).join(', ') }}</td>
                <td class="p-2"><button class="underline" @click="run({ company_id: row.company_id })">Migrate</button></td>
            </tr>
        </table>
    </Layout>
</template>
