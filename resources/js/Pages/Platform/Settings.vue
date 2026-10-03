<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { applyBrandTheme, shapeRadius } from '@/theme/applyBrandTheme';
import Layout from './Layout.vue';

const props = defineProps({ settings: Object, tenancy: Object });

const tabs = [
    { key: 'identity', label: 'Identity', icon: 'bi-building' },
    { key: 'theme', label: 'Theme', icon: 'bi-palette' },
    { key: 'email', label: 'Email', icon: 'bi-envelope' },
    { key: 'system', label: 'System', icon: 'bi-hdd-network' },
];
const activeTab = ref('identity');

const form = useForm({
    name: props.settings.name,
    tagline: props.settings.tagline,
    support_email: props.settings.support_email,
    support_phone: props.settings.support_phone,
    address: props.settings.address,
    default_currency: props.settings.default_currency,
    invoice_footer: props.settings.invoice_footer,
    theme_primary: props.settings.theme_primary,
    theme_shape: props.settings.theme_shape,
    theme_font_family: props.settings.theme_font_family,
    theme_font_href: props.settings.theme_font_href,
    theme_font_size: props.settings.theme_font_size,
    theme_font_weight: props.settings.theme_font_weight,
    email_enabled: props.settings.email_enabled,
    email_host: props.settings.email_host,
    email_port: props.settings.email_port,
    email_encryption: props.settings.email_encryption,
    email_username: props.settings.email_username,
    email_password: '',
    email_from_address: props.settings.email_from_address,
    email_from_name: props.settings.email_from_name,
    logo: null,
    favicon: null,
});
const test = useForm({ email_test_to: '' });
const shapes = [
    ['rounded', 'Rounded', 'Soft corners'],
    ['default', 'Default', 'Small corners'],
    ['flat', 'Flat', 'Square corners'],
];
const weightOptions = [300, 400, 500, 600, 700, 800, 900].map((weight) => ({
    value: weight,
    label: String(weight),
}));
const encryptionOptions = [
    { value: 'tls', label: 'TLS' },
    { value: 'ssl', label: 'SSL' },
    { value: '', label: 'None' },
];
const hosts = computed(() => (props.tenancy.central_hosts || []).join(', ') || '—');

const pushThemePreview = () => {
    applyBrandTheme({
        primary: form.theme_primary,
        shape: form.theme_shape,
        radius: shapeRadius(form.theme_shape),
        font_family: form.theme_font_family,
        font_href: form.theme_font_href,
        font_size: form.theme_font_size,
        font_weight: form.theme_font_weight,
        app_name: form.name,
        favicon: props.settings.favicon_url || '',
    });
};

/** Live-preview theme (incl. stylesheet) while editing — mirrors ThemeHost. */
watch(
    () => [
        form.name,
        form.theme_shape,
        form.theme_primary,
        form.theme_font_family,
        form.theme_font_href,
        form.theme_font_size,
        form.theme_font_weight,
    ],
    pushThemePreview,
    { immediate: true },
);

watch(
    () => props.settings,
    (next) => {
        if (!next) return;
        form.defaults({
            name: next.name,
            tagline: next.tagline,
            support_email: next.support_email,
            support_phone: next.support_phone,
            address: next.address,
            default_currency: next.default_currency,
            invoice_footer: next.invoice_footer,
            theme_primary: next.theme_primary,
            theme_shape: next.theme_shape,
            theme_font_family: next.theme_font_family,
            theme_font_href: next.theme_font_href,
            theme_font_size: next.theme_font_size,
            theme_font_weight: next.theme_font_weight,
            email_enabled: next.email_enabled,
            email_host: next.email_host,
            email_port: next.email_port,
            email_encryption: next.email_encryption,
            email_username: next.email_username,
            email_password: '',
            email_from_address: next.email_from_address,
            email_from_name: next.email_from_name,
            logo: null,
            favicon: null,
        });
        form.reset();
        pushThemePreview();
    },
    { deep: true },
);

const save = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        email_enabled: data.email_enabled ? 1 : 0,
    })).post('/platform/settings', {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
        onSuccess: () => {
            form.logo = null;
            form.favicon = null;
            pushThemePreview();
        },
    });
};
</script>

