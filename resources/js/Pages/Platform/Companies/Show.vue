<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    subscription: Object,
    schema: Object,
    files: Array,
    users: { type: Array, default: () => [] },
});

const tabs = [
    { key: 'overview', label: 'Overview', icon: 'bi-info-circle' },
    { key: 'access', label: 'Access', icon: 'bi-person-badge' },
    { key: 'ops', label: 'Operations', icon: 'bi-tools' },
    { key: 'danger', label: 'Danger', icon: 'bi-exclamation-triangle' },
];

const hashTab = () => {
    const hash = (typeof window !== 'undefined' ? window.location.hash : '').replace('#', '');
    return tabs.some((tab) => tab.key === hash) ? hash : 'overview';
};

const activeTab = ref(hashTab());
const setTab = (key) => {
    activeTab.value = key;
    if (typeof window !== 'undefined') {
        window.history.replaceState(null, '', `#${key}`);
    }
};

const lock = useForm({ password: '' });
const login = useForm({ password: '', user_id: '' });
const remove = useForm({ password: '', confirm_text: '' });
const upgrade = useForm({ password: '', company_id: props.company.id });

const userOptions = computed(() => [
    { value: '', label: 'Pharmacy admin (default)' },
    ...(props.users || []).map((user) => ({
        value: String(user.id),
        label: `${user.name} · ${user.email}${user.roles?.length ? ` (${user.roles.join(', ')})` : ''}`,
    })),
]);

const openAs = (userId = '') => {
    login.user_id = userId ? String(userId) : '';
    login.post(`/platform/companies/${props.company.id}/login-as`);
};

watch(
    () => [login.errors, lock.errors, upgrade.errors, remove.errors],
    () => {
        if (Object.keys(login.errors || {}).length) setTab('access');
        else if (Object.keys(lock.errors || {}).length || Object.keys(upgrade.errors || {}).length) setTab('ops');
        else if (Object.keys(remove.errors || {}).length) setTab('danger');
    },
    { deep: true },
);
</script>

