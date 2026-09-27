<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ plan: Object });
const form = useForm({
    name: props.plan.name,
    monthly_amount: props.plan.monthly_amount,
    currency: props.plan.currency,
    status: props.plan.status,
    features_text: (props.plan.features || []).join('\n'),
});
</script>

<template>
    <Head title="Edit plan" />
    <Layout>
        <h1 class="text-2xl font-semibold">{{ plan.code }}</h1>
        <form class="mt-4 max-w-lg space-y-3" @submit.prevent="form.put(`/platform/plans/${plan.id}`)">
            <label class="block text-sm"><span class="mb-1 block">Name</span><input v-model="form.name" class="w-full" required></label>
            <label class="block text-sm"><span class="mb-1 block">Monthly amount</span><input v-model="form.monthly_amount" type="number" step="0.01" class="w-full" required></label>
            <label class="block text-sm"><span class="mb-1 block">Currency</span><input v-model="form.currency" class="w-full" required></label>
            <label class="block text-sm"><span class="mb-1 block">Status</span>
                <select v-model="form.status" class="w-full">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
            </label>
            <label class="block text-sm"><span class="mb-1 block">Features, one per line</span><textarea v-model="form.features_text" class="w-full" rows="4" /></label>
            <button class="rounded bg-[#17342b] px-3 py-2 text-sm text-white">Save</button>
        </form>
    </Layout>
</template>
