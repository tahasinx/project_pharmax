import { router } from '@inertiajs/vue3'
import { useToastNotifications } from './useToast'

export function useErrorHandler() {
    const { showSuccess, showError, showWarning, showInfo, showValidationErrors } = useToastNotifications()

    // Handle Inertia.js responses
    const handleResponse = (response) => {
        if (response && response.props) {
            // Handle flash messages
            if (response.props.flash) {
                if (response.props.flash.success) {
                    showSuccess(response.props.flash.success)
                }
                if (response.props.flash.error) {
                    showError(response.props.flash.error)
                }
                if (response.props.flash.warning) {
                    showWarning(response.props.flash.warning)
                }
                if (response.props.flash.info) {
                    showInfo(response.props.flash.info)
                }
            }

            // Handle validation errors
            if (response.props.errors && Object.keys(response.props.errors).length > 0) {
                showValidationErrors(response.props.errors)
            }
        }
    }

    // Handle HTTP errors
    const handleError = (error) => {
        if (error.response) {
            const { status, data } = error.response

            switch (status) {
                case 422:
                    // Validation errors
                    if (data.errors) {
                        showValidationErrors(data.errors)
                    } else if (data.message) {
                        showError(data.message)
                    }
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
                    showError(data.message || 'An unexpected error occurred.')
            }
        } else if (error.message) {
            showError(error.message)
        } else {
            showError('An unexpected error occurred.')
        }
    }

    // Handle form submission errors
    const handleFormError = (error) => {
        // Handle Inertia.js validation errors (passed directly as object)
        if (typeof error === 'object' && error !== null && !error.response) {
            showValidationErrors(error)
            return error
        }

        // Handle HTTP response errors
        if (error.response && error.response.status === 422) {
            const { data } = error.response
            if (data.errors) {
                showValidationErrors(data.errors)
                return data.errors // Return errors for form handling
            }
        }

        handleError(error)
        return {}
    }

    // Setup global error handling
    const setupGlobalHandlers = () => {
        // Handle Inertia.js success events
        router.on('success', (event) => {
            handleResponse(event.detail.page)
        })

        // Handle Inertia.js error events
        router.on('error', (event) => {
            handleError(event.detail.error)
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
        showValidationErrors
    }
}
