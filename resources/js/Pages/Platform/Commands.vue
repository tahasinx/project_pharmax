<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({ catalog: Array, output: String, ran: String, exitCode: Number });
const form = useForm({ password: '', command_line: '' });
</script>

<template>
    <Head title="Commands" />
    <Layout>
        <h1 class="text-2xl font-semibold">Server commands</h1>
        <p class="mt-1 text-sm text-[#5c6b63]">Runs one Artisan command on this host. Shell operators are rejected.</p>
        <form class="mt-4 max-w-xl space-y-2" @submit.prevent="form.post('/platform/commands')">
            <input v-model="form.command_line" class="w-full rounded border px-2 py-1 font-mono" placeholder="migrate --force" required>
            <input v-model="form.password" type="password" class="w-full rounded border px-2 py-1" placeholder="Your password" required>
            <button class="rounded bg-[#1f3d32] px-3 py-2 text-sm text-white">Run</button>
        </form>
        <pre v-if="output" class="mt-4 overflow-auto rounded bg-[#1c2b24] p-3 text-xs text-[#f4f1ea]">{{ ran }} ({{ exitCode }}){{ '\n' }}{{ output }}</pre>
        <ul class="mt-6 max-h-80 overflow-auto rounded bg-white text-sm">
            <li v-for="command in catalog" :key="command.name" class="border-t px-3 py-1">
                <button class="font-mono" @click="form.command_line = command.name">{{ command.name }}</button>
                <span class="ml-2 text-[#5c6b63]">{{ command.description }}</span>
            </li>
        </ul>
    </Layout>
</template>
