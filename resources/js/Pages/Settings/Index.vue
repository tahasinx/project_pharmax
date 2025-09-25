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

                        <!-- SMS Configuration -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">SMS Configuration</h3>
                                <div class="space-y-6">
                                    <!-- SMS Provider Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">SMS Provider</label>
                                        <select v-model="form.sms_provider"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="twilio">Twilio</option>
                                            <option value="nexmo">Nexmo (Vonage)</option>
                                            <option value="custom">Custom API</option>
                                        </select>
                                        <p class="mt-1 text-sm text-gray-500">Choose your SMS service provider</p>
                                    </div>


                                    <!-- Twilio Configuration -->
                                    <div v-if="form.sms_provider === 'twilio'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Twilio Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Account SID *</label>
                                                <input v-model="form.twilio_sid"
                                                       type="text"
                                                       placeholder="Your Twilio Account SID"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Auth Token *</label>
                                                <input v-model="form.twilio_token"
                                                       type="password"
                                                       placeholder="Your Twilio Auth Token"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Number *</label>
                                                <input v-model="form.twilio_from"
                                                       type="text"
                                                       placeholder="+1234567890"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Country Code</label>
                                                <input v-model="form.sms_country_code"
                                                       type="text"
                                                       placeholder="1"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nexmo Configuration -->
                                    <div v-if="form.sms_provider === 'nexmo'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Nexmo (Vonage) Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key *</label>
                                                <input v-model="form.nexmo_key"
                                                       type="text"
                                                       placeholder="Your Nexmo API Key"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret *</label>
                                                <input v-model="form.nexmo_secret"
                                                       type="password"
                                                       placeholder="Your Nexmo API Secret"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">From Number *</label>
                                                <input v-model="form.nexmo_from"
                                                       type="text"
                                                       placeholder="Your Nexmo number"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Default Country Code</label>
                                                <input v-model="form.sms_country_code"
                                                       type="text"
                                                       placeholder="44"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom API Configuration -->
                                    <div v-if="form.sms_provider === 'custom'" class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-800 border-b pb-2">Custom API Settings</h4>

                                        <!-- Basic API Configuration -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">API URL *</label>
                                                <input v-model="form.sms_api_url"
                                                       type="url"
                                                       placeholder="https://your-sms-api.com/send"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">HTTP Method</label>
                                                <select v-model="form.sms_http_method"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="POST">POST</option>
                                                    <option value="GET">GET</option>
                                                    <option value="PUT">PUT</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Custom Parameters -->
                                        <div class="border rounded-lg p-4 bg-gray-50">
                                            <div class="flex justify-between items-center mb-4">
                                                <h5 class="text-sm font-medium text-gray-700">Custom Parameters</h5>
                                                <button type="button"
                                                        @click="addCustomParameter"
                                                        class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-1 px-3 rounded flex items-center space-x-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>Add Parameter</span>
                                                </button>
                                            </div>

                                            <div v-if="form.sms_custom_params.length === 0" class="text-center text-gray-500 py-4">
                                                No custom parameters added yet. Click "Add Parameter" to get started.
                                            </div>

                                            <div v-for="(param, index) in form.sms_custom_params"
                                                 :key="index"
                                                 class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3 p-3 bg-white rounded border">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Parameter Name</label>
                                                    <input v-model="param.name"
                                                           type="text"
                                                           placeholder="e.g., api_key, senderid"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Parameter Value</label>
                                                    <input v-model="param.value"
                                                           type="text"
                                                           :placeholder="getParameterPlaceholder(param.name)"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                                    <select v-model="param.type"
                                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                        <option value="static">Static Value</option>
                                                        <option value="phone">Phone Number</option>
                                                        <option value="message">Message Text</option>
                                                        <option value="placeholder">Custom Placeholder</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-end">
                                                    <button type="button"
                                                            @click="removeCustomParameter(index)"
                                                            class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>Remove</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Common Parameters Presets -->
                                        <div class="border rounded-lg p-4 bg-blue-50">
                                            <h5 class="text-sm font-medium text-gray-700 mb-3">Common Parameter Presets</h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                                <button type="button"
                                                        @click="addPresetParameters('bulksms')"
                                                        class="text-left p-3 bg-white rounded border hover:bg-gray-50 transition-colors">
                                                    <div class="font-medium text-sm">BulkSMS Structure</div>
                                                    <div class="text-xs text-gray-500">api_key, senderid, number, message</div>
                                                </button>
                                                <button type="button"
                                                        @click="addPresetParameters('generic')"
                                                        class="text-left p-3 bg-white rounded border hover:bg-gray-50 transition-colors">
                                                    <div class="font-medium text-sm">Generic Structure</div>
                                                    <div class="text-xs text-gray-500">key, from, to, text</div>
                                                </button>
                                            </div>

                                            <!-- Common Parameter Names Reference -->
                                            <div class="border-t pt-3">
                                                <h6 class="text-xs font-medium text-gray-600 mb-2">Common Parameter Names by Provider:</h6>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                                    <div class="bg-white p-2 rounded border">
                                                        <div class="font-medium text-gray-700 mb-1">Phone/Number:</div>
                                                        <div class="text-gray-500">number, to, phone, recipient, mobile</div>
                                                    </div>
                                                    <div class="bg-white p-2 rounded border">
                                                        <div class="font-medium text-gray-700 mb-1">Message/Text:</div>
                                                        <div class="text-gray-500">message, text, body, content, msg</div>
                                                    </div>
                                                    <div class="bg-white p-2 rounded border">
                                                        <div class="font-medium text-gray-700 mb-1">Sender/From:</div>
                                                        <div class="text-gray-500">senderid, from, sender, source</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Custom Response Mapping -->
                                        <div class="border rounded-lg p-4 bg-green-50">
                                            <div class="flex justify-between items-center mb-4">
                                                <h5 class="text-sm font-medium text-gray-700">Custom Response Mapping</h5>
                                                <button type="button"
                                                        @click="addResponseMapping"
                                                        class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-3 rounded flex items-center space-x-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>Add Response</span>
                                                </button>
                                            </div>

                                            <p class="text-xs text-gray-600 mb-4">
                                                Map any response from your SMS provider to user-friendly messages.
                                                Supports JSON responses (with key-value pairs) or plain string responses.
                                                The system will automatically detect and show your custom messages in notifications.
                                            </p>

                                            <div v-if="form.sms_response_mappings.length === 0" class="text-center text-gray-500 py-4">
                                                No response mappings added yet. Click "Add Response" to get started.
                                            </div>

                                            <div v-for="(mapping, index) in form.sms_response_mappings"
                                                 :key="index"
                                                 class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-white rounded border">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Response Key</label>
                                                    <input v-model="mapping.key"
                                                           type="text"
                                                           placeholder="e.g., status, code, result (leave empty for string match)"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Response Value</label>
                                                    <input v-model="mapping.value"
                                                           type="text"
                                                           placeholder="e.g., success, 200, OK, error"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div class="flex items-end space-x-2">
                                                    <div class="flex-1">
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Custom Message</label>
                                                        <input v-model="mapping.message"
                                                               type="text"
                                                               placeholder="Your custom message here"
                                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                    </div>
                                                    <button type="button"
                                                            @click="removeResponseMapping(index)"
                                                            class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>Remove</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Response Mapping Examples -->
                                            <div class="border-t pt-3">
                                                <h6 class="text-xs font-medium text-gray-600 mb-2">Response Mapping Examples:</h6>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                                    <div class="bg-white p-2 rounded border">
                                                        <div class="font-medium text-gray-700 mb-1">JSON Response:</div>
                                                        <div class="text-gray-500">Key: "status", Value: "success"</div>
                                                        <div class="text-gray-500">Key: "code", Value: "200"</div>
                                                    </div>
                                                    <div class="bg-white p-2 rounded border">
                                                        <div class="font-medium text-gray-700 mb-1">String Response:</div>
                                                        <div class="text-gray-500">Key: "", Value: "OK"</div>
                                                        <div class="text-gray-500">Key: "", Value: "SUCCESS"</div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Leave "Response Key" empty to match the entire response value as a string.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Test SMS Section -->
                                    <div class="border-t pt-6">
                                        <h4 class="text-md font-medium text-gray-800 mb-4">Test SMS Configuration</h4>
                                        <div class="flex items-end space-x-4">
                                            <div class="flex-1">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Test Phone Number</label>
                                                <input v-model="testSms"
                                                       type="tel"
                                                       placeholder="+8801612345678"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <button type="button"
                                                    @click="sendTestSms"
                                                    :disabled="!testSms || sendingSms"
                                                    class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                                                <svg v-if="sendingSms" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                                </svg>
                                                <span>{{ sendingSms ? 'Sending...' : 'Send Test SMS' }}</span>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">Send a test SMS to verify your configuration</p>
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

    <!-- Response Modal -->
    <div v-if="responseModal.open" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-40" @click="closeResponseModal"></div>
        <div class="relative bg-white w-full max-w-3xl mx-4 rounded-lg shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b">
                <h3 class="text-lg font-semibold text-gray-900">{{ responseModal.title }}</h3>
                <button @click="closeResponseModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-2">Request</div>
                    <pre class="text-xs bg-gray-50 border rounded p-3 overflow-x-auto"><code>{{ formatJson(responseModal.request) }}</code></pre>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-2">App Response</div>
                    <pre class="text-xs bg-gray-50 border rounded p-3 overflow-x-auto"><code>{{ formatJson(responseModal.appResponse) }}</code></pre>
                </div>
                <div v-if="responseModal.remoteRaw">
                    <div class="text-sm font-medium text-gray-700 mb-2">Remote API Raw Response</div>
                    <pre class="text-xs bg-gray-50 border rounded p-3 overflow-x-auto"><code>{{ responseModal.remoteRaw }}</code></pre>
                </div>
                <div v-if="responseModal.remoteParsed">
                    <div class="text-sm font-medium text-gray-700 mb-2">Remote API Parsed</div>
                    <pre class="text-xs bg-gray-50 border rounded p-3 overflow-x-auto"><code>{{ formatJson(responseModal.remoteParsed) }}</code></pre>
                </div>
            </div>
            <div class="px-5 py-3 border-t flex justify-end">
                <button @click="closeResponseModal" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded">Close</button>
            </div>
        </div>
    </div>

    <!-- Beautiful Toast Notification -->
    <div v-if="toast.show"
         class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 transform transition-all duration-300 ease-in-out"
         :class="{
             'border-green-500': toast.type === 'success',
             'border-red-500': toast.type === 'error',
             'border-blue-500': toast.type === 'info'
         }">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <!-- Success Icon -->
                    <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Error Icon -->
                    <svg v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Info Icon -->
                    <svg v-else class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        {{ toast.title }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ toast.message }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="hideToast"
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-1 bg-gray-200 rounded-b-lg overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-b-lg animate-pulse"
                 :style="{ animationDuration: toast.duration + 'ms' }"></div>
        </div>
    </div>
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

