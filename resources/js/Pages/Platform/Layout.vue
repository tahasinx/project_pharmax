<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import LunaShell from '@/Layouts/LunaShell.vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});
const flash = computed(() => page.props.flash || {});
const path = computed(() => (page.url || '').split('?')[0]);

const groups = computed(() => [
    {
        label: 'Operate',
        items: [
            ['Overview', '/platform'],
            ['Pharmacies', '/platform/companies'],
        ],
    },
    {
        label: 'Billing',
        items: [
            ['Plans', '/platform/plans'],
            ['Subscriptions', '/platform/subscriptions'],
            ['Invoices', '/platform/invoices'],
            ['Billing', '/platform/billing'],
        ],
    },
    {
        label: 'Server',
        items: [
            ['Schema', '/platform/schema'],
            ['Backups', '/platform/backups'],
            ['Commands', '/platform/commands'],
            ...(page.props.platform?.deploy ? [['Deploy', '/platform/deploy']] : []),
            ['Settings', '/platform/settings'],
        ],
    },
].map((group) => ({
    label: group.label,
    items: group.items.map(([name, href]) => ({
        key: href,
        name,
        href,
        active: href === '/platform' ? path.value === '/platform' : path.value === href || path.value.startsWith(`${href}/`),
    })),
})));
</script>

<template>
    <LunaShell :groups="groups" meta="Platform" home-href="/platform">
        <template v-if="$slots.header" #header>
            <slot name="header" />
        </template>

        <div class="pf-shell">
            <Transition name="pf-flash">
                <div v-if="flash.success" class="pf-alert is-ok" role="status">{{ flash.success }}</div>
            </Transition>
            <Transition name="pf-flash">
                <div v-if="flash.error" class="pf-alert is-danger" role="alert">{{ flash.error }}</div>
            </Transition>
            <p
                v-for="(message, key) in errors"
                :key="key"
                class="pf-alert is-danger"
            >
                {{ message }}
            </p>
            <slot />
        </div>
    </LunaShell>
</template>

<style>
.pf-shell {
    --pf-surface: var(--shell-panel-surface, #fff);
    --pf-bg: var(--shell-panel-bg, #f8f9fc);
    --pf-border: var(--shell-panel-border, #e6e8ee);
    --pf-text: var(--shell-panel-text, #343747);
    --pf-muted: var(--shell-panel-muted, #74788d);
    --pf-primary: var(--bs-primary, #5156be);
    --pf-radius: var(--pf-radius, 0.65rem);
    color: var(--pf-text);
}

.pf-alert {
    margin-bottom: 0.85rem;
    border-radius: var(--pf-radius);
    border: 1px solid var(--pf-border);
    padding: 0.7rem 0.9rem;
    font-size: 0.86rem;
    background: var(--pf-bg);
}

.pf-alert.is-ok {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.pf-alert.is-danger {
    border-color: rgba(220, 53, 69, 0.28);
    background: rgba(220, 53, 69, 0.08);
    color: #b02a37;
}

.pf-flash-enter-active,
.pf-flash-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}
.pf-flash-enter-from,
.pf-flash-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.pf-page-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.85rem;
    margin-bottom: 1rem;
}

.pf-page-head h1,
.pf-page-head h4 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 650;
    color: var(--pf-text);
}

.pf-page-sub {
    margin: 0.25rem 0 0;
    font-size: 0.82rem;
    color: var(--pf-muted);
}

.pf-card {
    background: var(--pf-surface);
    border: 1px solid var(--pf-border);
    border-radius: calc(var(--pf-radius) + 0.05rem);
    overflow: hidden;
}

.pf-card-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    padding: 0.85rem 1.05rem;
    border-bottom: 1px solid var(--pf-border);
    background: var(--pf-bg);
}

.pf-card-head h2,
.pf-card-head h5,
.pf-card-head h6 {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--pf-text);
}

.pf-card-body {
    padding: 1rem 1.05rem;
}

.pf-metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}

.pf-metric {
    background: var(--pf-surface);
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    padding: 0.9rem 1rem;
    transition: border-color 0.15s ease, transform 0.15s ease;
}

.pf-metric:hover {
    border-color: color-mix(in srgb, var(--pf-primary) 35%, var(--pf-border));
    transform: translateY(-1px);
}

.pf-metric-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--pf-muted);
}

.pf-metric-value {
    display: block;
    margin-top: 0.35rem;
    font-size: 1.55rem;
    font-weight: 700;
    line-height: 1;
    color: var(--pf-text);
}

.pf-metric-hint {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.72rem;
    color: var(--pf-muted);
}

.pf-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    border: 1px solid var(--pf-border);
    background: var(--pf-bg);
    color: var(--pf-muted);
    text-transform: lowercase;
}

.pf-badge.is-ok {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.pf-badge.is-info {
    border-color: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.28);
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.1);
    color: var(--pf-primary);
}

.pf-badge.is-warn {
    border-color: rgba(255, 193, 7, 0.4);
    background: rgba(255, 193, 7, 0.14);
    color: #9a6700;
}

.pf-badge.is-danger {
    border-color: rgba(220, 53, 69, 0.28);
    background: rgba(220, 53, 69, 0.1);
    color: #b02a37;
}

.pf-ops-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    padding: 0.3rem;
    margin-bottom: 1rem;
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    background: var(--pf-bg);
}

.pf-ops-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.8rem;
    border-radius: calc(var(--pf-radius) - 0.12rem);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--pf-muted);
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
}

.pf-ops-tab:hover {
    color: var(--pf-text);
    background: var(--pf-surface);
}

.pf-ops-tab.active {
    background: var(--pf-primary);
    color: #fff;
}

.pf-control-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.pf-field {
    display: block;
    margin-bottom: 0.65rem;
}

.pf-field > span {
    display: block;
    margin-bottom: 0.28rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--pf-muted);
}

.pf-field input,
.pf-field select,
.pf-field textarea {
    width: 100%;
    border: 1px solid var(--pf-border);
    border-radius: calc(var(--pf-radius) - 0.15rem);
    background: var(--pf-surface);
    color: var(--pf-text);
    padding: 0.5rem 0.7rem;
    font-size: 0.88rem;
}

.pf-field input:focus,
.pf-field select:focus,
.pf-field textarea:focus {
    outline: none;
    border-color: var(--pf-primary);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 81, 86, 190), 0.15);
}

.pf-console {
    margin: 0;
    min-height: 16rem;
    max-height: 28rem;
    overflow: auto;
    padding: 1rem;
    background: #0b1020;
    color: #d7deea;
    font-size: 0.75rem;
    line-height: 1.5;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

.pf-steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.pf-step {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.86rem;
    color: var(--pf-muted);
}

.pf-step-dot {
    width: 0.7rem;
    height: 0.7rem;
    border-radius: 999px;
    border: 2px solid var(--pf-border);
    background: transparent;
    flex-shrink: 0;
}

.pf-step.is-done {
    color: var(--shell-panel-accent-text, #1e8f68);
}
.pf-step.is-done .pf-step-dot {
    border-color: var(--shell-panel-accent-text, #1e8f68);
    background: var(--shell-panel-accent-text, #1e8f68);
}
.pf-step.is-current {
    color: var(--pf-primary);
    font-weight: 650;
}
.pf-step.is-current .pf-step-dot {
    border-color: var(--pf-primary);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 81, 86, 190), 0.18);
}

@media (max-width: 900px) {
    .pf-control-grid {
        grid-template-columns: 1fr;
    }
}
</style>
