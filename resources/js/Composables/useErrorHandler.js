import { router } from '@inertiajs/vue3'
import { useToastNotifications } from './useToast'

const isValidationBag = (value) => {
    if (!value || typeof value !== 'object' || Array.isArray(value) || value.response) {
        return false
    }
    const keys = Object.keys(value)
    if (!keys.length) return false
    return keys.every((key) => typeof value[key] === 'string' || Array.isArray(value[key]))
}

const unwrapErrors = (payload) => {
    if (!payload) return null
    if (isValidationBag(payload)) return payload
    if (isValidationBag(payload.errors)) return payload.errors
    if (isValidationBag(payload.detail?.errors)) return payload.detail.errors
    return null
}

export function useErrorHandler() {
    const { showSuccess, showError, showWarning, showInfo, showValidationErrors } = useToastNotifications()

    const handleResponse = (response) => {
        if (!response?.props) return

        const flash = response.props.flash || {}
        if (flash.success) showSuccess(flash.success)
        if (flash.error) showError(flash.error)
        if (flash.warning) showWarning(flash.warning)
        if (flash.info) showInfo(flash.info)
    }

    const handleError = (error) => {
        const validation = unwrapErrors(error) || unwrapErrors(error?.detail)
        if (validation) {
            showValidationErrors(validation)
            return
        }

        if (!error) {
            showError('An unexpected error occurred.')
            return
        }

        if (error.response) {
            const { status, data } = error.response
            switch (status) {
                case 422:
                    if (data?.errors) showValidationErrors(data.errors)
                    else if (data?.message) showError(data.message)
                    break
                case 403:
                    showError('You do not have permission to perform this action.')
                    break
                case 404:
                    showError('The requested resource was not found.')
                    break
                case 500:
                    showError('A server error occurred. Please try again later.')
                    break
                default:
                    showError(data?.message || 'An unexpected error occurred.')
            }
            return
        }

        if (typeof error === 'string') {
            showError(error)
            return
        }

        // Ignore cancel / abort noise
        if (error.name === 'AbortError' || /cancel|abort/i.test(String(error.message || ''))) {
            return
        }

        if (error.message) {
            showError(error.message)
            return
        }

        showError('An unexpected error occurred.')
    }

    const handleFormError = (error) => {
        const validation = unwrapErrors(error)
        if (validation) {
            showValidationErrors(validation)
            return validation
        }

        if (error?.response?.status === 422) {
            const { data } = error.response
            if (data?.errors) {
                showValidationErrors(data.errors)
                return data.errors
            }
        }

        handleError(error)
        return {}
    }

    const setupGlobalHandlers = () => {
        router.on('success', (event) => {
            handleResponse(event.detail?.page ?? event.detail ?? event)
        })

        router.on('error', (event) => {
            const payload = event?.detail ?? event
            handleFormError(payload?.errors ?? payload)
        })

        router.on('exception', (event) => {
            const exception = event?.detail?.exception ?? event?.detail ?? event
            if (exception?.name === 'AbortError') return
            if (exception?.message) showError(exception.message)
            else showError('An unexpected error occurred.')
        })
    }

    return {
        handleResponse,
        handleError,
        handleFormError,
        setupGlobalHandlers,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showValidationErrors,
    }
}
