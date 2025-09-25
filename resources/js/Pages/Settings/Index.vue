<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                System Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Simple error display -->
                <div v-if="Object.keys(props.errors).length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <h3 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li v-for="(error, field) in props.errors" :key="field">
                            • {{ field.replace('_', ' ') }}: {{ error }}
                        </li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <!-- General Settings -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                                        <input v-model="form.company_name"
                                               type="text"
                                               :class="props.errors.company_name ? 'border-red-500 bg-red-50' : 'border-gray-300'"
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p v-if="props.errors.company_name" class="mt-1 text-sm text-red-600">
                                            {{ props.errors.company_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Email</label>
                                        <input v-model="form.company_email"
                                               type="email"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                                        <input v-model="form.company_phone"
                                               type="tel"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                                        <textarea v-model="form.company_address"
                                                  rows="3"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Settings -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Tax Rate (%)</label>
                                        <input v-model.number="form.default_tax_rate"
                                               type="number"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                                        <input v-model="form.invoice_prefix"
                                               type="text"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Next Invoice Number</label>
                                        <input v-model.number="form.next_invoice_number"
                                               type="number"
                                               min="1"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Footer Text</label>
                                        <textarea v-model="form.invoice_footer"
                                                  rows="3"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Currency Settings -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Currency Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol *</label>
                                        <input v-model="form.currency_symbol"
                                               type="text"
                                               :class="props.errors.currency_symbol ? 'border-red-500 bg-red-50' : 'border-gray-300'"
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p v-if="props.errors.currency_symbol" class="mt-1 text-sm text-red-600">
                                            {{ props.errors.currency_symbol }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Position *</label>
                                        <select v-model="form.currency_position"
                                                :class="props.errors.currency_position ? 'border-red-500 bg-red-50' : 'border-gray-300'"
                                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="before">Before Amount ($100)</option>
                                            <option value="after">After Amount (100$)</option>
                                        </select>
                                        <p v-if="props.errors.currency_position" class="mt-1 text-sm text-red-600">
                                            {{ props.errors.currency_position }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">System Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                        <select v-model="form.timezone"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option v-for="tz in props.timezones" :key="tz" :value="tz">{{ tz }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                                        <select v-model="form.date_format"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="Y-m-d">YYYY-MM-DD</option>
                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                            <option value="M d, Y">Jan 1, 2024</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                                        <select v-model.number="form.items_per_page"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option :value="10">10</option>
                                            <option :value="25">25</option>
                                            <option :value="50">50</option>
                                            <option :value="100">100</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center">
                                        <input v-model="form.enable_notifications"
                                               type="checkbox"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label class="ml-2 block text-sm text-gray-900">
                                            Enable Notifications
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
// Simple approach - no complex imports needed

const props = defineProps({
    settings: Object,
    timezones: Array,
    errors: {
        type: Object,
        default: () => ({})
    }
})

const form = ref({
    company_name: '',
    company_email: '',
    company_phone: '',
    company_address: '',
    default_tax_rate: '',
    invoice_prefix: '',
    next_invoice_number: '',
    invoice_footer: '',
    currency_symbol: '',
    currency_position: '',
    timezone: '',
    date_format: '',
    items_per_page: '',
    enable_notifications: ''
})

onMounted(() => {
    // Populate form with existing settings
    if (props.settings) {
        Object.keys(form.value).forEach(key => {
            if (props.settings[key] !== undefined) {
                form.value[key] = props.settings[key]
            }
        })
    }
})

const submitForm = () => {
    router.put(route('settings.update'), form.value)
}
</script>
