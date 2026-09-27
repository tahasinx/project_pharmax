<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    hostEnabled: Boolean,
});
const state = ref({
    provision_status: props.company.provision_status,
    provision_step: props.company.provision_step,
    provision_error: props.company.provision_error,
    vhost_status: props.company.vhost_status,
    ssl_status: props.company.ssl_status,
    log: props.company.provision_log || [],
});
let timer;
async function pull() {
    const response = await fetch(`/platform/companies/${props.company.id}/provision/log`, { headers: { Accept: 'application/json' } });
    if (response.ok) {
        state.value = await response.json();
    }
    if (!['active', 'failed', 'degraded'].includes(state.value.provision_status)) {
        timer = setTimeout(pull, 2000);
    }
}
onMounted(pull);
onUnmounted(() => clearTimeout(timer));
</script>

<template>
    <Head title="Provisioning" />
    <Layout>
        <h1 class="text-2xl font-semibold">Provisioning {{ company.name }}</h1>
        <p class="mt-1 text-sm">{{ state.provision_status }} · {{ state.provision_step || 'waiting' }} · host {{ state.vhost_status || '—' }} · certificate {{ state.ssl_status || '—' }}</p>
        <p v-if="!hostEnabled" class="mt-2 text-sm text-[#71717a]">This machine does not have the host script. The pharmacy database and admin are still created. The hostname and certificate run on the server where that script is installed.</p>
        <p v-if="state.provision_error" class="mt-2 text-sm text-red-700">{{ state.provision_error }}</p>
        <ol class="mt-4 space-y-1 rounded bg-white p-3 text-sm">
            <li v-for="(line, index) in state.log" :key="index"><span class="text-[#5c6b63]">{{ line.at }}</span> {{ line.step }} — {{ line.message }}</li>
        </ol>
        <div class="mt-4 flex gap-3 text-sm">
            <Link :href="`/platform/companies/${company.id}`" class="underline">Pharmacy record</Link>
            <a v-if="state.provision_status === 'active'" :href="host" class="underline">{{ host }}</a>
            <button v-if="['failed', 'degraded'].includes(state.provision_status)" class="underline" @click="$inertia.post(`/platform/companies/${company.id}/provision/retry`)">Retry</button>
        </div>
    </Layout>
</template>
