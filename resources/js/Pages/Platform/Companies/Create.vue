<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import FormScreen from '@/Components/FormScreen.vue';
import Layout from '../Layout.vue';

const props = defineProps({
    prefix: String,
    baseDomain: String,
    hostEnabled: Boolean,
});

const form = useForm({
    name: '',
    slug: '',
    database_name: '',
    email: '',
    phone: '',
    address: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

const tabs = [
    { key: 'identity', label: 'Identity', icon: 'bi-building' },
    { key: 'database', label: 'Database', icon: 'bi-database' },
    { key: 'admin', label: 'Admin', icon: 'bi-person-gear' },
];

const activeTab = ref('identity');
const check = ref('');
const checkOk = ref(null);

const suggestedDb = computed(() => `${props.prefix || 'epharma_'}${(form.slug || 'name').replaceAll('-', '_')}`);
const hostPreview = computed(() => `${form.slug || 'name'}.${props.baseDomain}`);

const fieldTab = {
    name: 'identity',
    slug: 'identity',
    email: 'identity',
    phone: 'identity',
    address: 'identity',
    database_name: 'database',
    admin_name: 'admin',
    admin_email: 'admin',
    admin_password: 'admin',
};

watch(
    () => form.errors,
    (errors) => {
        const first = Object.keys(errors || {})[0];
        if (first && fieldTab[first]) activeTab.value = fieldTab[first];
    },
    { deep: true },
);

async function validateDatabase() {
    check.value = 'Checking…';
    checkOk.value = null;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch('/platform/companies/validate-database', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ slug: form.slug, database_name: form.database_name }),
    });
    const data = await response.json();
    checkOk.value = !!data.ok;
    check.value = data.message || 'Could not check that database.';
    if (data.ok && data.database_name && !form.database_name) {
        form.database_name = data.database_name;
    }
}

const goNext = () => {
    const idx = tabs.findIndex((tab) => tab.key === activeTab.value);
    if (idx < tabs.length - 1) activeTab.value = tabs[idx + 1].key;
};

const goPrev = () => {
    const idx = tabs.findIndex((tab) => tab.key === activeTab.value);
    if (idx > 0) activeTab.value = tabs[idx - 1].key;
};
</script>

<template>
    <Head title="New pharmacy" />
    <Layout>
        <FormScreen title="New pharmacy" :close-href="route('platform.companies.index')">
            <form id="platform-company-create" class="create-form" novalidate @submit.prevent="form.post('/platform/companies')">
                <p class="create-lead">
                    Host <strong>{{ hostPreview }}</strong>.
                    Creates the tenant database, pharmacy admin, menus,
                    {{ hostEnabled ? 'and the server hostname/certificate.' : 'and queues a hostname when the host script is installed.' }}
                </p>

                <nav class="create-tabs" aria-label="New pharmacy sections">
                    <button
                        v-for="(tab, idx) in tabs"
                        :key="tab.key"
                        type="button"
                        class="create-tab"
                        :class="{ active: activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        <span class="create-tab-num">{{ idx + 1 }}</span>
                        <i :class="['bi', tab.icon]" />
                        <span>{{ tab.label }}</span>
                    </button>
                </nav>

                <section v-show="activeTab === 'identity'" class="create-panel">
                    <header class="create-panel-head">
                        <h2>Pharmacy identity</h2>
                        <p>Public name, slug, and contact details.</p>
                    </header>
                    <div class="create-grid">
                        <label class="create-field">
                            <span>Pharmacy name <em>*</em></span>
                            <input v-model="form.name" type="text" required :class="{ 'is-invalid': form.errors.name }">
                            <small v-if="form.errors.name">{{ form.errors.name }}</small>
                        </label>
                        <label class="create-field">
                            <span>Slug <em>*</em></span>
                            <input
                                v-model="form.slug"
                                type="text"
                                required
                                placeholder="city-care"
                                pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                :class="{ 'is-invalid': form.errors.slug }"
                            >
                            <small v-if="form.errors.slug">{{ form.errors.slug }}</small>
                            <small v-else>Used for {{ hostPreview }}</small>
                        </label>
                        <label class="create-field">
                            <span>Contact email <em>*</em></span>
                            <input v-model="form.email" type="email" required autocomplete="off" :class="{ 'is-invalid': form.errors.email }">
                            <small v-if="form.errors.email">{{ form.errors.email }}</small>
                        </label>
                        <label class="create-field">
                            <span>Phone</span>
                            <input v-model="form.phone" type="tel">
                        </label>
                        <label class="create-field create-field-wide">
                            <span>Address</span>
                            <textarea v-model="form.address" rows="2" />
                        </label>
                    </div>
                </section>

                <section v-show="activeTab === 'database'" class="create-panel">
                    <header class="create-panel-head">
                        <h2>Tenant database</h2>
                        <p>Leave blank to use <code>{{ suggestedDb }}</code>.</p>
                    </header>
                    <div class="create-inline">
                        <label class="create-field create-field-grow">
                            <span>Database name</span>
                            <input
                                v-model="form.database_name"
                                type="text"
                                :placeholder="suggestedDb"
                                :class="{ 'is-invalid': form.errors.database_name }"
                            >
                            <small v-if="form.errors.database_name">{{ form.errors.database_name }}</small>
                        </label>
                        <button type="button" class="btn btn-outline-secondary btn-sm create-check-btn" @click="validateDatabase">
                            Check availability
                        </button>
                    </div>
                    <p v-if="check" class="create-check" :class="checkOk ? 'is-ok' : 'is-bad'">{{ check }}</p>
                </section>

                <section v-show="activeTab === 'admin'" class="create-panel">
                    <header class="create-panel-head">
                        <h2>Pharmacy admin</h2>
                        <p>Created inside the new pharmacy database — not the platform admin.</p>
                    </header>
                    <div class="create-grid">
                        <label class="create-field">
                            <span>Admin name <em>*</em></span>
                            <input v-model="form.admin_name" type="text" required autocomplete="off" :class="{ 'is-invalid': form.errors.admin_name }">
                            <small v-if="form.errors.admin_name">{{ form.errors.admin_name }}</small>
                        </label>
                        <label class="create-field">
                            <span>Admin email <em>*</em></span>
                            <input v-model="form.admin_email" type="email" required autocomplete="off" :class="{ 'is-invalid': form.errors.admin_email }">
                            <small v-if="form.errors.admin_email">{{ form.errors.admin_email }}</small>
                        </label>
                        <label class="create-field create-field-wide">
                            <span>Admin password <em>*</em></span>
                            <input
                                v-model="form.admin_password"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                :class="{ 'is-invalid': form.errors.admin_password }"
                            >
                            <small v-if="form.errors.admin_password">{{ form.errors.admin_password }}</small>
                            <small v-else>At least 8 characters.</small>
                        </label>
                    </div>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button v-if="activeTab !== 'identity'" type="button" class="btn btn-outline-secondary" @click="goPrev">Back</button>
                <button v-if="activeTab !== 'admin'" type="button" class="btn btn-primary" @click="goNext">Continue</button>
                <button
                    v-else
                    form="platform-company-create"
                    class="btn btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Starting…' : 'Start provisioning' }}
                </button>
            </template>
        </FormScreen>
    </Layout>
