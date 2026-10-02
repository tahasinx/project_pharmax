<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    closeHref: { type: String, required: true },
});
</script>

<template>
    <div class="screen-modal">
        <div class="screen-modal-panel">
            <div class="screen-modal-head">
                <h4 class="mb-0">{{ title }}</h4>
                <Link :href="closeHref" class="btn btn-light btn-sm">Close</Link>
            </div>
            <div class="screen-modal-body">
                <slot />
            </div>
        </div>
    </div>
</template>
