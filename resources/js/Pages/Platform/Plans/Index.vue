<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from '../Layout.vue';

const props = defineProps({ plans: Array, q: String, status: String });
const form = useForm({ name: '', code: '', monthly_amount: 0, currency: 'BDT', features_text: '' });
const filters = reactive({ q: props.q || '', status: props.status || '' });
function apply() {
    router.get('/platform/plans', filters, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Plans" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Plans</h1>
                <p class="mt-1 text-sm text-[#71717a]">Pricing a pharmacy can be subscribed to.</p>
            </div>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="apply">
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Search</span><input v-model="filters.q" class="w-48"></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Status</span>
                    <select v-model="filters.status">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </label>
                <button class="rounded-md border border-[#e4e4e7] px-3 py-2 text-sm">Filter</button>
            </form>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="form.post('/platform/plans')">
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Name</span><input v-model="form.name" class="w-44" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Code</span><input v-model="form.code" class="w-32" placeholder="starter" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Monthly</span><input v-model="form.monthly_amount" type="number" step="0.01" class="w-28" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Currency</span><input v-model="form.currency" class="w-20" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Features</span><textarea v-model="form.features_text" class="w-56" rows="2" placeholder="One feature per line" /></label>
                <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">Save</button>
            </form>
            <LunaTable title="Plans">
<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!plans.length">
                        <td colspan="4" class="py-10 text-center text-sm text-[#71717a]">No plans yet.</td>
                    </tr>
                    <tr v-for="plan in plans" :key="plan.id">
                        <td class="font-medium">{{ plan.name }} <span class="font-normal text-[#71717a]">{{ plan.code }}</span></td>
                        <td>
                            <div>{{ plan.monthly_amount }} {{ plan.currency }}</div>
                            <div v-if="plan.features?.length" class="text-[12px] text-[#71717a]">{{ plan.features.join(', ') }}</div>
                        </td>
                        <td>{{ plan.status }} · {{ plan.subscriptions_count }} subscriptions</td>
                        <td class="text-right">
                            <a :href="`/platform/plans/${plan.id}/edit`" class="text-sm font-medium text-[#17342b]">Edit</a>
                            <button class="ml-3 text-sm text-[#71717a]" @click="$inertia.post(`/platform/plans/${plan.id}/archive`)">{{ plan.status === 'active' ? 'Archive' : 'Restore' }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
</LunaTable>
        </section>
    </Layout>
</template>