</template>

<style scoped>
.create-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    max-width: 44rem;
    margin: 0 auto;
}

.create-lead {
    margin: 0;
    font-size: 0.82rem;
    color: #74788d;
    line-height: 1.45;
}

.create-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    padding: 0.3rem;
    border: 1px solid var(--pf-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    background: var(--pf-bg, #f8f9fc);
}

.create-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 0;
    background: transparent;
    padding: 0.45rem 0.75rem;
    border-radius: var(--pf-radius, 10px);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--pf-muted, #74788d) !important;
    cursor: pointer;
}

.create-tab:hover {
    background: var(--pf-surface, #fff);
    color: var(--pf-text, #343747) !important;
}

.create-tab.active {
    background: rgba(var(--bs-primary-rgb, 23, 52, 43), 0.12);
    color: var(--pf-primary, var(--bs-primary, #17342b)) !important;
    box-shadow: inset 0 0 0 1px rgba(var(--bs-primary-rgb, 23, 52, 43), 0.35);
}

.create-tab-num {
    display: inline-grid;
    place-items: center;
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 999px;
    font-size: 0.68rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid currentColor;
}

.create-tab:not(.active) .create-tab-num {
    border-color: #ced4da;
    background: #fff;
}

.create-panel {
    border: 1px solid var(--pf-border, #e6e8ee);
    border-radius: var(--pf-radius, 10px);
    background: var(--pf-surface, #fff);
    padding: 1rem 1.1rem 1.15rem;
}

.create-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.55rem;
    border-bottom: 1px solid var(--pf-border, #eef0f5);
}

.create-panel-head h2 {
    margin: 0 !important;
    font-size: 0.86rem !important;
    font-weight: 600 !important;
    color: var(--pf-text, #343747) !important;
}

.create-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: #74788d;
}

.create-panel-head code {
    font-size: 0.74rem;
}

.create-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.create-field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin: 0;
}

.create-field-wide {
    grid-column: 1 / -1;
}

.create-field > span {
    font-size: 0.72rem;
    font-weight: 600;
    color: #495057;
}

.create-field em {
    color: #f46a6a;
    font-style: normal;
}

.create-field input,
.create-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ced4da;
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    background: #fff;
    color: #343747;
    font-size: 0.82rem;
}

.create-field input {
    height: 2rem;
    min-height: 2rem;
    padding: 0 0.55rem;
    line-height: calc(2rem - 2px);
}

.create-field textarea {
    min-height: 4rem;
    padding: 0.4rem 0.55rem;
    line-height: 1.35;
    resize: vertical;
}

.create-field input:focus,
.create-field textarea:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.create-field input.is-invalid,
.create-field textarea.is-invalid {
    border-color: #f46a6a;
}

.create-field small {
    font-size: 0.7rem;
    color: #74788d;
}

.create-field input.is-invalid + small,
.create-field .is-invalid ~ small {
    color: #f46a6a;
}

.create-inline {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.65rem;
}

.create-field-grow {
    flex: 1 1 14rem;
}

.create-check-btn {
    height: 2rem;
    min-height: 2rem;
    display: inline-flex;
    align-items: center;
}

.create-check {
    margin: 0.65rem 0 0;
    font-size: 0.78rem;
}

.create-check.is-ok { color: #1e8f68; }
.create-check.is-bad { color: #f46a6a; }

@media (max-width: 767.98px) {
    .create-grid {
        grid-template-columns: 1fr;
    }
}
</style>
