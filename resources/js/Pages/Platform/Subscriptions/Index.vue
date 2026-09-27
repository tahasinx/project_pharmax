<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ subscriptions: Array, companies: Array, plans: Array, companyId: [String, Number] });
const form = useForm({ company_id: props.companyId || '', platform_plan_id: '', starts_on: '', ends_on: '' });
</script>

<template>
    <Head title="Subscriptions" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Subscriptions</h1>
                <p class="mt-1 text-sm text-[#71717a]">{{ subscriptions.length }} subscription{{ subscriptions.length === 1 ? '' : 's' }}</p>
            </div>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="form.post('/platform/subscriptions')">
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Pharmacy</span>
                    <select v-model="form.company_id" class="min-w-40" required>
                        <option value="">Select</option>
                        <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                    </select>
                </label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Plan</span>
                    <select v-model="form.platform_plan_id" class="min-w-36" required>
                        <option value="">Select</option>
                        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                    </select>
                </label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Starts</span><input v-model="form.starts_on" type="date" required></label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Ends</span><input v-model="form.ends_on" type="date"></label>
                <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">Add</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Pharmacy</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!subscriptions.length">
                        <td colspan="5" class="py-10 text-center text-sm text-[#71717a]">No subscriptions yet. Add a pharmacy and a plan first.</td>
                    </tr>
                    <tr v-for="row in subscriptions" :key="row.id">
                        <td class="font-medium">{{ row.company?.name }}</td>
                        <td>{{ row.plan?.name }}</td>
                        <td>{{ row.amount }}</td>
                        <td>{{ row.starts_on }} → {{ row.ends_on || 'open' }}</td>
                        <td class="space-y-2 text-right">
                            <form class="inline-flex items-center gap-2" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/upgrade`, { platform_plan_id: $event.target.plan.value })">
                                <select name="plan">
                                    <option v-for="plan in plans" :key="plan.id" :value="plan.id" :selected="plan.id === row.platform_plan_id">{{ plan.name }}</option>
                                </select>
                                <button class="text-sm font-medium text-[#17342b]">Upgrade</button>
                            </form>
                            <form class="inline-flex items-center gap-2" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/expiry`, { ends_on: $event.target.ends_on.value || null })">
                                <input name="ends_on" type="date" :value="row.ends_on || ''">
                                <button class="text-sm text-[#71717a]">Set expiry</button>
                            </form>
                            <button class="ml-3 text-sm text-[#71717a]" @click="$inertia.post(`/platform/subscriptions/${row.id}/invoice`)">Invoice</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </Layout>
</template>
