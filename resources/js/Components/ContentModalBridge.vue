<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const active = ref(false);
const visible = ref(false);
const title = ref('Loading…');
let hideTimer = null;

const formPathPattern = /\/(medicines|manufacturers|customers|users|categories)\/(create|(?:[^/]+\/(?:edit|codes|show)))$/;

const titleForPath = (path) => {
    if (path.includes('/codes')) return 'Codes';
    if (path.includes('/edit')) return 'Edit';
    if (path.includes('/create')) return 'Create';
    if (path.includes('/show')) return 'Details';
    return 'Loading…';
};

const pathFromVisit = (visit) => {
    try {
        const url = visit?.url;
        if (!url) return '';
        if (typeof url === 'string') {
            return new URL(url, window.location.origin).pathname;
        }
        return url.pathname || '';
    } catch {
        return '';
    }
};

const showShell = (path) => {
    window.clearTimeout(hideTimer);
    title.value = titleForPath(path);
    active.value = true;
    document.body.classList.add('app-content-modal-open');
    document.body.classList.add('app-float-modal-bridge');
    requestAnimationFrame(() => {
        visible.value = true;
    });
};

const hideShell = (instant = false) => {
    document.body.classList.remove('app-float-modal-bridge');
    if (instant) {
        visible.value = false;
        active.value = false;
        // FormScreen owns app-content-modal-open now.
        return;
    }
    visible.value = false;
    window.clearTimeout(hideTimer);
    hideTimer = window.setTimeout(() => {
        active.value = false;
        if (!document.querySelector('.app-float-modal:not(.app-float-modal-pending)')) {
            document.body.classList.remove('app-content-modal-open');
        }
    }, 180);
};

onMounted(() => {
    const offStart = router.on('start', (event) => {
        const path = pathFromVisit(event.detail.visit);
        if (!formPathPattern.test(path)) return;
        // Already inside a real content modal (e.g. save) — don't stack another shell.
        if (document.body.classList.contains('app-float-modal-pending-done')) return;
        if (document.querySelector('.app-float-modal:not(.app-float-modal-pending)')) return;
        showShell(path);
    });

    const offFinish = router.on('finish', () => {
        if (!active.value) return;
        // Real FormScreen just mounted — drop the preload shell instantly.
        hideShell(true);
    });

    const offCancel = router.on('cancel', () => {
        if (active.value) hideShell();
    });

    onUnmounted(() => {
        offStart();
        offFinish();
        offCancel();
        window.clearTimeout(hideTimer);
        document.body.classList.remove('app-float-modal-bridge');
    });
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="active"
            class="modal app-float-modal app-float-modal-pending"
            :class="{ 'is-in': visible, 'is-leave': !visible }"
            role="dialog"
            aria-modal="true"
            aria-busy="true"
        >
            <div class="modal-dialog modal-dialog-scrollable app-float-modal-dialog app-float-modal-dialog--fs" role="document">
                <div class="modal-content app-float-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ title }}</h5>
                        <button type="button" class="app-float-close" aria-label="Close" disabled>&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="app-float-loading">
                            <span class="app-float-spinner" />
                            <p>Preparing form…</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" disabled>Cancel</button>
                        <button type="button" class="btn btn-primary" disabled>Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="active"
            class="modal-backdrop app-float-modal-backdrop app-float-modal-pending"
            :class="{ 'is-in': visible, 'is-leave': !visible }"
        />
    </Teleport>
</template>
