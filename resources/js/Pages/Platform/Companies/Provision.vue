<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    hostEnabled: Boolean,
});

const stepOrder = ['validate', 'registry', 'database', 'schema', 'vhost', 'ssl', 'ready'];
const state = ref({
    provision_status: props.company.provision_status,
    provision_step: props.company.provision_step,
    provision_error: props.company.provision_error,
    vhost_status: props.company.vhost_status,
    ssl_status: props.company.ssl_status,
    log: props.company.provision_log || [],
    host: props.host,
});
let timer;

const currentIdx = computed(() => stepOrder.indexOf(String(state.value.provision_step || '').toLowerCase()));
const finished = computed(() => ['active', 'failed', 'degraded'].includes(state.value.provision_status));

async function pull() {
    try {
        const response = await fetch(`/platform/companies/${props.company.id}/provision/log`, {
            headers: { Accept: 'application/json' },
        });
        if (response.ok) {
            state.value = await response.json();
        }
    } catch {
        // keep polling
    }
    if (!finished.value) {
        timer = setTimeout(pull, 1500);
    }
}

onMounted(pull);
onUnmounted(() => clearTimeout(timer));
</script>

<template>
    <Head title="Provisioning" />
    <Layout>
        <div class="pf-page-head">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="mb-0">Provisioning {{ company.name }}</h1>
                    <StatusBadge :status="state.provision_status" kind="provision" />
                </div>
                <p class="pf-page-sub">Live progress for tenant database, nginx vhost, and SSL.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <Link :href="`/platform/companies/${company.id}`" class="btn btn-outline-secondary btn-sm">Pharmacy record</Link>
                <button
                    v-if="['failed', 'degraded'].includes(state.provision_status)"
                    type="button"
                    class="btn btn-warning btn-sm"
                    @click="router.post(`/platform/companies/${company.id}/provision/retry`)"
                >
                    Retry
                </button>
                <a v-if="state.provision_status === 'active'" :href="state.host || host" class="btn btn-success btn-sm" target="_blank" rel="noopener">
                    Open tenant
                </a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <section class="pf-card h-100">
                    <div class="pf-card-head"><h2>Steps</h2></div>
                    <div class="pf-card-body">
                        <ol class="pf-steps">
                            <li
                                v-for="(label, idx) in ['Validate', 'Registry', 'Database', 'Schema', 'Vhost', 'SSL', 'Ready']"
                                :key="label"
                                class="pf-step"
                                :class="{
                                    'is-done': currentIdx > idx || state.provision_status === 'active',
                                    'is-current': currentIdx === idx && !finished,
                                }"
                            >
                                <span class="pf-step-dot" />
                                <span>{{ label }}</span>
                            </li>
                        </ol>
                        <p v-if="!hostEnabled" class="small text-warning mt-3 mb-0">Host provisioner disabled on this machine — database-only mode.</p>
                    </div>
                </section>
            </div>
            <div class="col-lg-8">
                <section class="pf-card h-100">
                    <div class="pf-card-head"><h2>Status</h2></div>
                    <div class="pf-card-body small">
                        <p class="mb-1"><strong>Slug:</strong> <code>{{ company.slug }}</code></p>
                        <p class="mb-1"><strong>Database:</strong> <code>{{ company.database_name }}</code></p>
                        <p class="mb-1"><strong>Step:</strong> {{ state.provision_step || '—' }}</p>
                        <p class="mb-1"><strong>Vhost:</strong> {{ state.vhost_status || '—' }} · <strong>SSL:</strong> {{ state.ssl_status || '—' }}</p>
                        <p class="mb-2"><strong>URL:</strong> <code>{{ state.host || host }}</code></p>
                        <p v-if="state.provision_error" class="text-danger mb-0">{{ state.provision_error }}</p>
                        <p v-else-if="!finished" class="text-muted mb-0">
                            <span class="spinner-border spinner-border-sm me-1" />
                            Watching provision stream…
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <section class="pf-card">
            <div class="pf-card-head"><h2>Provision console</h2></div>
            <pre class="pf-console">{{ (state.log || []).length
                ? state.log.map((line) => `${line.at || ''}  ${line.step || ''} — ${line.message || ''}`).join('\n')
                : 'Waiting for log lines…' }}</pre>
        </section>
    </Layout>
</template>