// Response Modal State
const responseModal = ref({
    open: false,
    title: '',
    request: null,
    appResponse: null,
    remoteRaw: '',
    remoteParsed: null
})

const openResponseModal = ({ title, request, appResponse, remoteRaw, remoteParsed }) => {
    responseModal.value = {
        open: true,
        title,
        request,
        appResponse,
        remoteRaw,
        remoteParsed
    }
}

const closeResponseModal = () => {
    responseModal.value.open = false
}

const formatJson = (obj) => {
    try {
        return JSON.stringify(obj, null, 2)
    } catch (e) {
        return String(obj || '')
    }
}

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
    mail_from_address: '',
    // SMS Configuration
    sms_provider: 'custom',
    sms_api_url: '',
    sms_http_method: 'POST',
    sms_custom_params: [],
    sms_response_mappings: [],
    twilio_sid: '',
    twilio_token: '',
    twilio_from: '',
    nexmo_key: '',
    nexmo_secret: '',
    nexmo_from: ''
})

// Test email functionality
const testEmail = ref('')
const sendingTest = ref(false)

// Test SMS functionality
const testSms = ref('')
const sendingSms = ref(false)

// Toast notification system
const toast = ref({
    show: false,
    type: 'success', // success, error, info
    title: '',
    message: '',
    duration: 5000
})

