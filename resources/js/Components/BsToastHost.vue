<script setup>
import { dismissToast, toasts } from '@/toast';
</script>

<template>
    <Teleport to="body">
        <div id="toastHost" class="toast-container position-fixed top-0 end-0 p-3">
            <TransitionGroup name="bs-toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="toast show"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                >
                    <div class="toast-header">
                        <img src="/favicon.svg" class="rounded me-2" width="20" height="20" alt="">
                        <strong class="me-auto">Epharma</strong>
                        <small class="text-body-secondary">just now</small>
                        <button type="button" class="btn-close" aria-label="Close" @click="dismissToast(toast.id)" />
                    </div>
                    <div class="toast-body" :class="`text-${toast.type}`">{{ toast.message }}</div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style>
#toastHost.toast-container {
    max-width: 100%;
    pointer-events: none;
    position: fixed;
    right: 0;
    top: 0;
    width: max-content;
    z-index: 1090;
    padding: 1rem;
}

#toastHost .toast {
    background-clip: padding-box;
    background-color: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(0, 0, 0, 0.175);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    color: #212529;
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    max-width: 100%;
    pointer-events: auto;
    width: 350px;
    backdrop-filter: blur(6px);
}

#toastHost .toast-header {
    align-items: center;
    background-clip: padding-box;
    background-color: rgba(255, 255, 255, 0.85);
    border-bottom: 1px solid rgba(0, 0, 0, 0.175);
    border-top-left-radius: calc(0.375rem - 1px);
    border-top-right-radius: calc(0.375rem - 1px);
    color: rgba(33, 37, 41, 0.75);
    display: flex;
    padding: 0.5rem 0.75rem;
}

#toastHost .toast-header .rounded {
    border-radius: 0.375rem;
}

#toastHost .toast-header .me-2 {
    margin-right: 0.5rem;
}

#toastHost .toast-header .me-auto {
    flex: 1 1 auto;
    margin-right: auto;
}

#toastHost .toast-header strong {
    color: #212529;
    font-weight: 700;
}

#toastHost .text-body-secondary {
    color: rgba(33, 37, 41, 0.75);
    font-size: 0.875em;
}

#toastHost .toast-body {
    padding: 0.75rem;
    word-wrap: break-word;
}

#toastHost .toast-body.text-danger {
    color: #dc3545;
}

#toastHost .toast-body.text-success {
    color: #2f6f4e;
    font-weight: 500;
    letter-spacing: 0.01em;
}

#toastHost .toast-body.text-warning {
    color: #b58105;
    font-weight: 500;
}

#toastHost .toast-body.text-info {
    color: #0d6efd;
}

#toastHost .btn-close {
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center / 1em auto no-repeat;
    border: 0;
    box-sizing: content-box;
    cursor: pointer;
    flex: 0 0 auto;
    height: 1em;
    margin-left: 0.75rem;
    margin-right: -0.375rem;
    opacity: 0.5;
    padding: 0.25em;
    width: 1em;
}

#toastHost .btn-close:hover {
    opacity: 1;
}

.bs-toast-enter-active,
.bs-toast-leave-active {
    transition: opacity 0.15s linear;
}

.bs-toast-enter-from,
.bs-toast-leave-to {
    opacity: 0;
}
</style>
