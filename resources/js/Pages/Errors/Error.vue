<template>
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <!-- Error Icon -->
                <div class="mx-auto h-24 w-24 text-gray-400 mb-8">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>

                <!-- Error Message -->
                <h1 class="text-6xl font-bold text-gray-900 mb-4">{{ status }}</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">{{ title }}</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    {{ message }}
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="route('dashboard')"
                          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Go to Dashboard
                    </Link>

                    <button @click="goBack"
                            class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Go Back
                    </button>
                </div>

                <!-- Error Details (only show in development) -->
                <div v-if="error && isDevelopment" class="mt-8 p-4 bg-red-50 border border-red-200 rounded-md">
                    <h4 class="text-sm font-medium text-red-800 mb-2">Error Details:</h4>
                    <pre class="text-xs text-red-700 whitespace-pre-wrap">{{ error }}</pre>
                </div>

                <!-- Contact Information -->
                <div class="mt-12 text-center">
                    <p class="text-sm text-gray-500">
                        Need help?
                        <a href="mailto:support@pharmacare.com" class="text-blue-600 hover:text-blue-500 font-medium">
                            Contact Support
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    status: Number,
    error: String
})

const isDevelopment = computed(() => {
    return import.meta.env.DEV
})

const title = computed(() => {
    switch (props.status) {
        case 403:
            return 'Access Forbidden'
        case 404:
            return 'Page Not Found'
        case 419:
            return 'Page Expired'
        case 429:
            return 'Too Many Requests'
        case 500:
            return 'Internal Server Error'
        case 503:
            return 'Service Unavailable'
        default:
            return 'Something Went Wrong'
    }
})

const message = computed(() => {
    switch (props.status) {
        case 403:
            return 'You do not have permission to access this resource. Please contact your administrator if you believe this is an error.'
        case 404:
            return 'Sorry, we couldn\'t find the page you\'re looking for. The page might have been moved, deleted, or you might have entered the wrong URL.'
        case 419:
            return 'Your session has expired. Please refresh the page and try again.'
        case 429:
            return 'You have made too many requests. Please wait a moment before trying again.'
        case 500:
            return 'Something went wrong on our end. We\'re working to fix this issue. Please try again later.'
        case 503:
            return 'The service is temporarily unavailable. Please try again later.'
        default:
            return 'An unexpected error occurred. Please try again or contact support if the problem persists.'
    }
})

const goBack = () => {
    window.history.back()
}
</script>
