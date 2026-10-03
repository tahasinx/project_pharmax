<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">General</h4>
                <p class="text-muted mb-0 font-size-13">Company identity, invoices, and delivery channels.</p>
            </div>
        </template>

        <div v-if="flash.success" class="alert alert-success">{{ flash.success }}</div>

        <div class="settings-shell">
            <aside class="settings-shell-nav">
                <SettingsNav active="general" />
            </aside>

            <form class="settings-shell-main" novalidate @submit.prevent="submitForm">
                <div v-if="hasErrors" class="settings-alert">
                    <strong>Please fix the highlighted fields.</strong>
                    <ul>
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <nav class="settings-tabs" aria-label="Settings sections">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="settings-tab"
                        :class="{ active: activeTab === tab.key }"
                        @click="setTab(tab.key)"
                    >
                        <i :class="['bi', tab.icon]" />
                        <span>{{ tab.label }}</span>
                    </button>
                </nav>

                <section v-show="activeTab === 'company'" class="settings-panel">
                    <header class="settings-panel-head">
                        <div>
                            <h5>Company & branding</h5>
                            <p>Identity shown in the app shell, invoices, and browser tab.</p>
                        </div>
                    </header>
                    <div class="brand-grid mb-3">
                        <div class="brand-card">
                            <div class="brand-preview brand-preview-logo">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo preview">
                                <span v-else class="brand-placeholder"><i class="bi bi-image" /> Logo</span>
                            </div>
                            <div class="brand-meta">
                                <strong>Company logo</strong>
                                <span>PNG or JPG · max 2 MB · square works best</span>
                                <div class="brand-actions">
                                    <label class="btn btn-sm btn-soft-primary mb-0">
                                        {{ form.logo ? 'Change file' : 'Upload logo' }}
                                        <input type="file" accept="image/*" class="d-none" @change="onLogoPick">
                                    </label>
                                    <button
                                        v-if="logoPreview"
                                        type="button"
                                        class="btn btn-sm btn-soft-secondary"
                                        @click="clearLogo"
                                    >Remove</button>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card">
                            <div class="brand-preview brand-preview-favicon">
                                <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon preview">
                                <span v-else class="brand-placeholder"><i class="bi bi-app" /> Icon</span>
                            </div>
                            <div class="brand-meta">
                                <strong>Favicon</strong>
                                <span>PNG, ICO, or SVG · max 1 MB</span>
                                <div class="brand-actions">
                                    <label class="btn btn-sm btn-soft-primary mb-0">
                                        {{ form.favicon ? 'Change file' : 'Upload favicon' }}
                                        <input type="file" accept="image/*,.ico" class="d-none" @change="onFaviconPick">
                                    </label>
                                    <button
                                        v-if="faviconPreview"
                                        type="button"
                                        class="btn btn-sm btn-soft-secondary"
                                        @click="clearFavicon"
                                    >Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings-grid">
                        <label class="settings-field">
                            <span>Company name <em>*</em></span>
                            <input v-model="form.company_name" type="text" required :class="{ 'is-invalid': form.errors.company_name }">
                        </label>
                        <label class="settings-field">
                            <span>Company email</span>
                            <input v-model="form.company_email" type="email">
                        </label>
                        <label class="settings-field">
                            <span>Company phone</span>
                            <input v-model="form.company_phone" type="tel">
                        </label>
                        <label class="settings-field settings-field-wide">
                            <span>Company address</span>
                            <textarea v-model="form.company_address" rows="2" />
                        </label>
                    </div>
                </section>

                <section v-show="activeTab === 'invoice'" class="settings-panel">
                    <header class="settings-panel-head">
                        <div>
                            <h5>Invoice & currency</h5>
                            <p>Defaults for new invoices and money formatting.</p>
                        </div>
                    </header>
                    <div class="settings-grid">
                        <label class="settings-field">
                            <span>Default tax rate (%)</span>
                            <input v-model.number="form.default_tax_rate" type="number" step="0.01" min="0" max="100">
                        </label>
                        <label class="settings-field">
                            <span>Invoice prefix</span>
                            <input v-model="form.invoice_prefix" type="text" maxlength="10">
                        </label>
                        <label class="settings-field">
                            <span>Next invoice number</span>
                            <input v-model.number="form.next_invoice_number" type="number" min="1">
                        </label>
                        <label class="settings-field">
                            <span>Currency symbol</span>
                            <input v-model="form.currency_symbol" type="text" maxlength="5" required>
                        </label>
                        <label class="settings-field">
                            <span>Currency position</span>
                            <SearchableSelect
                                v-model="form.currency_position"
                                :options="currencyPositionOptions"
                                placeholder="Select position…"
                            />
                        </label>
                        <label class="settings-field settings-field-wide">
                            <span>Invoice footer</span>
                            <textarea v-model="form.invoice_footer" rows="2" placeholder="Thank you for your business!" />
                        </label>
                    </div>
                </section>

                <section v-show="activeTab === 'system'" class="settings-panel">
                    <header class="settings-panel-head">
                        <div>
                            <h5>System</h5>
                            <p>Locale defaults for lists and dates.</p>
                        </div>
                    </header>
                    <div class="settings-grid">
                        <label class="settings-field">
                            <span>Timezone</span>
                            <SearchableSelect
                                v-model="form.timezone"
                                :options="timezoneOptions"
                                placeholder="Search timezone…"
                            />
                        </label>
                        <label class="settings-field">
                            <span>Date format</span>
                            <SearchableSelect
                                v-model="form.date_format"
                                :options="dateFormatOptions"
                                placeholder="Select format…"
                            />
                        </label>
                        <label class="settings-field">
                            <span>Items per page</span>
                            <SearchableSelect
                                v-model="form.items_per_page"
                                :options="pageSizeOptions"
                                placeholder="Select size…"
                            />
                        </label>
                        <label class="settings-check">
                            <input v-model="form.enable_notifications" type="checkbox">
                            <span>Enable notifications</span>
                        </label>
                    </div>
                </section>

                <section v-show="activeTab === 'email'" class="settings-panel">
                    <header class="settings-panel-head">
                        <div>
                            <h5>Email</h5>
                            <p>Outbound mail for invoices and alerts.</p>
                        </div>
                    </header>
                    <div class="settings-grid">
                        <label class="settings-field">
                            <span>Provider</span>
                            <SearchableSelect
                                v-model="form.email_provider"
                                :options="emailProviderOptions"
                                placeholder="Select provider…"
                            />
                        </label>
                        <template v-if="form.email_provider === 'smtp'">
                            <label class="settings-field"><span>SMTP host</span><input v-model="form.smtp_host" placeholder="smtp.gmail.com"></label>
                            <label class="settings-field"><span>Port</span><input v-model.number="form.smtp_port" type="number"></label>
                            <label class="settings-field"><span>Username</span><input v-model="form.smtp_username" autocomplete="off"></label>
                            <label class="settings-field"><span>Password</span><input v-model="form.smtp_password" type="password" autocomplete="new-password"></label>
                            <label class="settings-field">
                                <span>Encryption</span>
                                <SearchableSelect v-model="form.smtp_encryption" :options="encryptionOptions" placeholder="Select…" />
                            </label>
                        </template>
                        <template v-else-if="form.email_provider === 'mailgun'">
                            <label class="settings-field"><span>Domain</span><input v-model="form.mailgun_domain"></label>
                            <label class="settings-field"><span>Secret</span><input v-model="form.mailgun_secret" type="password"></label>
                        </template>
                        <template v-else-if="form.email_provider === 'ses'">
                            <label class="settings-field"><span>Access key</span><input v-model="form.ses_key"></label>
                            <label class="settings-field"><span>Secret key</span><input v-model="form.ses_secret" type="password"></label>
                            <label class="settings-field">
                                <span>Region</span>
                                <SearchableSelect v-model="form.ses_region" :options="sesRegionOptions" placeholder="Select region…" />
                            </label>
                        </template>
                        <label class="settings-field"><span>From name</span><input v-model="form.mail_from_name"></label>
                        <label class="settings-field"><span>From address</span><input v-model="form.mail_from_address" type="email"></label>
                    </div>
                    <div class="settings-test-row">
                        <label class="settings-field mb-0 flex-grow-1">
                            <span>Send test to</span>
                            <input v-model="testEmail" type="email" placeholder="you@example.com">
                        </label>
                        <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="!testEmail || sendingTest" @click="sendTestEmail">
                            {{ sendingTest ? 'Sending…' : 'Send test email' }}
                        </button>
                    </div>
                </section>

                <section v-show="activeTab === 'sms'" class="settings-panel">
                    <header class="settings-panel-head">
                        <div>
                            <h5>SMS</h5>
                            <p>Optional provider for text alerts.</p>
                        </div>
                    </header>
                    <div class="settings-grid">
                        <label class="settings-field">
                            <span>Provider</span>
                            <SearchableSelect
                                v-model="form.sms_provider"
                                :options="smsProviderOptions"
                                placeholder="Select provider…"
                            />
                        </label>
                        <template v-if="form.sms_provider === 'twilio'">
                            <label class="settings-field"><span>Account SID</span><input v-model="form.twilio_sid"></label>
                            <label class="settings-field"><span>Auth token</span><input v-model="form.twilio_token" type="password"></label>
                            <label class="settings-field"><span>From number</span><input v-model="form.twilio_from" placeholder="+1234567890"></label>
                        </template>
                        <template v-else-if="form.sms_provider === 'nexmo'">
                            <label class="settings-field"><span>API key</span><input v-model="form.nexmo_key"></label>
                            <label class="settings-field"><span>API secret</span><input v-model="form.nexmo_secret" type="password"></label>
                            <label class="settings-field"><span>From number</span><input v-model="form.nexmo_from"></label>
                        </template>
                        <template v-else>
                            <label class="settings-field settings-field-wide"><span>API URL</span><input v-model="form.sms_api_url" type="url" placeholder="https://…"></label>
                            <label class="settings-field">
                                <span>HTTP method</span>
                                <SearchableSelect v-model="form.sms_http_method" :options="httpMethodOptions" placeholder="Method…" />
                            </label>
                        </template>
                    </div>

                    <div v-if="form.sms_provider === 'custom'" class="settings-subblock">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="font-size-13">Custom parameters</strong>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-soft-secondary" @click="addPresetParameters('bulksms')">BulkSMS preset</button>
                                <button type="button" class="btn btn-sm btn-soft-primary" @click="addCustomParameter">Add</button>
                            </div>
                        </div>
                        <div v-if="!form.sms_custom_params.length" class="text-muted font-size-12 mb-2">No parameters yet.</div>
                        <div v-for="(param, index) in form.sms_custom_params" :key="`p-${index}`" class="settings-grid settings-grid-tight mb-2">
                            <label class="settings-field"><span>Name</span><input v-model="param.name"></label>
                            <label class="settings-field"><span>Value</span><input v-model="param.value" :placeholder="getParameterPlaceholder(param.name)"></label>
                            <label class="settings-field">
                                <span>Type</span>
                                <select v-model="param.type">
                                    <option value="static">Static</option>
                                    <option value="phone">Phone</option>
                                    <option value="message">Message</option>
                                    <option value="placeholder">Placeholder</option>
                                </select>
                            </label>
                            <div class="d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-soft-danger" @click="removeCustomParameter(index)">Remove</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                            <strong class="font-size-13">Response mappings</strong>
                            <button type="button" class="btn btn-sm btn-soft-primary" @click="addResponseMapping">Add</button>
                        </div>
                        <div v-if="!form.sms_response_mappings.length" class="text-muted font-size-12">Optional friendly messages for provider responses.</div>
                        <div v-for="(mapping, index) in form.sms_response_mappings" :key="`m-${index}`" class="settings-grid settings-grid-tight mb-2">
                            <label class="settings-field"><span>Key</span><input v-model="mapping.key" placeholder="status"></label>
                            <label class="settings-field"><span>Value</span><input v-model="mapping.value" placeholder="success"></label>
                            <label class="settings-field"><span>Message</span><input v-model="mapping.message"></label>
                            <div class="d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-soft-danger" @click="removeResponseMapping(index)">Remove</button>
                            </div>
                        </div>
                    </div>

                    <div class="settings-test-row">
                        <label class="settings-field mb-0 flex-grow-1">
                            <span>Send test to</span>
                            <input v-model="testSms" type="tel" placeholder="+8801…">
                        </label>
                        <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="!testSms || sendingSms" @click="sendTestSms">
                            {{ sendingSms ? 'Sending…' : 'Send test SMS' }}
                        </button>
                    </div>
                </section>

                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save settings' }}
                    </button>
                </div>
            </form>
        </div>

        <div v-if="responseModal.open" class="settings-modal">
            <div class="settings-modal-backdrop" @click="closeResponseModal" />
            <div class="settings-modal-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 font-size-16">{{ responseModal.title }}</h5>
                    <button type="button" class="btn btn-sm btn-soft-secondary" @click="closeResponseModal">Close</button>
                </div>
                <div class="settings-modal-body">
                    <div class="mb-3">
                        <div class="text-muted font-size-12 mb-1">Request</div>
                        <pre><code>{{ formatJson(responseModal.request) }}</code></pre>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted font-size-12 mb-1">App response</div>
                        <pre><code>{{ formatJson(responseModal.appResponse) }}</code></pre>
                    </div>
                    <div v-if="responseModal.remoteRaw" class="mb-3">
                        <div class="text-muted font-size-12 mb-1">Remote raw</div>
                        <pre><code>{{ responseModal.remoteRaw }}</code></pre>
                    </div>
                    <div v-if="responseModal.remoteParsed">
                        <div class="text-muted font-size-12 mb-1">Remote parsed</div>
                        <pre><code>{{ formatJson(responseModal.remoteParsed) }}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SettingsNav from '@/Components/SettingsNav.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import { useToastNotifications } from '@/Composables/useToast'

