import { clearToasts, pushToast } from '@/toast';

export function useToastNotifications() {
    const showSuccess = (message) => pushToast(message, 'success');
    const showError = (message) => pushToast(message, 'danger', 7000);
    const showWarning = (message) => pushToast(message, 'warning', 6000);
    const showInfo = (message) => pushToast(message, 'info');

    const showValidationErrors = (errors) => {
        if (typeof errors === 'string') {
            showError(errors);
            return;
        }

        if (!errors || typeof errors !== 'object') {
            return;
        }

        const lines = Object.values(errors).flat().map((message) => String(message)).filter(Boolean);
        if (lines.length) {
            showError(lines.join(' '));
        }
    };

    return {
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showValidationErrors,
        clear: clearToasts,
    };
}
