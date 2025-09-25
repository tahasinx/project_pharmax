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

                        <!-- Email Configuration -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Configuration</h3>
                                <div class="space-y-6">
                                    <!-- Email Provider Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Provider</label>
                                        <select v-model="form.email_provider"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="smtp">SMTP</option>
                                            <option value="mailgun">Mailgun</option>
                                            <option value="ses">Amazon SES</option>
                                            <option value="sendmail">Sendmail</option>
                                        </select>
                                        <p class="mt-1 text-sm text-gray-500">Choose your email service provider</p>
                                    </div>

                                    <!-- SMTP Configuration -->
                                    <div v-if="form.email_provider === 'smtp'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">SMTP Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host *</label>
                                                <input v-model="form.smtp_host"
                                                       type="text"
                                                       placeholder="smtp.gmail.com"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port *</label>
                                                <input v-model.number="form.smtp_port"
                                                       type="number"
                                                       placeholder="587"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Username *</label>
                                                <input v-model="form.smtp_username"
                                                       type="text"
                                                       placeholder="your-email@gmail.com"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Password *</label>
                                                <input v-model="form.smtp_password"
                                                       type="password"
                                                       placeholder="Your app password"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                                                <select v-model="form.smtp_encryption"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="tls">TLS</option>
                                                    <option value="ssl">SSL</option>
                                                    <option value="">None</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                                                <input v-model="form.mail_from_name"
                                                       type="text"
                                                       placeholder="Your Company Name"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mailgun Configuration -->
                                    <div v-if="form.email_provider === 'mailgun'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Mailgun Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Mailgun Domain *</label>
                                                <input v-model="form.mailgun_domain"
                                                       type="text"
                                                       placeholder="mg.yourdomain.com"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Mailgun Secret *</label>
                                                <input v-model="form.mailgun_secret"
                                                       type="password"
                                                       placeholder="Your Mailgun secret key"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                                                <input v-model="form.mail_from_name"
                                                       type="text"
                                                       placeholder="Your Company Name"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Email</label>
                                                <input v-model="form.mail_from_address"
                                                       type="email"
                                                       placeholder="noreply@yourdomain.com"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Amazon SES Configuration -->
                                    <div v-if="form.email_provider === 'ses'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Amazon SES Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">AWS Access Key ID *</label>
                                                <input v-model="form.ses_key"
                                                       type="text"
                                                       placeholder="Your AWS access key"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">AWS Secret Access Key *</label>
                                                <input v-model="form.ses_secret"
                                                       type="password"
                                                       placeholder="Your AWS secret key"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">AWS Region *</label>
                                                <select v-model="form.ses_region"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="us-east-1">US East (N. Virginia)</option>
                                                    <option value="us-west-2">US West (Oregon)</option>
                                                    <option value="eu-west-1">Europe (Ireland)</option>
                                                    <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                                                <input v-model="form.mail_from_name"
                                                       type="text"
                                                       placeholder="Your Company Name"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Test Email Section -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-800 mb-4">Test Email Configuration</h4>
                                        <div class="flex items-end space-x-4">
                                            <div class="flex-1">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Test Email Address</label>
                                                <input v-model="testEmail"
                                                       type="email"
                                                       placeholder="test@example.com"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <button type="button"
                                                    @click="sendTestEmail"
                                                    :disabled="!testEmail || sendingTest"
                                                    class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                                                <svg v-if="sendingTest" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                                </svg>
                                                <span>{{ sendingTest ? 'Sending...' : 'Send Test Email' }}</span>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">Send a test email to verify your configuration</p>
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
    enable_notifications: '',
    // Email Configuration
    email_provider: 'smtp',
    smtp_host: '',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',
    smtp_encryption: 'tls',
    mailgun_domain: '',
    mailgun_secret: '',
    ses_key: '',
    ses_secret: '',
    ses_region: 'us-east-1',
    mail_from_name: '',
    mail_from_address: ''
})

// Test email functionality
const testEmail = ref('')
const sendingTest = ref(false)

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

const sendTestEmail = async () => {
    if (!testEmail.value) return

    sendingTest.value = true

    try {
        await router.post(route('settings.test-email'), {
            email: testEmail.value,
            email_config: form.value
        })

        // Show success message (you can implement a toast notification here)
        alert('Test email sent successfully!')
    } catch (error) {
        // Show error message
        alert('Failed to send test email. Please check your configuration.')
    } finally {
        sendingTest.value = false
    }
}
</script>