const props = defineProps({
    settings: Object,
    timezones: Array,
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const { showSuccess, showError, showInfo } = useToastNotifications()

const tabs = [
    { key: 'company', label: 'Company', icon: 'bi-building' },
    { key: 'invoice', label: 'Invoice', icon: 'bi-receipt' },
    { key: 'system', label: 'System', icon: 'bi-gear' },
    { key: 'email', label: 'Email', icon: 'bi-envelope' },
    { key: 'sms', label: 'SMS', icon: 'bi-phone' },
]

const tabKeys = tabs.map((tab) => tab.key)
const hashTab = () => {
    const hash = (window.location.hash || '').replace('#', '')
    return tabKeys.includes(hash) ? hash : 'company'
}
const activeTab = ref(typeof window !== 'undefined' ? hashTab() : 'company')

const setTab = (key) => {
    activeTab.value = key
    if (typeof window !== 'undefined') {
        window.history.replaceState(null, '', `#${key}`)
    }
}

const fieldTab = {
    company_name: 'company',
    company_email: 'company',
    company_phone: 'company',
    company_address: 'company',
    logo: 'company',
    favicon: 'company',
    default_tax_rate: 'invoice',
    invoice_prefix: 'invoice',
    next_invoice_number: 'invoice',
    invoice_footer: 'invoice',
    currency_symbol: 'invoice',
    currency_position: 'invoice',
    timezone: 'system',
    date_format: 'system',
    items_per_page: 'system',
    enable_notifications: 'system',
    email_provider: 'email',
    smtp_host: 'email',
    smtp_port: 'email',
    smtp_username: 'email',
    smtp_password: 'email',
    smtp_encryption: 'email',
    mailgun_domain: 'email',
    mailgun_secret: 'email',
    ses_key: 'email',
    ses_secret: 'email',
    ses_region: 'email',
    mail_from_name: 'email',
    mail_from_address: 'email',
    sms_provider: 'sms',
    sms_api_url: 'sms',
    sms_http_method: 'sms',
    twilio_sid: 'sms',
    twilio_token: 'sms',
    twilio_from: 'sms',
    nexmo_key: 'sms',
    nexmo_secret: 'sms',
    nexmo_from: 'sms',
}

const form = useForm({
    company_name: props.settings?.company_name || '',
    company_email: props.settings?.company_email || '',
    company_phone: props.settings?.company_phone || '',
    company_address: props.settings?.company_address || '',
    default_tax_rate: props.settings?.default_tax_rate ?? 10,
    invoice_prefix: props.settings?.invoice_prefix || 'INV',
    next_invoice_number: props.settings?.next_invoice_number ?? 1000,
    invoice_footer: props.settings?.invoice_footer || '',
    currency_symbol: props.settings?.currency_symbol || '$',
    currency_position: props.settings?.currency_position || 'before',
    timezone: props.settings?.timezone || 'UTC',
    date_format: props.settings?.date_format || 'Y-m-d',
    items_per_page: props.settings?.items_per_page ?? 15,
    enable_notifications: !!props.settings?.enable_notifications,
    email_provider: props.settings?.email_provider || 'smtp',
    smtp_host: props.settings?.smtp_host || '',
    smtp_port: props.settings?.smtp_port ?? 587,
    smtp_username: props.settings?.smtp_username || '',
    smtp_password: props.settings?.smtp_password || '',
    smtp_encryption: props.settings?.smtp_encryption || 'tls',
    mailgun_domain: props.settings?.mailgun_domain || '',
    mailgun_secret: props.settings?.mailgun_secret || '',
    ses_key: props.settings?.ses_key || '',
    ses_secret: props.settings?.ses_secret || '',
    ses_region: props.settings?.ses_region || 'us-east-1',
    mail_from_name: props.settings?.mail_from_name || '',
    mail_from_address: props.settings?.mail_from_address || '',
    sms_provider: props.settings?.sms_provider || 'custom',
    sms_api_url: props.settings?.sms_api_url || '',
    sms_http_method: props.settings?.sms_http_method || 'POST',
    sms_custom_params: props.settings?.sms_custom_params || [],
    sms_response_mappings: props.settings?.sms_response_mappings || [],
    twilio_sid: props.settings?.twilio_sid || '',
    twilio_token: props.settings?.twilio_token || '',
    twilio_from: props.settings?.twilio_from || '',
    nexmo_key: props.settings?.nexmo_key || '',
    nexmo_secret: props.settings?.nexmo_secret || '',
    nexmo_from: props.settings?.nexmo_from || '',
    logo: null,
    favicon: null,
    remove_logo: false,
    remove_favicon: false,
})

watch(
    () => form.errors,
    (errors) => {
        const first = Object.keys(errors || {})[0]
        if (!first) return
        const tab = fieldTab[first]
        if (tab && tab !== activeTab.value) setTab(tab)
    },
    { deep: true },
)

const logoObjectUrl = ref('')
const faviconObjectUrl = ref('')
const logoPreview = computed(() => {
    if (logoObjectUrl.value) return logoObjectUrl.value
    if (form.remove_logo) return ''
    return props.settings?.logo_url || ''
})
const faviconPreview = computed(() => {
    if (faviconObjectUrl.value) return faviconObjectUrl.value
    if (form.remove_favicon) return ''
    return props.settings?.favicon_url || ''
})

const hasErrors = computed(() => Object.keys(form.errors || {}).length > 0)

const timezoneOptions = computed(() => (props.timezones || []).map((tz) => ({ value: tz, label: tz })))
const currencyPositionOptions = [
    { value: 'before', label: 'Before amount ($100)' },
    { value: 'after', label: 'After amount (100$)' },
]
const dateFormatOptions = [
    { value: 'Y-m-d', label: 'YYYY-MM-DD' },
    { value: 'm/d/Y', label: 'MM/DD/YYYY' },
    { value: 'd/m/Y', label: 'DD/MM/YYYY' },
    { value: 'M d, Y', label: 'Jan 1, 2024' },
]
const pageSizeOptions = [10, 15, 25, 50, 100].map((n) => ({ value: n, label: String(n) }))
const emailProviderOptions = [
    { value: 'smtp', label: 'SMTP' },
    { value: 'mailgun', label: 'Mailgun' },
    { value: 'ses', label: 'Amazon SES' },
    { value: 'sendmail', label: 'Sendmail' },
]
const encryptionOptions = [
    { value: 'tls', label: 'TLS' },
    { value: 'ssl', label: 'SSL' },
    { value: '', label: 'None' },
]
const sesRegionOptions = [
    { value: 'us-east-1', label: 'US East (N. Virginia)' },
    { value: 'us-west-2', label: 'US West (Oregon)' },
    { value: 'eu-west-1', label: 'Europe (Ireland)' },
    { value: 'ap-southeast-1', label: 'Asia Pacific (Singapore)' },
]
const smsProviderOptions = [
    { value: 'twilio', label: 'Twilio' },
    { value: 'nexmo', label: 'Nexmo (Vonage)' },
    { value: 'custom', label: 'Custom API' },
]
const httpMethodOptions = [
    { value: 'POST', label: 'POST' },
    { value: 'GET', label: 'GET' },
    { value: 'PUT', label: 'PUT' },
]

const onLogoPick = (event) => {
    const file = event.target.files?.[0] || null
    form.logo = file
    form.remove_logo = false
    if (logoObjectUrl.value) URL.revokeObjectURL(logoObjectUrl.value)
    logoObjectUrl.value = file ? URL.createObjectURL(file) : ''
}

const onFaviconPick = (event) => {
    const file = event.target.files?.[0] || null
    form.favicon = file
    form.remove_favicon = false
    if (faviconObjectUrl.value) URL.revokeObjectURL(faviconObjectUrl.value)
    faviconObjectUrl.value = file ? URL.createObjectURL(file) : ''
}

const clearLogo = () => {
    form.logo = null
    form.remove_logo = true
    if (logoObjectUrl.value) URL.revokeObjectURL(logoObjectUrl.value)
    logoObjectUrl.value = ''
}

const clearFavicon = () => {
    form.favicon = null
    form.remove_favicon = true
    if (faviconObjectUrl.value) URL.revokeObjectURL(faviconObjectUrl.value)
    faviconObjectUrl.value = ''
}

const submitForm = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        enable_notifications: data.enable_notifications ? 1 : 0,
        remove_logo: data.remove_logo ? 1 : 0,
        remove_favicon: data.remove_favicon ? 1 : 0,
    })).post(route('settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
        onSuccess: () => {
            form.logo = null
            form.favicon = null
            form.remove_logo = false
            form.remove_favicon = false
            if (logoObjectUrl.value) URL.revokeObjectURL(logoObjectUrl.value)
            if (faviconObjectUrl.value) URL.revokeObjectURL(faviconObjectUrl.value)
            logoObjectUrl.value = ''
            faviconObjectUrl.value = ''
        },
    })
}