<template>
    <Head :title="company.name" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="mb-0 font-size-18">{{ company.name }}</h4>
                    <StatusBadge
                        :status="company.status"
                        :provision="company.provision_status"
                    />
                </div>
                <p class="text-muted mb-0 font-size-13">Tenant controls for access, schema, backups, and lockdown.</p>
            </div>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link href="/platform/companies" class="btn btn-outline-secondary btn-sm">All pharmacies</Link>
                <Link :href="`/platform/companies/${company.id}/provision`" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cpu me-1" />Provision
                </Link>
                <Link :href="`/platform/companies/${company.id}/edit`" class="btn btn-primary btn-sm">Edit</Link>
            </div>
        </template>

        <nav class="pf-ops-nav mb-3" aria-label="Pharmacy sections">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="pf-ops-tab"
                :class="{ active: activeTab === tab.key }"
                @click="setTab(tab.key)"
            >
                <i :class="['bi', tab.icon]" />
                <span>{{ tab.label }}</span>
            </button>
        </nav>

        <div v-show="activeTab === 'overview'" class="row g-3">
            <div class="col-lg-8">
                <section class="pf-card h-100">
                    <div class="pf-card-head">
                        <div>
                            <h2>Identity</h2>
                            <p>Registry details for this pharmacy host.</p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <dl class="pf-kv">
                            <div><dt>Slug</dt><dd class="font-monospace">{{ company.slug }}</dd></div>
                            <div><dt>Database</dt><dd class="font-monospace">{{ company.database_name }}</dd></div>
                            <div><dt>Email</dt><dd>{{ company.email || '—' }}</dd></div>
                            <div><dt>Admin login</dt><dd>{{ company.admin_email || '—' }}</dd></div>
                            <div><dt>Phone</dt><dd>{{ company.phone || '—' }}</dd></div>
                            <div><dt>Hostname</dt><dd>{{ company.vhost_status || '—' }} · SSL {{ company.ssl_status || '—' }}</dd></div>
                            <div class="pf-kv-wide"><dt>Address</dt><dd>{{ company.address || '—' }}</dd></div>
                            <div class="pf-kv-wide">
                                <dt>URL</dt>
                                <dd><a :href="host" target="_blank" rel="noopener">{{ host }}</a></dd>
                            </div>
                        </dl>
                        <p v-if="company.provision_error" class="text-danger small mt-3 mb-0">{{ company.provision_error }}</p>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="pf-card h-100">
                    <div class="pf-card-head">
                        <div>
                            <h2>Subscription</h2>
                            <p>Commercial plan link.</p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <div class="pf-stat-stack">
                            <div>
                                <span class="text-muted small d-block">Plan</span>
                                <strong>{{ subscription?.plan?.name || 'None' }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small d-block">Status</span>
                                <strong>{{ subscription?.status || '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small d-block">Ends</span>
                                <strong>{{ subscription?.ends_on || '—' }}</strong>
                            </div>
                        </div>
                        <Link :href="`/platform/subscriptions?company=${company.id}`" class="btn btn-sm btn-outline-primary mt-3">
                            Manage subscription
                        </Link>
                    </div>
                </section>
            </div>
        </div>

        <div v-show="activeTab === 'access'" class="d-flex flex-column gap-3">
            <section class="pf-card">
                <div class="pf-card-head">
                    <div>
                        <h2>Open as pharmacy user</h2>
                        <p>Sign into the tenant host as a selected user.</p>
                    </div>
                </div>
                <div class="pf-card-body">
                    <form class="pf-inline-form" @submit.prevent="openAs(login.user_id)">
                        <label class="pf-field pf-field-grow">
                            <span>User</span>
                            <SearchableSelect
                                v-model="login.user_id"
                                :options="userOptions"
                                placeholder="Pharmacy admin (default)"
                            />
                        </label>
                        <label class="pf-field pf-field-grow">
                            <span>Your platform password</span>
                            <input v-model="login.password" type="password" required autocomplete="current-password">
                        </label>
                        <button class="btn btn-primary btn-sm pf-inline-btn" :disabled="company.status !== 'active' || login.processing">
                            <i class="bi bi-box-arrow-up-right me-1" />
                            Sign in there
                        </button>
                    </form>
                    <p v-if="login.errors.password || login.errors.login" class="text-danger small mt-2 mb-0">
                        {{ login.errors.password || login.errors.login }}
                    </p>
                </div>
            </section>

            <section class="pf-card">
                <div class="pf-card-head">
                    <div>
                        <h2>Tenant users</h2>
                        <p>{{ users.length }} loaded from the pharmacy database.</p>
                    </div>
                </div>
                <div v-if="!users.length" class="pf-card-body">
                    <div class="pf-empty">
                        <i class="bi bi-people" />
                        <strong>No users yet</strong>
                        <span>The database may still be provisioning.</span>
                    </div>
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th class="text-end">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td class="fw-semibold">{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td class="small text-muted">{{ (user.roles || []).join(', ') || '—' }}</td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-soft-primary"
                                        :disabled="company.status !== 'active'"
                                        @click="openAs(user.id)"
                                    >
                                        Login as
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div v-show="activeTab === 'ops'" class="row g-3">
            <div class="col-md-6">
                <section class="pf-card h-100">
                    <div class="pf-card-head">
                        <div>
                            <h2>{{ company.status === 'active' ? 'Lock pharmacy' : 'Unlock pharmacy' }}</h2>
                            <p>
                                {{ company.status === 'active'
                                    ? 'Blocks tenant sign-in on this host.'
                                    : 'Allows tenant sign-in again.' }}
                            </p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <form @submit.prevent="lock.post(`/platform/companies/${company.id}/lock`)">
                            <label class="pf-field">
                                <span>Your platform password</span>
                                <input v-model="lock.password" type="password" required autocomplete="current-password">
                            </label>
                            <button class="btn btn-warning btn-sm" :disabled="lock.processing">
                                {{ company.status === 'active' ? 'Lock company' : 'Unlock company' }}
                            </button>
                        </form>
                        <p v-if="lock.errors.password" class="text-danger small mt-2 mb-0">{{ lock.errors.password }}</p>
                    </div>
                </section>
            </div>
            <div class="col-md-6">
                <section class="pf-card h-100">
                    <div class="pf-card-head">
                        <div>
                            <h2>Schema</h2>
                            <p>{{ schema?.status || 'Unknown' }} · {{ (schema?.pending || []).length }} pending</p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <form @submit.prevent="upgrade.post('/platform/schema')">
                            <label class="pf-field">
                                <span>Your platform password</span>
                                <input v-model="upgrade.password" type="password" required autocomplete="current-password">
                            </label>
                            <button class="btn btn-outline-primary btn-sm" :disabled="upgrade.processing">
                                Migrate this pharmacy
                            </button>
                        </form>
                        <p v-if="upgrade.errors.password" class="text-danger small mt-2 mb-0">{{ upgrade.errors.password }}</p>
                    </div>
                </section>
            </div>
            <div class="col-12">
                <section class="pf-card">
                    <div class="pf-card-head">
                        <div>
                            <h2>Backups</h2>
                            <p>Database dumps for this pharmacy.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" @click="$inertia.post(`/platform/backups/${company.id}`)">
                            Backup now
                        </button>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li v-if="!files.length" class="list-group-item">
                            <div class="pf-empty py-4">
                                <i class="bi bi-hdd" />
                                <strong>No dumps yet</strong>
                                <span>Create a backup to download a SQL dump.</span>
                            </div>
                        </li>
                        <li
                            v-for="file in files"
                            :key="file.name"
                            class="list-group-item d-flex justify-content-between align-items-center gap-2"
                        >
                            <span class="font-monospace text-truncate">{{ file.name }}</span>
                            <Link :href="`/platform/backups/${company.id}/${file.name}`" class="btn btn-sm btn-link px-0">
                                Download
                            </Link>
                        </li>
                    </ul>
                </section>
            </div>
        </div>

        <div v-show="activeTab === 'danger'">
            <form class="pf-card" style="max-width: 28rem;" @submit.prevent="remove.delete(`/platform/companies/${company.id}`)">
                <div class="pf-card-head">
                    <div>
                        <h2>Delete pharmacy</h2>
                        <p>Removes the record, tenant database, and hostname when available.</p>
                    </div>
                </div>
                <div class="pf-card-body">
                    <label class="pf-field"><span>Type DELETE</span><input v-model="remove.confirm_text" required></label>
                    <label class="pf-field"><span>Your platform password</span><input v-model="remove.password" type="password" required></label>
                    <button class="btn btn-outline-danger btn-sm" :disabled="remove.processing">Delete pharmacy</button>
                    <p v-if="remove.errors.password" class="text-danger small mt-2 mb-0">{{ remove.errors.password }}</p>
                </div>
            </form>
        </div>
    </Layout>
</template>

<style scoped>
.pf-ops-tab {
    border: 0;
    background: transparent;
    cursor: pointer;
}

.pf-kv {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem 1rem;
    margin: 0;
}

.pf-kv > div {
    min-width: 0;
}

.pf-kv-wide {
    grid-column: 1 / -1;
}

.pf-kv dt {
    margin: 0 0 0.1rem;
    font-size: 0.72rem;
    color: var(--pf-muted, #74788d);
}

.pf-kv dd {
    margin: 0;
    font-size: 0.86rem;
    color: var(--pf-text, #343747);
    word-break: break-word;
}

.pf-stat-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pf-inline-form {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 0.65rem;
}

.pf-field-grow {
    flex: 1 1 12rem;
    margin-bottom: 0;
}

.pf-inline-btn {
    height: 2rem;
    min-height: 2rem;
    display: inline-flex;
    align-items: center;
}

@media (max-width: 767.98px) {
    .pf-kv {
        grid-template-columns: 1fr;
    }
}
</style>
