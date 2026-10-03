<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import LunaShell from '@/Layouts/LunaShell.vue';

const page = usePage();
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
            <slot />
        </div>
    </LunaShell>
</template>

<style>
/* Hoist tokens so teleported FormScreen modals inherit borders/colors.
   Corner radius is owned by ThemeHost (--pf-radius / --bs-border-radius). */
body.minia-app {
    --pf-radius: var(--bs-border-radius, 10px);
}

body.minia-app,
.pf-shell,
.app-float-modal {
    --pf-surface: var(--shell-panel-surface, #fff);
    --pf-bg: var(--shell-panel-bg, #f8f9fc);
    --pf-border: var(--shell-panel-border, #e6e8ee);
    --pf-text: var(--shell-panel-text, #343747);
    --pf-muted: var(--shell-panel-muted, #74788d);
    --pf-primary: var(--bs-primary, var(--pf-accent, #5156be));
}

.pf-shell {
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

/* Match tenant user-panel page titles (font-size-18). Beat Minia h1/h4 scale. */
body.minia-app .pf-shell h1,
body.minia-app .pf-shell .pf-page-head h1,
body.minia-app .pf-shell .pf-title,
body.minia-app .page-title-box h1,
body.minia-app .page-title-box h4,
body.minia-app .page-title-box .pf-title,
body.minia-app .page-title-box .font-size-18 {
    margin: 0 !important;
    font-size: 18px !important;
    font-weight: var(--pf-weight, 400) !important;
    line-height: 1.3 !important;
    color: var(--pf-text) !important;
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
.pf-page-head h4,
.pf-title {
    margin: 0;
    font-size: 18px;
    font-weight: var(--pf-weight, 400);
    line-height: 1.3;
    color: var(--pf-text);
}

.pf-page-sub {
    margin: 0.25rem 0 0;
    font-size: 0.82rem;
    color: var(--pf-muted);
}

.pf-start-link {
    display: block;
    padding: 0.55rem 0.65rem;
    border: 1px solid transparent;
    border-radius: var(--pf-radius, 10px);
    transition: background 0.15s ease, border-color 0.15s ease;
}

.pf-start-link:hover {
    background: var(--pf-bg);
    border-color: var(--pf-border);
}

.pf-card {
    background: var(--pf-surface);
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    overflow: hidden;
    position: relative;
}

/* SearchableSelect menus must escape the card clip and stack above the next card. */
.pf-card:has(.ss.is-open) {
    overflow: visible;
    z-index: 20;
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

/* Beat Minia `h2 { font-size: 1.75rem }` — card titles stay compact. */
body.minia-app .pf-shell .pf-card-head h2,
body.minia-app .pf-shell .pf-card-head h5,
body.minia-app .pf-shell .pf-card-head h6,
body.minia-app .pf-shell .create-section h2,
body.minia-app .app-float-modal .pf-card-head h2,
body.minia-app .app-float-modal .create-section h2,
.pf-card-head h2,
.pf-card-head h5,
.pf-card-head h6 {
    margin: 0 !important;
    font-size: 0.86rem !important;
    font-weight: var(--pf-weight, 400) !important;
    line-height: 1.3 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
    color: var(--pf-text) !important;
}

.pf-card-head p {
    margin: 0.15rem 0 0;
    font-size: 0.76rem;
    color: var(--pf-muted);
}

/* Nested LunaTable inside a pf-card — drop the second chrome. */
body.minia-app .pf-shell .pf-card > .dt-card,
body.minia-app .pf-shell .pf-card > .card.dt-card {
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: transparent !important;
}

body.minia-app .pf-shell .pf-card > .dt-card > .card-body {
    padding: 0.85rem 1.05rem 1rem !important;
}

.pf-card-body {
    padding: 1rem 1.05rem;
}

.pf-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.65rem;
    margin-bottom: 0.85rem;
    padding: 0.75rem 0.9rem;
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius);
    background: var(--pf-bg);
}

.pf-toolbar .pf-field {
    margin-bottom: 0;
    min-width: 9rem;
}

.pf-composer {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 0.75rem;
}

.pf-composer .pf-field {
    margin-bottom: 0;
}

.pf-span-3 { grid-column: span 3; }
.pf-span-4 { grid-column: span 4; }
.pf-span-5 { grid-column: span 5; }
.pf-span-6 { grid-column: span 6; }
.pf-span-8 { grid-column: span 8; }
.pf-span-12 { grid-column: span 12; }

.pf-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 2.5rem 1rem;
    text-align: center;
    color: var(--pf-muted);
}

.pf-empty i {
    font-size: 1.6rem;
    opacity: 0.55;
    margin-bottom: 0.25rem;
}

.pf-empty strong {
    display: block;
    font-size: 0.9rem;
    font-weight: var(--pf-weight-medium, 500);
    color: var(--pf-text);
}

.pf-empty span {
    font-size: 0.8rem;
}

.pf-money {
    font-variant-numeric: tabular-nums;
    font-weight: var(--pf-weight-medium, 500);
}

@media (max-width: 900px) {
    .pf-span-3,
    .pf-span-4,
    .pf-span-5,
    .pf-span-6,
    .pf-span-8,
    .pf-span-12 {
        grid-column: span 12;
    }
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
    font-weight: var(--pf-weight, 400);
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--pf-muted);
}

.pf-metric-value {
    display: block;
    margin-top: 0.35rem;
    font-size: 1.55rem;
    font-weight: var(--pf-weight-medium, 500);
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
    padding: 0.18rem 0.5rem;
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px)) !important;
    font-size: 0.7rem;
    font-weight: var(--pf-weight, 400);
    letter-spacing: 0.01em;
    border: 1px solid var(--pf-border);
    background: var(--pf-bg);
    color: var(--pf-muted);
    text-transform: lowercase;
    line-height: 1.2;
}

/* Active / success — soft theme primary (not mint green). */
.pf-badge.is-ok {
    border-color: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.28);
    background: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.1);
    color: var(--pf-primary, var(--bs-primary, #17342b));
}

.pf-badge.is-info {
    border-color: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.22);
    background: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.06);
    color: var(--pf-primary, var(--bs-primary, #17342b));
}

.pf-badge.is-warn {
    border-color: rgba(176, 132, 12, 0.35);
    background: rgba(255, 193, 7, 0.12);
    color: #8a6a00;
}

.pf-badge.is-danger {
    border-color: rgba(176, 42, 55, 0.28);
    background: rgba(220, 53, 69, 0.08);
    color: #b02a37;
}

.pf-badge.is-muted {
    border-color: var(--pf-border);
    background: var(--pf-bg);
    color: var(--pf-muted);
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

/* Beat Minia `a { color: var(--bs-link-color) }` so tabs stay readable on theme green. */
body.minia-app a.pf-ops-tab,
body.minia-app button.pf-ops-tab,
.pf-ops-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.8rem;
    border: 0;
    border-radius: var(--pf-radius);
    background: transparent;
    font-size: 0.8rem;
    font-weight: var(--pf-weight, 400);
    color: var(--pf-muted) !important;
    text-decoration: none !important;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}

body.minia-app a.pf-ops-tab i,
body.minia-app a.pf-ops-tab span,
body.minia-app button.pf-ops-tab i,
body.minia-app button.pf-ops-tab span,
.pf-ops-tab i,
.pf-ops-tab span {
    color: inherit !important;
}

body.minia-app a.pf-ops-tab:hover,
body.minia-app button.pf-ops-tab:hover,
.pf-ops-tab:hover {
    color: var(--pf-text) !important;
    background: var(--pf-surface);
}

/* Soft active — never solid fill (theme primary ≈ link color → invisible labels). */
body.minia-app a.pf-ops-tab.active,
body.minia-app button.pf-ops-tab.active,
.pf-ops-tab.active {
    background: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.12);
    color: var(--pf-primary) !important;
    box-shadow: inset 0 0 0 1px rgba(var(--bs-primary-rgb, 23, 52, 43), 0.35);
}

body.minia-app a.pf-ops-tab.active i,
body.minia-app a.pf-ops-tab.active span,
body.minia-app button.pf-ops-tab.active i,
body.minia-app button.pf-ops-tab.active span,
.pf-ops-tab.active i,
.pf-ops-tab.active span {
    color: var(--pf-primary) !important;
}

.pf-inline-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.65rem;
}

.pf-inline-actions .pf-field {
    flex: 1 1 10rem;
    margin-bottom: 0;
}

.pf-inline-actions .btn {
    height: 2rem;
    min-height: 2rem;
    display: inline-flex;
    align-items: center;
    padding: 0 0.9rem;
    font-size: 0.82rem;
}

.pf-field input[type="file"] {
    height: auto;
    min-height: 2rem;
    padding: 0.25rem 0.4rem;
    line-height: 1.2;
    font-size: 0.78rem;
}

.pf-field input[type="color"] {
    width: 3rem;
    padding: 0.15rem;
    cursor: pointer;
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
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: var(--pf-weight, 400);
    letter-spacing: 0;
    text-transform: none;
    color: var(--pf-muted);
}

.pf-field input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
.pf-field select,
.pf-field .ss-input {
    width: 100%;
    box-sizing: border-box;
    height: 2rem;
    min-height: 2rem;
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    background: var(--pf-surface);
    color: var(--pf-text);
    padding: 0 0.55rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
}

.pf-field .ss-input {
    padding-right: 1.8rem;
}

.pf-field input[type="checkbox"],
.pf-field input[type="radio"] {
    width: auto;
    height: auto;
    min-height: 0;
    margin: 0;
    padding: 0;
    border: 0;
    box-shadow: none;
}

.pf-field textarea {
    width: 100%;
    box-sizing: border-box;
    min-height: 4rem;
    border: 1px solid var(--pf-border);
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    background: var(--pf-surface);
    color: var(--pf-text);
    padding: 0.4rem 0.55rem;
    font-size: 0.82rem;
    line-height: 1.35;
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
    font-weight: var(--pf-weight-medium, 500);
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