const showToast = (type, title, message, duration = 5000) => {
    toast.value = {
        show: true,
        type,
        title,
        message,
        duration
    }

    setTimeout(() => {
        toast.value.show = false
    }, duration)
}

const hideToast = () => {
    toast.value.show = false
}

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
        const response = await fetch('/api/settings/test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                email: testEmail.value,
                email_config: form.value
            })
        })

        const data = await response.json()

        if (data.success) {
            showToast('success', 'Email Sent Successfully!',
                     `Test email has been sent to ${testEmail.value}. Please check your inbox.`)
            openResponseModal({
                title: 'Test Email Result',
                request: { email: testEmail.value, provider: form.value.email_provider },
                appResponse: data,
                remoteRaw: null,
                remoteParsed: null
            })
        } else {
            showToast('error', 'Email Failed', data.message)
            openResponseModal({
                title: 'Test Email Error',
                request: { email: testEmail.value, provider: form.value.email_provider },
                appResponse: data,
                remoteRaw: null,
                remoteParsed: null
            })
        }
    } catch (error) {
        showToast('error', 'Email Failed', 'Failed to send test email. Please check your configuration.')
    } finally {
        sendingTest.value = false
    }
}

const sendTestSms = async () => {
    if (!testSms.value) return

    sendingSms.value = true

    try {
        const response = await fetch('/api/settings/test-sms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                phone: testSms.value,
                sms_config: form.value
            })
        })

        const data = await response.json()

        if (data.success) {
            // Check for custom response message
            const customMessage = getCustomResponseMessage(data.data?.response || '')
            const toastMessage = customMessage || `Test SMS has been sent to ${testSms.value}. Please check your phone.`

            showToast('success', 'SMS Sent Successfully!', toastMessage)

            let remoteParsed = null
            try {
                remoteParsed = JSON.parse(data.data?.response || '{}')
            } catch (e) {}
            openResponseModal({
                title: 'Test SMS Result',
                request: { phone: testSms.value, provider: form.value.sms_provider, url: form.value.sms_api_url },
                appResponse: data,
                remoteRaw: data.data?.response || '',
                remoteParsed
            })
        } else {
            // Check for custom response message even for errors
            const customMessage = getCustomResponseMessage(data.data?.response || '')
            const toastMessage = customMessage || data.message

            showToast('error', 'SMS Failed', toastMessage)

            let remoteParsed = null
            try {
                remoteParsed = JSON.parse(data.data?.response || '{}')
            } catch (e) {}
            openResponseModal({
                title: 'Test SMS Error',
                request: { phone: testSms.value, provider: form.value.sms_provider, url: form.value.sms_api_url },
                appResponse: data,
                remoteRaw: data.data?.response || '',
                remoteParsed
            })
        }
    } catch (error) {
        showToast('error', 'SMS Failed', 'Failed to send test SMS. Please check your configuration.')
    } finally {
        sendingSms.value = false
    }
}