const testEmail = ref('')
const sendingTest = ref(false)
const testSms = ref('')
const sendingSms = ref(false)

const responseModal = ref({
    open: false,
    title: '',
    request: null,
    appResponse: null,
    remoteRaw: '',
    remoteParsed: null,
})

const openResponseModal = (payload) => {
    responseModal.value = { open: true, ...payload }
}

const closeResponseModal = () => {
    responseModal.value.open = false
}

const formatJson = (obj) => {
    try {
        return JSON.stringify(obj, null, 2)
    } catch {
        return String(obj || '')
    }
}

const showToast = (type, title, message) => {
    const text = [title, message].filter(Boolean).join(' ')
    if (type === 'success') showSuccess(text)
    else if (type === 'info') showInfo(text)
    else showError(text)
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
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email: testEmail.value, email_config: form.data() }),
        })
        const data = await response.json()
        if (data.success) {
            showToast('success', 'Email sent', `Test email sent to ${testEmail.value}.`)
        } else {
            showToast('error', 'Email failed', data.message)
        }
        openResponseModal({
            title: data.success ? 'Test email result' : 'Test email error',
            request: { email: testEmail.value, provider: form.email_provider },
            appResponse: data,
            remoteRaw: null,
            remoteParsed: null,
        })
    } catch {
        showToast('error', 'Email failed', 'Could not send test email.')
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
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ phone: testSms.value, sms_config: form.data() }),
        })
        const data = await response.json()
        const customMessage = getCustomResponseMessage(data.data?.response || '')
        if (data.success) {
            showToast('success', 'SMS sent', customMessage || `Test SMS sent to ${testSms.value}.`)
        } else {
            showToast('error', 'SMS failed', customMessage || data.message)
        }
        let remoteParsed = null
        try { remoteParsed = JSON.parse(data.data?.response || '{}') } catch { /* ignore */ }
        openResponseModal({
            title: data.success ? 'Test SMS result' : 'Test SMS error',
            request: { phone: testSms.value, provider: form.sms_provider, url: form.sms_api_url },
            appResponse: data,
            remoteRaw: data.data?.response || '',
            remoteParsed,
        })
    } catch {
        showToast('error', 'SMS failed', 'Could not send test SMS.')
    } finally {
        sendingSms.value = false
    }
}

