<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import FormScreen from '@/Components/FormScreen.vue';
import OpsNav from '@/Components/Platform/OpsNav.vue';
import Layout from './Layout.vue';

const props = defineProps({ catalog: Array, output: String, ran: String, exitCode: Number });
const form = useForm({ password: '', command_line: '' });
const showCatalog = ref(false);
const catalogQuery = ref('');

const filteredCatalog = computed(() => {
    const q = catalogQuery.value.trim().toLowerCase();
    if (!q) return props.catalog || [];
    return (props.catalog || []).filter((command) => (
        command.name.toLowerCase().includes(q) || String(command.description || '').toLowerCase().includes(q)
    ));
});

const pick = (name) => {
    form.command_line = name;
    showCatalog.value = false;
};
</script>

<template>
    <Head title="Commands" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Artisan commands</h4>
                <p class="text-muted mb-0 font-size-13">Runs one Artisan command on this host. Shell operators are rejected.</p>
            </div>
            <div class="page-title-right">
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="showCatalog = true">
                    <i class="bi bi-journal-code me-1" />
                    Browse catalog
                </button>
            </div>
        </template>
        <OpsNav />

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <div>
                    <h2>Run command</h2>
                    <p>One Artisan invocation — no shell pipes or chaining.</p>
                </div>
            </div>
            <div class="pf-card-body">
                <form class="pf-inline-actions" @submit.prevent="form.post('/platform/commands')">
                    <label class="pf-field" style="flex: 2 1 16rem;">
                        <span>Command</span>
                        <input v-model="form.command_line" class="font-monospace" placeholder="tenants:migrate --all" required autocomplete="off">
                    </label>
                    <label class="pf-field">
                        <span>Platform password</span>
                        <input v-model="form.password" type="password" required autocomplete="current-password">
                    </label>
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Running…' : 'Run' }}
                    </button>
                </form>
                <p v-if="form.errors.command_line || form.errors.password" class="text-danger small mt-2 mb-0">
                    {{ form.errors.command_line || form.errors.password }}
                </p>
            </div>
            <pre v-if="output" class="pf-console border-top">{{ ran }} (exit {{ exitCode }})
{{ output }}</pre>
        </section>

        <FormScreen v-if="showCatalog" title="Command catalog" size="fullscreen" @close="showCatalog = false">
            <div class="mb-3">
                <label class="pf-field mb-0">
                    <span>Search commands</span>
                    <input v-model="catalogQuery" type="search" placeholder="migrate, cache, queue…">
                </label>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Command</th>
                            <th>Description</th>
                            <th />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="command in filteredCatalog" :key="command.name">
                            <td class="font-monospace small">{{ command.name }}</td>
                            <td class="small text-muted">{{ command.description }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" @click="pick(command.name)">Use</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
            </template>
        </FormScreen>
    </Layout>
</template>
