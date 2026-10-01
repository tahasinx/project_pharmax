<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from '../Layout.vue';

const props = defineProps({
    companies: Object,
    q: String,
    provision: String,
    baseDomain: String,
});
const filters = reactive({ q: props.q || '', provision: props.provision || '' });

function apply() {
    router.get('/platform/companies', filters, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Pharmacies" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#f4f4f5] px-5 py-4">
                <div>
                    <h1>Pharmacies</h1>
                    <p class="mt-1 text-sm text-[#71717a]">{{ companies.total }} on this platform</p>
                </div>
                <Link href="/platform/companies/create" class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">New pharmacy</Link>
            </div>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="apply">
                <label class="text-sm">
                    <span class="mb-1 block text-[#71717a]">Search</span>
                    <input v-model="filters.q" class="w-64" placeholder="Name, slug, database, email">
                </label>
                <label class="text-sm">
                    <span class="mb-1 block text-[#71717a]">Provision</span>
                    <select v-model="filters.provision" class="min-w-36">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="running">Running</option>
                        <option value="active">Active</option>
                        <option value="degraded">Degraded</option>
                        <option value="failed">Failed</option>
                    </select>
                </label>
                <button class="rounded-md border border-[#e4e4e7] px-3 py-2 text-sm">Filter</button>
                <button v-if="q || provision" type="button" class="text-sm text-[#71717a]" @click="filters.q = ''; filters.provision = ''; apply()">Clear</button>
            </form>
            <LunaTable title="Companies">
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Database</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!companies.data.length">
                        <td colspan="5" class="py-10 text-center text-sm text-[#71717a]">No pharmacies match.</td>
                    </tr>
                    <tr v-for="company in companies.data" :key="company.id">
                        <td>
                            <Link :href="`/platform/companies/${company.id}`" class="font-medium text-[#17342b]">{{ company.name }}</Link>
                            <p class="text-[12px] text-[#71717a]">{{ company.email || company.admin_email || 'No contact email' }}</p>
                        </td>
                        <td>{{ company.slug }}.{{ baseDomain }}</td>
                        <td class="font-mono text-[13px]">{{ company.database_name }}</td>
                        <td>{{ company.status }} · {{ company.provision_status }}</td>
                        <td class="space-x-3 text-right text-sm">
                            <Link :href="`/platform/companies/${company.id}`" class="font-medium text-[#17342b]">Profile</Link>
                            <Link :href="`/platform/companies/${company.id}/provision`" class="text-[#71717a]">Provision</Link>
                            <Link :href="`/platform/companies/${company.id}/edit`" class="text-[#71717a]">Edit</Link>
                            <Link :href="`/platform/subscriptions?company=${company.id}`" class="text-[#71717a]">Subscribe</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
            <div v-if="companies.last_page > 1" class="flex justify-end gap-2 border-t border-[#f4f4f5] px-5 py-3 text-sm">
                <Link v-if="companies.prev_page_url" :href="companies.prev_page_url">Previous</Link>
                <span class="text-[#71717a]">{{ companies.current_page }} / {{ companies.last_page }}</span>
                <Link v-if="companies.next_page_url" :href="companies.next_page_url">Next</Link>
            </div>
        </section>
    </Layout>
</template>
