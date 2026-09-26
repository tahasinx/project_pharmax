<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineProps({ plans: Array });
const form = useForm({ name: '', code: '', monthly_amount: 0, currency: 'BDT' });
</script>

<template>
    <Head title="Plans" />
    <Layout>
        <h1 class="text-2xl font-semibold">Plans</h1>
        <form class="mt-4 flex flex-wrap gap-2" @submit.prevent="form.post('/platform/plans')">
            <input v-model="form.name" class="rounded border px-2 py-1" placeholder="Name" required>
            <input v-model="form.code" class="rounded border px-2 py-1" placeholder="code" required>
            <input v-model="form.monthly_amount" type="number" step="0.01" class="w-28 rounded border px-2 py-1" required>
            <input v-model="form.currency" class="w-20 rounded border px-2 py-1" required>
            <button class="rounded bg-[#1f3d32] px-3 py-1 text-sm text-white">Save</button>
        </form>
        <table class="mt-4 w-full bg-white text-sm">
            <tr v-for="plan in plans" :key="plan.id" class="border-t">
                <td class="p-2">{{ plan.name }} ({{ plan.code }})</td>
                <td class="p-2">{{ plan.monthly_amount }} {{ plan.currency }}</td>
                <td class="p-2">{{ plan.status }} · {{ plan.subscriptions_count }} subscriptions</td>
                <td class="p-2">
                    <a :href="`/platform/plans/${plan.id}/edit`" class="underline">Edit</a>
                    <button class="ml-2 underline" @click="$inertia.post(`/platform/plans/${plan.id}/archive`)">{{ plan.status === 'active' ? 'Archive' : 'Restore' }}</button>
                </td>
            </tr>
        </table>
    </Layout>
</template>
