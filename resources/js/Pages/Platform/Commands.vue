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
        <OpsNav />
        <div class="pf-page-head">
            <div>
                <h1>Artisan commands</h1>
                <p class="pf-page-sub">Runs one Artisan command on this host. Shell operators are rejected.</p>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="showCatalog = true">
                <i class="bi bi-journal-code me-1" />
                Browse catalog
            </button>
        </div>

        <section class="pf-card mb-3">
            <div class="pf-card-body">
                <form class="row g-2 align-items-end" @submit.prevent="form.post('/platform/commands')">
                    <div class="col-lg-7">
                        <label class="pf-field mb-0">
                            <span>Command</span>
                            <input v-model="form.command_line" class="font-monospace" placeholder="tenants:migrate --all" required autocomplete="off">
                        </label>
                    </div>
                    <div class="col-lg-3">
                        <label class="pf-field mb-0">
                            <span>Platform password</span>
                            <input v-model="form.password" type="password" required autocomplete="current-password">
                        </label>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-primary w-100" :disabled="form.processing">Run</button>
                    </div>
                </form>
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
