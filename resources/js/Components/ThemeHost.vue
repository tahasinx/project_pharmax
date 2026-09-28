<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const page = usePage();

const apply = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }
    const root = document.documentElement;
    const brand = theme || {
        primary: '#17342b',
        font_family: 'Inter',
        font_href: '',
        font_size: 16,
        font_weight: 400,
        radius: '10px',
    };
    const strong = Math.min(900, Number(brand.font_weight || 400) + 200);
    root.style.setProperty('--pf-accent', brand.primary || '#17342b');
    root.style.setProperty('--pf-font', `"${brand.font_family || 'Inter'}", sans-serif`);
    root.style.setProperty('--pf-size', `${brand.font_size || 16}px`);
    root.style.setProperty('--pf-weight', String(brand.font_weight || 400));
    root.style.setProperty('--pf-weight-strong', String(strong));
    root.style.setProperty('--pf-radius', brand.radius || '10px');

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

watch(() => page.props.platform?.theme, apply, { immediate: true, deep: true });
</script>

<template><span hidden /></template>
