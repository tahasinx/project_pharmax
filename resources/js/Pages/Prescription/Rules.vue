<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

onMounted(() => {
    router.replace(route('clinical-rules.index'))
})
</script>

<template>
    <div class="p-4 text-muted font-size-13">Opening clinical rules…</div>
</template>