<template>
    <Head title="Platform settings" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Settings</h4>
                <p class="text-muted mb-0 font-size-13">Platform identity, theme, and outbound email.</p>
            </div>
        </template>

        <nav class="pf-ops-nav" aria-label="Settings sections">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="pf-ops-tab"
                :class="{ active: activeTab === tab.key }"
                @click="activeTab = tab.key"
            >
                <i :class="['bi', tab.icon]" />
                <span>{{ tab.label }}</span>
            </button>
        </nav>

        <form class="mx-auto" style="max-width: 48rem;" novalidate @submit.prevent="save">
            <section v-show="activeTab === 'identity'" class="pf-card mb-3">
                <div class="pf-card-head">
                    <div>
                        <h2>Identity</h2>
                        <p>Name, branding, support contacts, and invoice currency.</p>
                    </div>
                </div>
                <div class="pf-card-body">
                    <div class="pf-control-grid">
                        <label class="pf-field"><span>Platform name</span><input v-model="form.name" required></label>
                        <label class="pf-field"><span>Tagline</span><input v-model="form.tagline"></label>
                        <label class="pf-field"><span>Support email</span><input v-model="form.support_email" type="email"></label>
                        <label class="pf-field"><span>Support phone</span><input v-model="form.support_phone"></label>
                        <label class="pf-field" style="grid-column: 1 / -1;"><span>Address</span><textarea v-model="form.address" rows="2" /></label>
                        <label class="pf-field">
                            <span>Logo</span>
                            <img v-if="settings.logo_url" :src="settings.logo_url" alt="" class="mb-2 d-block" style="height: 2.5rem; width: 2.5rem; object-fit: contain;">
                            <input type="file" accept="image/*" @input="form.logo = $event.target.files[0]">
                        </label>
                        <label class="pf-field">
                            <span>Favicon</span>
                            <img v-if="settings.favicon_url" :src="settings.favicon_url" alt="" class="mb-2 d-block" style="height: 2rem; width: 2rem; object-fit: contain;">
                            <input type="file" accept="image/*" @input="form.favicon = $event.target.files[0]">
                        </label>
                        <label class="pf-field"><span>Currency</span><input v-model="form.default_currency" required maxlength="8"></label>
                        <label class="pf-field"><span>Invoice footer</span><textarea v-model="form.invoice_footer" rows="2" /></label>
                    </div>
                </div>
            </section>

            <section v-show="activeTab === 'theme'" class="pf-card mb-3">
                <div class="pf-card-head">
                    <div>
                        <h2>Theme</h2>
                        <p>Accent, corner shape, and type for every screen.</p>
                    </div>
                </div>
                <div class="pf-card-body d-flex flex-column gap-3">
                    <label class="pf-field mb-0" style="max-width: 14rem;">
                        <span>Accent</span>
                        <div class="d-flex align-items-center gap-2">
                            <input v-model="form.theme_primary" type="color">
                            <span class="font-monospace small text-muted">{{ form.theme_primary }}</span>
                        </div>
                    </label>
                    <div>
                        <p class="small text-muted mb-2">Corner shape</p>
                        <div class="shape-grid">
                            <label
                                v-for="[value, label, hint] in shapes"
                                :key="value"
                                class="shape-choice"
                                :class="{ 'is-on': form.theme_shape === value }"
                            >
                                <input v-model="form.theme_shape" class="sr-only" type="radio" :value="value">
                                <span class="shape-swatch" :class="`shape-sample--${value}`" aria-hidden="true" />
                                <span>
                                    <span class="d-block small fw-semibold">{{ label }}</span>
                                    <span class="d-block text-muted" style="font-size: 0.72rem;">{{ hint }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="pf-control-grid">
                        <label class="pf-field"><span>Font name</span><input v-model="form.theme_font_family" required maxlength="60"></label>
                        <label class="pf-field"><span>Size (px)</span><input v-model.number="form.theme_font_size" type="number" min="12" max="22" required></label>
                        <label class="pf-field">
                            <span>Weight</span>
                            <SearchableSelect
                                v-model="form.theme_font_weight"
                                :options="weightOptions"
                                placeholder="Select weight…"
                            />
                        </label>
                        <label class="pf-field" style="grid-column: 1 / -1;">
                            <span>Font stylesheet URL</span>
                            <textarea v-model="form.theme_font_href" rows="2" placeholder="Leave blank for bundled IBM Plex Sans" />
                        </label>
                    </div>
                </div>
            </section>

            <section v-show="activeTab === 'email'" class="pf-card mb-3">
                <div class="pf-card-head">
                    <div>
                        <h2>System emailer</h2>
                        <p>SMTP used when the platform sends mail.</p>
                    </div>
                </div>
                <div class="pf-card-body">
                    <div class="pf-control-grid">
                        <label class="pf-field" style="grid-column: 1 / -1;">
                            <span class="d-inline-flex align-items-center gap-2">
                                <input v-model="form.email_enabled" type="checkbox">
                                Enable outbound email
                            </span>
                        </label>
                        <label class="pf-field"><span>SMTP host</span><input v-model="form.email_host" placeholder="smtp.gmail.com"></label>
                        <label class="pf-field"><span>Port</span><input v-model.number="form.email_port" type="number"></label>
                        <label class="pf-field">
                            <span>Encryption</span>
                            <SearchableSelect
                                v-model="form.email_encryption"
                                :options="encryptionOptions"
                                placeholder="Select encryption…"
                            />
                        </label>
                        <label class="pf-field"><span>Username</span><input v-model="form.email_username" autocomplete="off"></label>
                        <label class="pf-field">
                            <span>Password</span>
                            <input
                                v-model="form.email_password"
                                type="password"
                                autocomplete="new-password"
                                :placeholder="settings.email_password_set ? 'Saved. Leave blank to keep it.' : 'SMTP password'"
                            >
                        </label>
                        <label class="pf-field"><span>From address</span><input v-model="form.email_from_address" type="email"></label>
                        <label class="pf-field"><span>From name</span><input v-model="form.email_from_name"></label>
                    </div>
                    <div class="pf-inline-actions mt-3 pt-3 border-top">
                        <label class="pf-field">
                            <span>Send test to</span>
                            <input v-model="test.email_test_to" type="email" placeholder="you@example.com">
                        </label>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            :disabled="test.processing || !test.email_test_to"
                            @click="test.post('/platform/settings/email-test')"
                        >
                            {{ test.processing ? 'Sending…' : 'Send test' }}
                        </button>
                    </div>
                    <p class="small text-muted mt-2 mb-0">Uses saved SMTP settings — save this tab first.</p>
                </div>
            </section>

            <section v-show="activeTab === 'system'" class="pf-card mb-3">
                <div class="pf-card-head">
                    <div>
                        <h2>Tenancy</h2>
                        <p>Read-only host and database configuration.</p>
                    </div>
                </div>
                <div class="pf-card-body small">
                    <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Tenancy</span><span>{{ tenancy.enabled ? 'On' : 'Off' }}</span></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Domain</span><span>{{ tenancy.base_domain }}</span></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Database prefix</span><span>{{ tenancy.prefix }}</span></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Central database</span><span>{{ tenancy.central }}</span></div>
                    <div class="d-flex justify-content-between py-2"><span class="text-muted">Admin hosts</span><span>{{ hosts }}</span></div>
                </div>
            </section>

            <div v-if="activeTab !== 'system'" class="d-flex justify-content-end mb-3">
                <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save settings' }}
                </button>
            </div>
        </form>
    </Layout>
</template>

<style scoped>
.shape-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.55rem;
}

.shape-choice {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    border: 1px solid var(--pf-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    padding: 0.65rem 0.75rem;
    cursor: pointer;
    background: var(--pf-bg, #f8f9fc);
}

.shape-choice.is-on {
    border-color: var(--pf-primary, var(--pf-accent, #17342b));
    background: color-mix(in srgb, var(--pf-primary, var(--pf-accent, #17342b)) 10%, #fff);
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--pf-primary, var(--pf-accent, #17342b)) 35%, transparent);
}

.shape-swatch {
    width: 2.5rem;
    height: 1.55rem;
    flex-shrink: 0;
    border: 2px solid #343747;
    background: #fff;
}

.shape-sample--rounded { border-radius: 10px; }
.shape-sample--default { border-radius: 4px; }
.shape-sample--flat { border-radius: 0; }

@media (max-width: 767.98px) {
    .shape-grid {
        grid-template-columns: 1fr;
    }
}
</style>
