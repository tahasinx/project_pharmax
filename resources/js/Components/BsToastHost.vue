<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { dismissToast, toasts } from '@/toast';

const page = usePage();
const appName = computed(() => page.props.app?.name || 'Epharma');
const logo = computed(() => page.props.app?.logo || '/favicon.svg');

const typeClass = (type) => ({
    success: 'is-success',
    danger: 'is-danger',
    warning: 'is-warning',
    info: 'is-info',
}[type] || 'is-info');
</script>

<template>
    <Teleport to="body">
        <div id="toastHost" class="app-toast-host" aria-live="polite" aria-relevant="additions">
            <TransitionGroup name="app-toast" tag="div" class="app-toast-stack">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="app-toast"
                    :class="typeClass(toast.type)"
                    role="alert"
                    aria-atomic="true"
                >
                    <div class="app-toast-header">
                        <img :src="logo" alt="" class="app-toast-logo" height="18" width="18">
                        <strong class="app-toast-brand">{{ appName }}</strong>
                        <small class="app-toast-when">{{ toast.when || 'just now' }}</small>
                        <button type="button" class="app-toast-close" aria-label="Close" @click="dismissToast(toast.id)">&times;</button>
                    </div>
                    <div class="app-toast-body">
                        <strong v-if="toast.title" class="app-toast-title">{{ toast.title }}</strong>
                        <span>{{ toast.message }}</span>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style>
.app-toast-host {
    position: fixed;
    top: 0.85rem;
    right: 0.85rem;
    z-index: 10050;
    width: min(360px, calc(100vw - 1.5rem));
    pointer-events: none;
}

.app-toast-stack {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.app-toast {
    pointer-events: auto;
    width: 100%;
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.4rem;
    box-shadow: 0 0.5rem 1.25rem rgba(16, 24, 40, 0.14);
    backdrop-filter: blur(8px);
    overflow: hidden;
    position: relative;
}

.app-toast::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #5156be;
}

.app-toast.is-success::before { background: #34c38f; }
.app-toast.is-danger::before { background: #f46a6a; }
.app-toast.is-warning::before { background: #f1b44c; }
.app-toast.is-info::before { background: #50a5f1; }

.app-toast-header {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 0.75rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    background: rgba(255, 255, 255, 0.8);
}

.app-toast-logo {
    border-radius: 0.2rem;
    object-fit: contain;
    flex-shrink: 0;
}

.app-toast-brand {
    flex: 1 1 auto;
    font-size: 0.84rem;
    font-weight: 700;
    color: #212529;
}

.app-toast-when {
    color: #74788d;
    font-size: 0.72rem;
    white-space: nowrap;
}

.app-toast-close {
    border: 0;
    background: transparent;
    color: #74788d;
    font-size: 1.15rem;
    line-height: 1;
    padding: 0 0.15rem;
    cursor: pointer;
}

.app-toast-close:hover {
    color: #212529;
}

.app-toast-body {
    padding: 0.7rem 0.85rem 0.8rem;
    font-size: 0.86rem;
    color: #495057;
    word-break: break-word;
}

.app-toast-title {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.8rem;
    font-weight: 700;
}

.app-toast.is-success .app-toast-body { color: #1f7a57; }
.app-toast.is-danger .app-toast-body { color: #c0392b; }
.app-toast.is-warning .app-toast-body { color: #9a6b12; }
.app-toast.is-info .app-toast-body { color: #2a6fad; }

body[data-bs-theme="dark"] .app-toast,
body[data-bs-theme="dark"] .app-toast-header {
    background: rgba(33, 37, 41, 0.96);
    border-color: rgba(255, 255, 255, 0.08);
}

body[data-bs-theme="dark"] .app-toast-brand,
body[data-bs-theme="dark"] .app-toast-body,
body[data-bs-theme="dark"] .app-toast-close:hover {
    color: #e9ecef;
}

body[data-bs-theme="dark"] .app-toast-when,
body[data-bs-theme="dark"] .app-toast-close {
    color: #adb5bd;
}

.app-toast-enter-active,
.app-toast-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.app-toast-enter-from,
.app-toast-leave-to {
    opacity: 0;
    transform: translateX(14px);
}
</style>
