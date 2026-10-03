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
const stepLabels = ['Validate', 'Registry', 'Database', 'Schema', 'Vhost', 'SSL', 'Ready'];

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
const consoleText = computed(() => {
    const lines = state.value.log || [];
    if (!lines.length) return 'Waiting for log lines…';
    return lines.map((line) => {
        const at = line.at || '';
        const step = line.step || '';
        const message = line.message || '';
        return `${at}  ${step}${step ? ' — ' : ''}${message}`.trim();
    }).join('\n');
});

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
        <template #header>
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="mb-0 font-size-18">Provisioning {{ company.name }}</h4>
                    <StatusBadge :status="state.provision_status" kind="provision" />
                </div>
                <p class="text-muted mb-0 font-size-13">Live progress for tenant database, nginx vhost, and SSL.</p>
            </div>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="`/platform/companies/${company.id}`" class="btn btn-outline-secondary btn-sm">Pharmacy record</Link>
                <button
                    v-if="['failed', 'degraded'].includes(state.provision_status)"
                    type="button"
                    class="btn btn-warning btn-sm"
                    @click="router.post(`/platform/companies/${company.id}/provision/retry`)"
                >
                    Retry
                </button>
                <a
                    v-if="state.provision_status === 'active'"
                    :href="state.host || host"
                    class="btn btn-success btn-sm"
                    target="_blank"
                    rel="noopener"
                >
                    Open tenant
                </a>
            </div>
        </template>

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <div>
                    <h2>Pipeline</h2>
                    <p v-if="!hostEnabled">Host provisioner disabled — database-only mode.</p>
                    <p v-else>Each step lights up as the provisioner advances.</p>
                </div>
                <span v-if="!finished" class="small text-muted d-inline-flex align-items-center gap-1">
                    <span class="spinner-border spinner-border-sm" />
                    Streaming…
                </span>
            </div>
            <div class="pf-card-body">
                <ol class="pf-step-rail">
                    <li
                        v-for="(label, idx) in stepLabels"
                        :key="label"
                        class="pf-step-rail-item"
                        :class="{
                            'is-done': currentIdx > idx || state.provision_status === 'active',
                            'is-current': currentIdx === idx && !finished,
                            'is-failed': finished && state.provision_status === 'failed' && currentIdx === idx,
                        }"
                    >
                        <span class="pf-step-rail-dot" />
                        <span class="pf-step-rail-label">{{ label }}</span>
                    </li>
                </ol>
            </div>
        </section>

        <div class="pf-metric-grid mb-3">
            <div class="pf-metric">
                <span class="pf-metric-label">Slug</span>
                <span class="pf-metric-value font-monospace">{{ company.slug }}</span>
            </div>
            <div class="pf-metric">
                <span class="pf-metric-label">Database</span>
                <span class="pf-metric-value font-monospace">{{ company.database_name }}</span>
            </div>
            <div class="pf-metric">
                <span class="pf-metric-label">Step</span>
                <span class="pf-metric-value">{{ state.provision_step || '—' }}</span>
            </div>
            <div class="pf-metric">
                <span class="pf-metric-label">Host</span>
                <span class="pf-metric-value">{{ state.vhost_status || '—' }} · SSL {{ state.ssl_status || '—' }}</span>
                <span class="pf-metric-hint">
                    <a :href="state.host || host" target="_blank" rel="noopener">{{ state.host || host }}</a>
                </span>
            </div>
        </div>

        <p v-if="state.provision_error" class="alert alert-danger py-2 px-3 small">{{ state.provision_error }}</p>

        <section class="pf-card">
            <div class="pf-card-head">
                <div>
                    <h2>Console</h2>
                    <p>{{ (state.log || []).length }} log line{{ (state.log || []).length === 1 ? '' : 's' }}</p>
                </div>
            </div>
            <pre class="pf-console">{{ consoleText }}</pre>
        </section>
    </Layout>
</template>

<style scoped>
.pf-step-rail {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.35rem;
}

.pf-step-rail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    text-align: center;
    position: relative;
    color: var(--pf-muted, #74788d);
    font-size: 0.75rem;
    font-weight: 600;
}

.pf-step-rail-item:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 0.4rem;
    left: calc(50% + 0.55rem);
    right: calc(-50% + 0.55rem);
    height: 2px;
    background: var(--pf-border, #e6e8ee);
}

.pf-step-rail-dot {
    width: 0.85rem;
    height: 0.85rem;
    border-radius: 999px;
    border: 2px solid var(--pf-border, #ced4da);
    background: #fff;
    z-index: 1;
}

.pf-step-rail-item.is-done {
    color: var(--shell-panel-accent-text, #1e8f68);
}

.pf-step-rail-item.is-done .pf-step-rail-dot,
.pf-step-rail-item.is-done:not(:last-child)::after {
    background: var(--shell-panel-accent-text, #1e8f68);
    border-color: var(--shell-panel-accent-text, #1e8f68);
}

.pf-step-rail-item.is-current {
    color: var(--pf-accent, #5156be);
}

.pf-step-rail-item.is-current .pf-step-rail-dot {
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 3px rgba(var(--pf-accent-rgb, 81, 86, 190), 0.2);
}

.pf-step-rail-item.is-failed {
    color: #f46a6a;
}

.pf-step-rail-item.is-failed .pf-step-rail-dot {
    border-color: #f46a6a;
    background: #f46a6a;
}

.pf-metric-value {
    font-size: 0.92rem;
    word-break: break-word;
}

@media (max-width: 900px) {
    .pf-step-rail {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pf-step-rail-item:not(:last-child)::after {
        display: none;
    }
}
</style>
