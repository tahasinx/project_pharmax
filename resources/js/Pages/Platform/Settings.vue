<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({ settings: Object, tenancy: Object });
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
});
const test = useForm({ email_test_to: '' });
const shapes = [
    ['rounded', 'Rounded', 'Soft corners'],
    ['default', 'Default', 'Small corners'],
    ['flat', 'Flat', 'Square corners'],
];
const hosts = computed(() => (props.tenancy.central_hosts || []).join(', ') || '—');
</script>

<template>
    <Head title="Platform settings" />
    <Layout>
        <form class="mx-auto max-w-3xl space-y-4" @submit.prevent="form.put('/platform/settings')">
            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>Identity</h1>
                    <p class="mt-1 text-sm text-[#71717a]">Name, support contacts, and the currency used on platform invoices.</p>
                </header>
                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Platform name</span>
                        <input v-model="form.name" class="w-full" required>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Tagline</span>
                        <input v-model="form.tagline" class="w-full">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Support email</span>
                        <input v-model="form.support_email" type="email" class="w-full">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Support phone</span>
                        <input v-model="form.support_phone" class="w-full">
                    </label>
                    <label class="block text-sm sm:col-span-2">
                        <span class="mb-1 block text-[#3f3f46]">Address</span>
                        <textarea v-model="form.address" class="w-full" rows="2" />
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Currency</span>
                        <input v-model="form.default_currency" class="w-full" required maxlength="8">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Invoice footer</span>
                        <textarea v-model="form.invoice_footer" class="w-full" rows="2" />
                    </label>
                </div>
            </section>

            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>Theme</h1>
                    <p class="mt-1 text-sm text-[#71717a]">Accent, corner shape, and type for every screen, control, and label. Saved values apply after you refresh.</p>
                </header>
                <div class="space-y-4 p-5">
                    <label class="flex items-center gap-3 text-sm">
                        <span class="text-[#3f3f46]">Accent</span>
                        <input v-model="form.theme_primary" type="color" class="h-9 w-14 cursor-pointer p-1">
                        <span class="font-mono text-[#71717a]">{{ form.theme_primary }}</span>
                    </label>
                    <div>
                        <p class="mb-2 text-sm text-[#3f3f46]">Corner shape</p>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <label v-for="[value, label, hint] in shapes" :key="value" class="shape-choice" :class="{ 'is-on': form.theme_shape === value }">
                                <input v-model="form.theme_shape" class="sr-only" type="radio" :value="value">
                                <span class="shape-swatch" :class="`shape-sample--${value}`" aria-hidden="true" />
                                <span>
                                    <span class="block text-sm font-medium">{{ label }}</span>
                                    <span class="block text-xs text-[#71717a]">{{ hint }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="block text-sm sm:col-span-1">
                            <span class="mb-1 block text-[#3f3f46]">Font name</span>
                            <input v-model="form.theme_font_family" class="w-full" required maxlength="60">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block text-[#3f3f46]">Size (px)</span>
                            <input v-model.number="form.theme_font_size" type="number" min="12" max="22" class="w-full" required>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block text-[#3f3f46]">Weight</span>
                            <select v-model.number="form.theme_font_weight" class="w-full">
                                <option v-for="weight in [300, 400, 500, 600, 700, 800, 900]" :key="weight" :value="weight">{{ weight }}</option>
                            </select>
                        </label>
                        <label class="block text-sm sm:col-span-3">
                            <span class="mb-1 block text-[#3f3f46]">Font link</span>
                            <textarea v-model="form.theme_font_href" class="w-full" rows="2" placeholder="https://fonts.googleapis.com/css2?family=Inter" />
                            <span class="mt-1 block text-xs text-[#71717a]">A Google Fonts or jsDelivr stylesheet URL. The font name must match.</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-[#e4e4e7] bg-white">
                <header class="border-b border-[#f4f4f5] px-5 py-4">
                    <h1>System emailer</h1>
                    <p class="mt-1 text-sm text-[#71717a]">SMTP used when the platform sends mail, including the test below.</p>
                </header>
                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm sm:col-span-2">
                        <input v-model="form.email_enabled" type="checkbox">
                        Enable outbound email
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">SMTP host</span>
                        <input v-model="form.email_host" class="w-full" placeholder="smtp.gmail.com">
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block text-sm">
                            <span class="mb-1 block text-[#3f3f46]">Port</span>
                            <input v-model.number="form.email_port" type="number" class="w-full">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block text-[#3f3f46]">Encryption</span>
                            <select v-model="form.email_encryption" class="w-full">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="">None</option>
                            </select>
                        </label>
                    </div>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Username</span>
                        <input v-model="form.email_username" class="w-full" autocomplete="off">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">Password</span>
                        <input v-model="form.email_password" type="password" class="w-full" autocomplete="new-password" :placeholder="settings.email_password_set ? 'Saved. Leave blank to keep it.' : 'SMTP password'">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">From address</span>
                        <input v-model="form.email_from_address" type="email" class="w-full">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block text-[#3f3f46]">From name</span>
                        <input v-model="form.email_from_name" class="w-full">
                    </label>
                </div>
            </section>

            <div class="flex justify-end">
                <button class="pf-accent rounded-lg px-4 py-2 text-sm" :disabled="form.processing">Save settings</button>
            </div>
        </form>

        <form class="mx-auto mt-4 max-w-3xl rounded-xl border border-[#e4e4e7] bg-white p-5" @submit.prevent="test.post('/platform/settings/email-test')">
            <p class="text-sm font-medium">Test delivery</p>
            <p class="mt-1 text-sm text-[#71717a]">Uses the saved SMTP settings, so save first.</p>
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <label class="block min-w-[16rem] flex-1 text-sm">
                    <span class="mb-1 block text-[#3f3f46]">Send test to</span>
                    <input v-model="test.email_test_to" type="email" class="w-full" required>
                </label>
                <button class="rounded-lg border border-[#e4e4e7] px-3 py-2 text-sm" :disabled="test.processing">Send test</button>
            </div>
        </form>

        <dl class="mx-auto mt-4 max-w-3xl rounded-xl border border-[#e4e4e7] bg-white p-5 text-sm">
            <div class="flex justify-between gap-4 border-b border-[#f4f4f5] py-2"><dt class="text-[#71717a]">Tenancy</dt><dd>{{ tenancy.enabled ? 'On' : 'Off' }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-[#f4f4f5] py-2"><dt class="text-[#71717a]">Domain</dt><dd>{{ tenancy.base_domain }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-[#f4f4f5] py-2"><dt class="text-[#71717a]">Database prefix</dt><dd>{{ tenancy.prefix }}</dd></div>
            <div class="flex justify-between gap-4 border-b border-[#f4f4f5] py-2"><dt class="text-[#71717a]">Central database</dt><dd>{{ tenancy.central }}</dd></div>
            <div class="flex justify-between gap-4 py-2"><dt class="text-[#71717a]">Admin hosts</dt><dd>{{ hosts }}</dd></div>
        </dl>
    </Layout>
</template>

<style scoped>
.shape-choice {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #e4e4e7;
    border-radius: 10px;
    padding: 0.65rem 0.75rem;
    cursor: pointer;
}
.shape-choice.is-on {
    border-color: var(--pf-accent, #17342b);
    box-shadow: inset 0 0 0 1px var(--pf-accent, #17342b);
}
.shape-swatch {
    width: 2.75rem;
    height: 1.75rem;
    flex-shrink: 0;
    border: 2px solid #18181b;
    background: #fff;
}
</style>
