<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({ status: Object, settings: Object });
const form = useForm({ password: '', reason: '', confirm_text: '', allow_redeploy: false });
</script>

<template>
    <Head title="Deploy" />
    <Layout>
        <h1 class="text-2xl font-semibold">Promote to production</h1>
        <p class="mt-1 text-sm text-[#5c6b63]">{{ settings.owner }}/{{ settings.repo }} · {{ settings.base }} → {{ settings.prod }} · {{ settings.workflow }}</p>
        <p class="mt-3 text-sm">{{ status.message }}</p>
        <p v-if="status.base_sha" class="mt-1 text-sm">{{ settings.base }} {{ status.base_sha }} · {{ settings.prod }} {{ status.prod_sha }} · ahead {{ status.ahead_by }} · behind {{ status.behind_by }}</p>
        <ul class="mt-3 text-sm">
            <li v-for="commit in status.commits || []" :key="commit.sha" class="border-t py-1">{{ commit.sha }} {{ commit.message }}</li>
        </ul>
        <form class="mt-4 max-w-lg space-y-2" @submit.prevent="form.post('/platform/deploy')">
            <input v-model="form.reason" class="w-full rounded border px-2 py-1" placeholder="Reason">
            <input v-model="form.confirm_text" class="w-full rounded border px-2 py-1" placeholder="Type DEPLOY" required>
            <input v-model="form.password" type="password" class="w-full rounded border px-2 py-1" placeholder="Your password" required>
            <label class="flex items-center gap-2 text-sm"><input v-model="form.allow_redeploy" type="checkbox"> Allow redeploy when already in sync</label>
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white" :disabled="!settings.configured">Promote</button>
        </form>
        <ul class="mt-4 text-sm">
            <li v-for="run in status.runs || []" :key="run.url">
                <a :href="run.url" class="underline">{{ run.name }}</a> {{ run.status }} {{ run.conclusion }}
            </li>
        </ul>
    </Layout>
</template>
