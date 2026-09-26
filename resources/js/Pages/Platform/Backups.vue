<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from './Layout.vue';

defineProps({ rows: Array });
</script>

<template>
    <Head title="Backups" />
    <Layout>
        <h1 class="text-2xl font-semibold">Backups</h1>
        <section v-for="row in rows" :key="row.id" class="mt-4 rounded bg-white p-3 text-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-medium">{{ row.name }}</h2>
                <button class="rounded bg-[#1f3d32] px-3 py-1 text-white" @click="$inertia.post(`/platform/backups/${row.id}`)">Create</button>
            </div>
            <ul class="mt-2">
                <li v-for="file in row.files" :key="file.name" class="flex gap-3 py-1">
                    <span>{{ file.name }}</span>
                    <span>{{ file.created_at }}</span>
                    <Link :href="`/platform/backups/${row.id}/${file.name}`" class="underline">Download</Link>
                    <button class="underline" @click="$inertia.delete(`/platform/backups/${row.id}/${file.name}`)">Delete</button>
                </li>
            </ul>
        </section>
    </Layout>
</template>