const addCustomParameter = () => {
    form.sms_custom_params.push({ name: '', value: '', type: 'static' })
}
const removeCustomParameter = (index) => {
    form.sms_custom_params.splice(index, 1)
}
const addPresetParameters = (type) => {
    form.sms_custom_params = type === 'bulksms'
        ? [
            { name: 'api_key', value: '', type: 'static' },
            { name: 'senderid', value: '', type: 'static' },
            { name: 'number', value: '{phone}', type: 'phone' },
            { name: 'message', value: '{message}', type: 'message' },
        ]
        : [
            { name: 'key', value: '', type: 'static' },
            { name: 'from', value: '', type: 'static' },
            { name: 'to', value: '{phone}', type: 'phone' },
            { name: 'text', value: '{message}', type: 'message' },
        ]
}
const getParameterPlaceholder = (paramName = '') => {
    const key = String(paramName).toLowerCase()
    if (['number', 'to', 'phone', 'recipient', 'mobile'].includes(key)) return 'Use {phone}'
    if (['message', 'text', 'body', 'content', 'msg'].includes(key)) return 'Use {message}'
    return 'Value'
}
const addResponseMapping = () => {
    form.sms_response_mappings.push({ key: '', value: '', message: '' })
}
const removeResponseMapping = (index) => {
    form.sms_response_mappings.splice(index, 1)
}
const getCustomResponseMessage = (remoteResponse) => {
    if (!remoteResponse || !form.sms_response_mappings.length) return null
    const responseStr = typeof remoteResponse === 'string' ? remoteResponse : JSON.stringify(remoteResponse)
    for (const mapping of form.sms_response_mappings) {
        if (!mapping.value || !mapping.message) continue
        if (!mapping.key?.trim()) {
            if (responseStr === mapping.value || responseStr.includes(mapping.value)) return mapping.message
            continue
        }
        try {
            const parsed = typeof remoteResponse === 'string' ? JSON.parse(remoteResponse) : remoteResponse
            if (parsed?.[mapping.key] !== undefined && String(parsed[mapping.key]) === String(mapping.value)) {
                return mapping.message
            }
        } catch { /* ignore */ }
    }
    return null
}
</script>

