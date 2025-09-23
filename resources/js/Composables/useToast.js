import { useToast } from 'vue-toastification'

export function useToastNotifications() {
    const toast = useToast()

    const showSuccess = (message, options = {}) => {
        toast.success(message, {
            position: 'top-right',
            timeout: 5000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: 'button',
            icon: true,
            ...options
        })
    }

    const showError = (message, options = {}) => {
        toast.error(message, {
            position: 'top-right',
            timeout: 7000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: 'button',
            icon: true,
            ...options
        })
    }

    const showWarning = (message, options = {}) => {
        toast.warning(message, {
            position: 'top-right',
            timeout: 6000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: 'button',
            icon: true,
            ...options
        })
    }

    const showInfo = (message, options = {}) => {
        toast.info(message, {
            position: 'top-right',
            timeout: 5000,
            closeOnClick: true,
            pauseOnFocusLoss: true,
            pauseOnHover: true,
            draggable: true,
            draggablePercent: 0.6,
            showCloseButtonOnHover: false,
            hideProgressBar: false,
            closeButton: 'button',
            icon: true,
            ...options
        })
    }

    const showValidationErrors = (errors, options = {}) => {
        if (typeof errors === 'object' && errors !== null) {
            const errorMessages = []

            Object.keys(errors).forEach(field => {
                const fieldError = errors[field]

                if (Array.isArray(fieldError)) {
                    fieldError.forEach(message => {
                        // Ensure message is a string, not an array of characters
                        const messageStr = Array.isArray(message) ? message.join('') : String(message)
                        errorMessages.push(`${field}: ${messageStr}`)
                    })
                } else {
                    // Ensure the error value is a string, not an array of characters
                    const errorStr = Array.isArray(fieldError) ? fieldError.join('') : String(fieldError)
                    errorMessages.push(`${field}: ${errorStr}`)
                }
            })

            if (errorMessages.length > 0) {
                showError(errorMessages.join('\n'), {
                    timeout: 10000,
                    ...options
                })
            }
        } else if (typeof errors === 'string') {
            showError(errors, options)
        }
    }

    const clear = () => {
        toast.clear()
    }

    return {
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showValidationErrors,
        clear
    }
}
