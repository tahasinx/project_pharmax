<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({ catalog: Array, output: String, ran: String, exitCode: Number });
const form = useForm({ password: '', command_line: '' });
</script>

<template>
    <Head title="Commands" />
    <Layout>
        <section class="overflow-hidden rounded-xl border border-[#e4e4e7] bg-white">
            <div class="border-b border-[#f4f4f5] px-5 py-4">
                <h1>Commands</h1>
                <p class="mt-1 text-sm text-[#71717a]">Runs one Artisan command on this host. Shell operators are rejected.</p>
            </div>
            <form class="flex flex-wrap items-end gap-3 border-b border-[#f4f4f5] px-5 py-4" @submit.prevent="form.post('/platform/commands')">
                <label class="min-w-64 flex-1 text-sm"><span class="mb-1 block text-[#71717a]">Command</span>
                    <input v-model="form.command_line" class="w-full font-mono" placeholder="migrate --force" required>
                </label>
                <label class="text-sm"><span class="mb-1 block text-[#71717a]">Password</span>
                    <input v-model="form.password" type="password" placeholder="Your password" required>
                </label>
                <button class="rounded-md bg-[#17342b] px-3 py-2 text-sm font-medium text-white">Run</button>
            </form>
            <pre v-if="output" class="overflow-auto border-b border-[#f4f4f5] bg-[#18181b] p-4 text-xs text-[#e4e4e7]">{{ ran }} ({{ exitCode }}){{ '\n' }}{{ output }}</pre>
            <table>
                <thead>
                    <tr>
                        <th>Command</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="command in catalog" :key="command.name">
                        <td><button class="font-mono text-[13px] text-[#17342b]" @click="form.command_line = command.name">{{ command.name }}</button></td>
                        <td class="text-[#71717a]">{{ command.description }}</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </Layout>
</template>