<style scoped>
.settings-shell {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 1rem;
    align-items: start;
}

.settings-shell-main {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    min-width: 0;
}

.settings-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    padding: 0.3rem;
    border: 1px solid #e6e8ee;
    border-radius: 0.65rem;
    background: #f8f9fc;
}

.settings-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 0;
    background: transparent;
    padding: 0.45rem 0.8rem;
    border-radius: 0.45rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #74788d;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.settings-tab:hover {
    color: #343747;
    background: #fff;
}

.settings-tab.active {
    background: var(--pf-accent, #5156be);
    color: #fff;
}

.settings-tab i {
    font-size: 0.9rem;
}

.settings-panel {
    background: #fff;
    border: 1px solid #e6e8ee;
    border-radius: 0.65rem;
    padding: 1rem 1.1rem 1.15rem;
}

.settings-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.55rem;
    border-bottom: 1px solid #eef0f5;
}

.settings-panel-head h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #343747;
}

.settings-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: #74788d;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.settings-grid-tight {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.settings-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0;
}

.settings-field > span {
    font-size: 0.72rem;
    font-weight: 600;
    color: #495057;
}

.settings-field em {
    color: #f46a6a;
    font-style: normal;
}

.settings-field input,
.settings-field select {
    width: 100%;
    box-sizing: border-box;
    height: 2rem;
    min-height: 2rem;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid #ced4da;
    background: #fff;
    padding: 0 0.55rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
    color: #343747;
}

