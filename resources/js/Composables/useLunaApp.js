import { onUnmounted } from 'vue';

let depth = 0;

export function useLunaApp() {
    if (typeof document !== 'undefined') {
        depth += 1;
        document.body.classList.add('minia-app');
    }

    onUnmounted(() => {
        depth = Math.max(0, depth - 1);
        if (depth === 0 && typeof document !== 'undefined') {
            document.body.classList.remove('minia-app', 'sidebar-enable');
            document.body.removeAttribute('data-sidebar-size');
        }
    });
}
