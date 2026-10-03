<script setup>
defineProps({
    status: { type: String, default: '' },
    kind: { type: String, default: 'status' }, // status | provision
});

const tone = (value, kind) => {
    const v = String(value || '').toLowerCase();
    if (kind === 'provision') {
        if (v === 'active') return 'ok';
        if (v === 'running' || v === 'pending') return 'info';
        if (v === 'degraded') return 'warn';
        if (v === 'failed') return 'danger';
        return 'muted';
    }
    if (v === 'active') return 'ok';
    if (v === 'locked' || v === 'suspended') return 'warn';
    return 'muted';
};
</script>

<template>
    <span class="pf-badge" :class="`is-${tone(status, kind)}`">{{ status || '—' }}</span>
</template>
