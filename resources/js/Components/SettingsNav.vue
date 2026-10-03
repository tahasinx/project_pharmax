<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    active: {
        type: String,
        default: 'general',
    },
});

const items = [
    { key: 'general', label: 'General', href: 'settings.index', hint: 'Company, brand, mail' },
    { key: 'theme', label: 'Theme', href: 'settings.theme', hint: 'Color, shape, type' },
];
</script>

<template>
    <nav class="settings-nav" aria-label="App settings">
        <div class="settings-nav-title">App settings</div>
        <Link
            v-for="item in items"
            :key="item.key"
            :href="route(item.href)"
            class="settings-nav-item"
            :class="{ 'is-active': active === item.key }"
        >
            <span class="settings-nav-label">{{ item.label }}</span>
            <span class="settings-nav-hint">{{ item.hint }}</span>
        </Link>
    </nav>
</template>

<style scoped>
.settings-nav {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 0.85rem;
    background: #f8f9fc;
    border: 1px solid #e6e8ee;
    border-radius: 0.65rem;
}

.settings-nav-title {
    margin: 0 0 0.35rem;
    padding: 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #74788d;
}

.settings-nav-item {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    padding: 0.65rem 0.75rem;
    border-radius: 0.45rem;
    text-decoration: none;
    color: #495057;
    transition: background 0.15s ease, color 0.15s ease;
}

.settings-nav-item:hover {
    background: #fff;
    color: #343747;
}

.settings-nav-item.is-active {
    background: color-mix(in srgb, var(--pf-accent, #5156be) 12%, #fff);
    box-shadow: inset 2px 0 0 var(--pf-accent, #5156be);
    color: #343747;
}

.settings-nav-label {
    font-size: 0.9rem;
    font-weight: 600;
}

.settings-nav-hint {
    font-size: 0.72rem;
    color: #74788d;
}
</style>
