<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { applyBrandTheme } from '@/theme/applyBrandTheme';

const page = usePage();

watch(
    () => ({
        theme: page.props?.platform?.theme,
        name: page.props?.app?.name,
        favicon: page.props?.app?.favicon,
    }),
    ({ theme, name, favicon }) => applyBrandTheme({
        ...(theme || {}),
        app_name: name || '',
        favicon: favicon || '',
    }),
    { immediate: true, deep: true },
);
</script>

<template><span hidden /></template>
