<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ plan: Object });
const form = useForm({
    name: props.plan.name,
    monthly_amount: props.plan.monthly_amount,
    currency: props.plan.currency,
    status: props.plan.status,
});
</script>

<template>
    <Head title="Edit plan" />
    <Layout>
        <h1 class="text-2xl font-semibold">{{ plan.code }}</h1>
        <form class="mt-4 max-w-lg space-y-2" @submit.prevent="form.put(`/platform/plans/${plan.id}`)">
            <input v-model="form.name" class="w-full rounded border px-2 py-1" required>
            <input v-model="form.monthly_amount" type="number" step="0.01" class="w-full rounded border px-2 py-1" required>
            <input v-model="form.currency" class="w-full rounded border px-2 py-1" required>
            <select v-model="form.status" class="w-full rounded border px-2 py-1">
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </select>
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white">Save</button>
        </form>
    </Layout>
</template>
