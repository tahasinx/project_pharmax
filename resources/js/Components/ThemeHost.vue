<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const page = usePage();

const hexToRgb = (hex) => {
    const clean = String(hex || '').replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(clean)) {
        return '81, 86, 190';
    }
    return [
        parseInt(clean.slice(0, 2), 16),
        parseInt(clean.slice(2, 4), 16),
        parseInt(clean.slice(4, 6), 16),
    ].join(', ');
};

const apply = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }
    const root = document.documentElement;
    const body = document.body;
    const brand = theme || {
        primary: '#5156be',
        primary_rgb: '81, 86, 190',
        font_family: 'IBM Plex Sans',
        font_href: '',
        font_size: 16,
        font_weight: 400,
        radius: '10px',
        shape: 'rounded',
    };
    const primary = brand.primary || '#5156be';
    const rgb = brand.primary_rgb || hexToRgb(primary);
    const strong = Math.min(900, Number(brand.font_weight || 400) + 200);
    const radius = brand.radius || '10px';
    const family = brand.font_family || 'IBM Plex Sans';
    const size = `${brand.font_size || 16}px`;
    const weight = String(brand.font_weight || 400);
    const shape = brand.shape || 'rounded';

    const pairs = {
        '--pf-accent': primary,
        '--pf-accent-rgb': rgb,
        '--pf-font': `"${family}", sans-serif`,
        '--pf-size': size,
        '--pf-weight': weight,
        '--pf-weight-strong': String(strong),
        '--pf-radius': radius,
        '--bs-primary': primary,
        '--bs-primary-rgb': rgb,
        '--bs-link-color': primary,
        '--bs-link-hover-color': primary,
        '--bs-border-radius': radius,
        '--bs-border-radius-sm': radius,
        '--bs-border-radius-lg': radius,
        '--bs-sidebar-menu-item-active-color': primary,
        '--bs-sidebar-menu-sub-item-active-color': primary,
        '--bs-sidebar-menu-item-active-bg-color': `rgba(${rgb}, 0.12)`,
        '--bs-topbar-user-bg': primary,
    };

    Object.entries(pairs).forEach(([key, value]) => {
        root.style.setProperty(key, value);
        body.style.setProperty(key, value);
    });

    root.style.fontSize = size;
    body.style.fontSize = size;
    body.style.fontWeight = weight;
    body.style.fontFamily = `"${family}", sans-serif`;

    root.setAttribute('data-app-shape', shape);
    body.setAttribute('data-app-shape', shape);

    let link = document.getElementById('epharma-font');
    if (brand.font_href) {
        if (!link) {
            link = document.createElement('link');
            link.id = 'epharma-font';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
        link.href = brand.font_href;
    } else if (link) {
        link.remove();
    }
};

watch(() => page.props?.platform?.theme, apply, { immediate: true, deep: true });
</script>

<template><span hidden /></template>
