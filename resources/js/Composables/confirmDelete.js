import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

const confirmClass = {
    popup: 'epharma-confirm',
    icon: 'epharma-confirm-icon',
    title: 'epharma-confirm-title',
    htmlContainer: 'epharma-confirm-text',
    actions: 'epharma-confirm-actions',
    confirmButton: 'epharma-confirm-delete',
    cancelButton: 'epharma-confirm-cancel',
    closeButton: 'epharma-confirm-close',
};

export function notify({ title, text, tone = 'success' }) {
    const icon = tone === 'error' ? 'bi-exclamation-lg' : 'bi-check-lg';
    return Swal.fire({
        title,
        text,
        iconHtml: `<i class="bi ${icon}"></i>`,
        showConfirmButton: true,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: {
            popup: 'epharma-confirm',
            icon: `epharma-confirm-icon is-${tone}`,
            title: 'epharma-confirm-title',
            htmlContainer: 'epharma-confirm-text',
            actions: 'epharma-confirm-actions',
            confirmButton: 'epharma-confirm-ok',
        },
    });
}

export function confirmDelete({
    title = 'Delete this item?',
    text = 'This action cannot be undone.',
} = {}) {
    return Swal.fire({
        title,
        text,
        iconHtml: '<i class="bi bi-trash"></i>',
        showCloseButton: true,
        showCancelButton: true,
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: true,
        confirmButtonText: '<i class="bi bi-trash"></i><span>Delete</span>',
        cancelButtonText: 'Cancel',
        customClass: confirmClass,
    });
}

export function destroyRecord(routeName, id, title, successText) {
    return confirmDelete({ title }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        router.delete(route(routeName, id), {
            onSuccess: () => notify({ title: 'Deleted', text: successText }),
            onError: () => notify({ title: 'Could not delete', text: 'Something went wrong while deleting.', tone: 'error' }),
        });
    });
}
