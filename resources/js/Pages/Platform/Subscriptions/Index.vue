<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineProps({ subscriptions: Array, companies: Array, plans: Array });
const form = useForm({ company_id: '', platform_plan_id: '', starts_on: '', ends_on: '' });
</script>

<template>
    <Head title="Subscriptions" />
    <Layout>
        <h1 class="text-2xl font-semibold">Subscriptions</h1>
        <form class="mt-4 flex flex-wrap gap-2" @submit.prevent="form.post('/platform/subscriptions')">
            <select v-model="form.company_id" class="rounded border px-2 py-1" required>
                <option value="">Pharmacy</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
            </select>
            <select v-model="form.platform_plan_id" class="rounded border px-2 py-1" required>
                <option value="">Plan</option>
                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
            </select>
            <input v-model="form.starts_on" type="date" class="rounded border px-2 py-1" required>
            <input v-model="form.ends_on" type="date" class="rounded border px-2 py-1">
            <button class="rounded bg-[#1f3d32] px-3 py-1 text-sm text-white">Add</button>
        </form>
        <table class="mt-4 w-full bg-white text-sm">
            <tr v-for="row in subscriptions" :key="row.id" class="border-t">
                <td class="p-2">{{ row.company?.name }}</td>
                <td class="p-2">{{ row.plan?.name }}</td>
                <td class="p-2">{{ row.amount }} · {{ row.starts_on }} → {{ row.ends_on || 'open' }}</td>
                <td class="p-2">
                    <form class="flex gap-1" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/upgrade`, { platform_plan_id: $event.target.plan.value })">
                        <select name="plan" class="rounded border px-1">
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id" :selected="plan.id === row.platform_plan_id">{{ plan.name }}</option>
                        </select>
                        <button class="underline">Upgrade</button>
                    </form>
                    <button class="underline" @click="$inertia.post(`/platform/subscriptions/${row.id}/invoice`)">Raise invoice</button>
                    <form class="mt-1 flex gap-1" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/expiry`, { ends_on: $event.target.ends_on.value })">
                        <input name="ends_on" type="date" class="rounded border px-1" :value="row.ends_on">
                        <button class="underline">Save expiry</button>
                    </form>
                </td>
            </tr>
        </table>
    </Layout>
</template>