// Custom SMS parameter management
const addCustomParameter = () => {
    form.value.sms_custom_params.push({
        name: '',
        value: '',
        type: 'static'
    })
}

const removeCustomParameter = (index) => {
    form.value.sms_custom_params.splice(index, 1)
}

const addPresetParameters = (type) => {
    // Clear existing parameters
    form.value.sms_custom_params = []

    if (type === 'bulksms') {
        form.value.sms_custom_params = [
            { name: 'api_key', value: '', type: 'static' },
            { name: 'senderid', value: '', type: 'static' },
            { name: 'number', value: '{phone}', type: 'phone' },
            { name: 'message', value: '{message}', type: 'message' }
        ]
    } else if (type === 'generic') {
        form.value.sms_custom_params = [
            { name: 'key', value: '', type: 'static' },
            { name: 'from', value: '', type: 'static' },
            { name: 'to', value: '{phone}', type: 'phone' },
            { name: 'text', value: '{message}', type: 'message' }
        ]
    }
}

const getParameterPlaceholder = (paramName) => {
    const commonNames = {
        // Phone/Number parameters
        'number': 'Use {phone} for dynamic phone number',
        'to': 'Use {phone} for dynamic phone number',
        'phone': 'Use {phone} for dynamic phone number',
        'recipient': 'Use {phone} for dynamic phone number',
        'mobile': 'Use {phone} for dynamic phone number',

        // Message/Text parameters
        'message': 'Use {message} for dynamic message text',
        'text': 'Use {message} for dynamic message text',
        'body': 'Use {message} for dynamic message text',
        'content': 'Use {message} for dynamic message text',
        'msg': 'Use {message} for dynamic message text',

        // Sender/From parameters
        'senderid': 'Your sender ID',
        'from': 'Your sender ID or phone number',
        'sender': 'Your sender ID',
        'source': 'Your sender ID',

        // API Key parameters
        'api_key': 'Your API key',
        'key': 'Your API key',
        'token': 'Your API token',
        'auth': 'Your authentication token'
    }

    return commonNames[paramName.toLowerCase()] || 'Enter parameter value'
}

// Response mapping management
const addResponseMapping = () => {
    form.value.sms_response_mappings.push({
        key: '',
        value: '',
        message: ''
    })
}

const removeResponseMapping = (index) => {
    form.value.sms_response_mappings.splice(index, 1)
}


// Function to get custom message based on response mappings
const getCustomResponseMessage = (remoteResponse) => {
    if (!remoteResponse || !form.value.sms_response_mappings.length) {
        return null
    }

    // Convert response to string for comparison
    const responseStr = typeof remoteResponse === 'string' ? remoteResponse : JSON.stringify(remoteResponse)

    for (const mapping of form.value.sms_response_mappings) {
        if (!mapping.value || !mapping.message) continue

        // Case 1: Empty key - match entire response value
        if (!mapping.key || mapping.key.trim() === '') {
            if (responseStr === mapping.value || responseStr.includes(mapping.value)) {
                return mapping.message
            }
        }
        // Case 2: Has key - try to parse as JSON and match specific field
        else {
            try {
                const parsed = typeof remoteResponse === 'string' ? JSON.parse(remoteResponse) : remoteResponse

                // Check if the key exists and value matches
                if (parsed[mapping.key] !== undefined) {
                    const responseValue = parsed[mapping.key]
                    if (responseValue === mapping.value ||
                        responseValue === String(mapping.value) ||
                        String(responseValue) === mapping.value) {
                        return mapping.message
                    }
                }
            } catch (e) {
                // If JSON parsing fails, try string matching with key
                const keyPattern = new RegExp(`"${mapping.key}"\\s*:\\s*"${mapping.value}"`, 'i')
                if (keyPattern.test(responseStr)) {
                    return mapping.message
                }
            }
        }
    }

    return null
}
</script>
