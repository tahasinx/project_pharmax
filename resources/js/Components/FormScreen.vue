<script setup>
import { router } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    closeHref: { type: String, default: null },
    loading: { type: Boolean, default: false },
    size: {
        type: String,
        default: 'fullscreen', // sm | md | lg | xl | fullscreen
    },
});

const emit = defineEmits(['close']);

const phase = ref(document.body.classList.contains('app-float-modal-bridge') ? 'in' : 'enter'); // enter | in | leave
let closeTimer = null;

const finishClose = () => {
    if (props.closeHref) {
        router.visit(props.closeHref, { preserveScroll: true });
        return;
    }
    emit('close');
};

const requestClose = () => {
    if (phase.value === 'leave') return;
    phase.value = 'leave';
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(finishClose, 200);
};

onMounted(async () => {
    document.body.classList.add('app-content-modal-open');
    document.body.classList.add('app-float-modal-pending-done');
    document.body.classList.remove('app-float-modal-bridge');
    await nextTick();
    if (phase.value === 'enter') {
        requestAnimationFrame(() => {
            phase.value = 'in';
        });
    }
});

onUnmounted(() => {
    window.clearTimeout(closeTimer);
    document.body.classList.remove('app-content-modal-open');
    document.body.classList.remove('app-float-modal-pending-done');
});
</script>

<template>
    <Teleport to="body">
        <div
            class="modal app-float-modal"
            :class="{
                'is-enter': phase === 'enter',
                'is-in': phase === 'in',
                'is-leave': phase === 'leave',
            }"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="modal-dialog modal-dialog-scrollable app-float-modal-dialog"
                :class="{
                    'modal-sm': size === 'sm',
                    'modal-lg': size === 'lg',
                    'modal-xl': size === 'xl',
                    'app-float-modal-dialog--fs': size === 'fullscreen',
                }"
                role="document"
            >
                <div class="modal-content app-float-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ title }}</h5>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <slot name="header-actions" />
                            <button
                                type="button"
                                class="app-float-close"
                                aria-label="Close"
                                @click="requestClose"
                            >&times;</button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div v-if="loading" class="app-float-loading" aria-live="polite" aria-busy="true">
                            <span class="app-float-spinner" />
                            <p>Loading…</p>
                        </div>
                        <slot v-else />
                    </div>
                    <div class="modal-footer">
                        <slot name="footer" :close="requestClose">
                            <button type="button" class="btn btn-outline-danger" @click="requestClose">Cancel</button>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="modal-backdrop app-float-modal-backdrop"
            :class="{
                'is-enter': phase === 'enter',
                'is-in': phase === 'in',
                'is-leave': phase === 'leave',
            }"
        />
    </Teleport>
</template>
