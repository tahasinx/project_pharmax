import { reactive } from 'vue';

export const toasts = reactive([]);

export function dismissToast(id) {
    const index = toasts.findIndex((toast) => toast.id === id);
    if (index >= 0) {
        toasts.splice(index, 1);
    }
}

export function clearToasts() {
    toasts.splice(0, toasts.length);
}

export function pushToast(message, type = 'danger', timeout = 5000) {
    const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    toasts.push({ id, message, type });
    window.setTimeout(() => dismissToast(id), timeout);
    return id;
}
