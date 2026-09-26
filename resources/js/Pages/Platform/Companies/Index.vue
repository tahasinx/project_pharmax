<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineProps({
    companies: Array,
    baseDomain: String,
});
</script>

<template>
    <Head title="Pharmacies" />
    <Layout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Pharmacies</h1>
            <Link href="/platform/companies/create" class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white">New pharmacy</Link>
        </div>
        <table class="mt-4 w-full border-collapse bg-white text-sm">
            <thead class="bg-[#efeae1] text-left">
                <tr>
                    <th class="p-2">Name</th>
                    <th class="p-2">Host</th>
                    <th class="p-2">Database</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="company in companies" :key="company.id" class="border-t">
                    <td class="p-2"><Link :href="`/platform/companies/${company.id}`" class="underline">{{ company.name }}</Link></td>
                    <td class="p-2">{{ company.slug }}.{{ baseDomain }}</td>
                    <td class="p-2">{{ company.database_name }}</td>
                    <td class="p-2">{{ company.status }} / {{ company.provision_status }}</td>
                </tr>
            </tbody>
        </table>
    </Layout>
</template>
