<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: '' },
    provision: { type: String, default: '' },
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
    if (v === 'active' || v === 'paid' || v === 'success') return 'ok';
    if (v === 'locked' || v === 'suspended' || v === 'past_due' || v === 'trialing') return 'warn';
    if (v === 'unpaid' || v === 'failed' || v === 'canceled' || v === 'cancelled') return 'danger';
    if (v === 'archived' || v === 'inactive' || v === 'draft') return 'muted';
    return 'muted';
};

/** Prefer lock/fail signals; avoid showing the same label twice. */
const chips = computed(() => {
    const status = String(props.status || '').trim();
    const provision = String(props.provision || '').trim();

    if (props.kind === 'provision' || (!status && provision)) {
        return provision || status ? [{ label: provision || status, kind: 'provision' }] : [];
    }

    if (!status && !provision) {
        return [{ label: '—', kind: 'status' }];
    }

    if (!provision || provision.toLowerCase() === status.toLowerCase()) {
        return status ? [{ label: status, kind: 'status' }] : [];
    }

    // Locked / archived company status plus a distinct provision state.
    return [
        { label: status, kind: 'status' },
        { label: provision, kind: 'provision' },
    ];
});
</script>

<template>
    <span class="pf-status-stack">
        <span
            v-for="chip in chips"
            :key="`${chip.kind}:${chip.label}`"
            class="pf-badge"
            :class="`is-${tone(chip.label, chip.kind)}`"
        >{{ chip.label }}</span>
    </span>
</template>

<style scoped>
.pf-status-stack {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
}
</style>
