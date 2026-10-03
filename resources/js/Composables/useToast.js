import { clearToasts, pushToast } from '@/toast';

export function useToastNotifications() {
    const showSuccess = (message, title = 'Success') => pushToast(message, 'success', 5000, title);
    const showError = (message, title = 'Error') => pushToast(message, 'danger', 7000, title);
    const showWarning = (message, title = 'Warning') => pushToast(message, 'warning', 6000, title);
    const showInfo = (message, title = 'Notice') => pushToast(message, 'info', 5000, title);

    const showValidationErrors = (errors) => {
        if (typeof errors === 'string') {
            showError(errors, 'Validation');
            return;
        }

        if (!errors || typeof errors !== 'object') {
            return;
        }

        const lines = Object.values(errors).flat().map((message) => String(message)).filter(Boolean);
        if (lines.length) {
            showError(lines.join(' '), 'Validation');
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
