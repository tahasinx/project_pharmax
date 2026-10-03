<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import OpsNav from '@/Components/Platform/OpsNav.vue';
import Layout from './Layout.vue';

const props = defineProps({ status: Object, settings: Object });
const form = useForm({ password: '', reason: '', confirm_text: '', allow_redeploy: false });
const live = ref({ ...props.status });
let timer;

async function refresh() {
    try {
        const response = await fetch('/platform/deploy/status', { headers: { Accept: 'application/json' } });
        if (response.ok) {
            live.value = await response.json();
        }
    } catch {
        // ignore transient errors
    }
    timer = setTimeout(refresh, 8000);
}

onMounted(() => {
    if (props.settings?.configured) refresh();
});
onUnmounted(() => clearTimeout(timer));
</script>

<template>
    <Head title="Deploy" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Deploy production</h4>
                <p class="text-muted mb-0 font-size-13">
                    {{ settings.owner }}/{{ settings.repo }} · {{ settings.base }} → {{ settings.prod }} · {{ settings.workflow }}
                </p>
            </div>
        </template>
        <OpsNav />

        <div class="row g-3">
            <div class="col-lg-7">
                <section class="pf-card mb-3">
                    <div class="pf-card-head"><h2>Branch status</h2></div>
                    <div class="pf-card-body">
                        <p class="mb-2">{{ live.message || status.message }}</p>
                        <p v-if="live.base_sha || status.base_sha" class="small text-muted mb-3">
                            {{ settings.base }} {{ live.base_sha || status.base_sha }}
                            · {{ settings.prod }} {{ live.prod_sha || status.prod_sha }}
                            · ahead {{ live.ahead_by ?? status.ahead_by }}
                            · behind {{ live.behind_by ?? status.behind_by }}
                        </p>
                        <ul class="list-unstyled small mb-0">
                            <li v-for="commit in (live.commits || status.commits || [])" :key="commit.sha" class="border-top py-2">
                                <code>{{ commit.sha }}</code> {{ commit.message }}
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card-head"><h2>Recent workflow runs</h2></div>
                    <ul class="list-group list-group-flush small">
                        <li v-for="run in (live.runs || status.runs || [])" :key="run.url" class="list-group-item d-flex justify-content-between gap-2">
                            <a :href="run.url" target="_blank" rel="noopener">{{ run.name }}</a>
                            <span class="text-muted">{{ run.status }} {{ run.conclusion || '' }}</span>
                        </li>
                        <li v-if="!(live.runs || status.runs || []).length" class="list-group-item text-muted">No recent runs.</li>
                    </ul>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="pf-card">
                    <div class="pf-card-head"><h2>Promote to production</h2></div>
                    <div class="pf-card-body">
                        <form class="d-grid gap-2" @submit.prevent="form.post('/platform/deploy')">
                            <label class="pf-field mb-0">
                                <span>Reason</span>
                                <input v-model="form.reason" placeholder="Why this promote?">
                            </label>
                            <label class="pf-field mb-0">
                                <span>Type DEPLOY</span>
                                <input v-model="form.confirm_text" placeholder="DEPLOY" required autocomplete="off">
                            </label>
                            <label class="pf-field mb-0">
                                <span>Platform password</span>
                                <input v-model="form.password" type="password" required autocomplete="current-password">
                            </label>
                            <label class="d-flex align-items-center gap-2 small mb-2">
                                <input v-model="form.allow_redeploy" type="checkbox">
                                Allow redeploy when already in sync
                            </label>
                            <button class="btn btn-primary" :disabled="!settings.configured || form.processing">
                                <i class="bi bi-rocket-takeoff me-1" />
                                Promote & deploy
                            </button>
                        </form>
                        <p v-if="form.errors.password || form.errors.deploy || form.errors.confirm_text" class="text-danger small mt-2 mb-0">
                            {{ form.errors.password || form.errors.deploy || form.errors.confirm_text }}
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </Layout>
</template>