.settings-field textarea {
    width: 100%;
    box-sizing: border-box;
    height: auto;
    min-height: 4rem;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid #ced4da;
    background: #fff;
    padding: 0.4rem 0.55rem;
    font-size: 0.82rem;
    line-height: 1.35;
    color: #343747;
    resize: vertical;
}

.settings-field :deep(.ss-input) {
    height: 2rem;
    min-height: 2rem;
    padding: 0 1.8rem 0 0.55rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
}

.settings-field input:focus,
.settings-field textarea:focus,
.settings-field select:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.settings-field input.is-invalid {
    border-color: #f46a6a;
}

.settings-test-row .btn,
.settings-actions .btn {
    height: 2rem;
    min-height: 2rem;
    padding: 0 0.85rem;
    display: inline-flex;
    align-items: center;
    font-size: 0.82rem;
}

.brand-actions .btn {
    height: 1.85rem;
    min-height: 1.85rem;
    padding: 0 0.7rem;
    display: inline-flex;
    align-items: center;
    font-size: 0.78rem;
}

.settings-field-wide {
    grid-column: 1 / -1;
}

.settings-check {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    font-size: 0.82rem;
    color: #343747;
    align-self: end;
    padding-bottom: 0.35rem;
}

.brand-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.brand-card {
    display: flex;
    gap: 0.85rem;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #e6e8ee;
    border-radius: 0.55rem;
    background: #f8f9fc;
}

.brand-preview {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    overflow: hidden;
    background: #fff;
    border: 1px dashed #ced4da;
}

.brand-preview-logo {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 0.55rem;
}

.brand-preview-favicon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.4rem;
}

.brand-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.brand-placeholder {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    font-size: 0.68rem;
    color: #adb5bd;
}

.brand-meta {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.brand-meta strong {
    font-size: 0.86rem;
    color: #343747;
}

.brand-meta > span {
    font-size: 0.72rem;
    color: #74788d;
}

.brand-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.35rem;
}

.settings-subblock {
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid #eef0f5;
}

.settings-test-row {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.65rem;
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid #eef0f5;
}

.settings-actions {
    display: flex;
    justify-content: flex-end;
}

.settings-alert {
    padding: 0.75rem 0.9rem;
    border-radius: 0.55rem;
    border: 1px solid #f5c2c7;
    background: #f8d7da;
    color: #842029;
    font-size: 0.82rem;
}

.settings-alert ul {
    margin: 0.35rem 0 0;
    padding-left: 1.1rem;
}

.settings-modal {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: grid;
    place-items: center;
    padding: 1rem;
}

.settings-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
}

.settings-modal-card {
    position: relative;
    width: min(42rem, 100%);
    max-height: 80vh;
    overflow: auto;
    background: #fff;
    border-radius: 0.65rem;
    padding: 1rem 1.1rem;
    box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, 0.18);
}

.settings-modal-body pre {
    margin: 0;
    padding: 0.65rem;
    border-radius: 0.4rem;
    background: #f8f9fc;
    border: 1px solid #e6e8ee;
    font-size: 0.72rem;
    overflow: auto;
}

@media (max-width: 991.98px) {
    .settings-shell,
    .settings-grid,
    .brand-grid,
    .settings-grid-tight {
        grid-template-columns: 1fr;
    }
}
</style>
