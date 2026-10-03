<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import Layout from '../Layout.vue';

const props = defineProps({
    company: Object,
    host: String,
    subscription: Object,
    schema: Object,
    files: Array,
    users: { type: Array, default: () => [] },
});

const lock = useForm({ password: '' });
const login = useForm({ password: '', user_id: '' });
const remove = useForm({ password: '', confirm_text: '' });
const upgrade = useForm({ password: '', company_id: props.company.id });

const openAs = (userId = '') => {
    login.user_id = userId ? String(userId) : '';
    login.post(`/platform/companies/${props.company.id}/login-as`);
};
</script>

<template>
    <Head :title="company.name" />
    <Layout>
        <div class="pf-page-head">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="mb-0">{{ company.name }}</h1>
                    <StatusBadge :status="company.status" />
                    <StatusBadge :status="company.provision_status" kind="provision" />
                </div>
                <p class="pf-page-sub">Tenant controls — login, lockdown, schema, backups, and users.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <Link href="/platform/companies" class="btn btn-outline-secondary btn-sm">All pharmacies</Link>
                <Link :href="`/platform/companies/${company.id}/provision`" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cpu me-1" />Provision
                </Link>
                <Link :href="`/platform/companies/${company.id}/edit`" class="btn btn-primary btn-sm">Edit</Link>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <section class="pf-card h-100">
                    <div class="pf-card-head"><h2>Identity</h2></div>
                    <div class="pf-card-body">
                        <dl class="row mb-0 small">
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Slug</dt><dd class="font-monospace mb-0">{{ company.slug }}</dd></div>
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Database</dt><dd class="font-monospace mb-0">{{ company.database_name }}</dd></div>
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Email</dt><dd class="mb-0">{{ company.email || '—' }}</dd></div>
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Admin login</dt><dd class="mb-0">{{ company.admin_email || '—' }}</dd></div>
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Phone</dt><dd class="mb-0">{{ company.phone || '—' }}</dd></div>
                            <div class="col-sm-6 mb-2"><dt class="text-muted">Hostname</dt><dd class="mb-0">{{ company.vhost_status || '—' }} · SSL {{ company.ssl_status || '—' }}</dd></div>
                            <div class="col-12 mb-2"><dt class="text-muted">Address</dt><dd class="mb-0">{{ company.address || '—' }}</dd></div>
                            <div class="col-12"><dt class="text-muted">URL</dt><dd class="mb-0"><a :href="host" target="_blank" rel="noopener">{{ host }}</a></dd></div>
                        </dl>
                        <p v-if="company.provision_error" class="text-danger small mt-3 mb-0">{{ company.provision_error }}</p>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="pf-card h-100">
                    <div class="pf-card-head"><h2>Subscription</h2></div>
                    <div class="pf-card-body small">
                        <p class="mb-1">Plan: <strong>{{ subscription?.plan?.name || 'None' }}</strong></p>
                        <p class="mb-1">Status: {{ subscription?.status || '—' }}</p>
                        <p class="mb-3">Ends: {{ subscription?.ends_on || '—' }}</p>
                        <Link :href="`/platform/subscriptions?company=${company.id}`" class="btn btn-sm btn-outline-primary">Manage subscription</Link>
                    </div>
                </section>
            </div>
        </div>

        <div class="pf-control-grid mb-3">
            <section class="pf-card">
                <div class="pf-card-head"><h2>Open as pharmacy user</h2></div>
                <div class="pf-card-body">
                    <p class="small text-muted mb-3">Signs into the tenant host as the selected user (default: pharmacy admin).</p>
                    <form @submit.prevent="openAs(login.user_id)">
                        <label class="pf-field">
                            <span>User</span>
                            <select v-model="login.user_id">
                                <option value="">Pharmacy admin (default)</option>
                                <option v-for="user in users" :key="user.id" :value="String(user.id)">
                                    {{ user.name }} · {{ user.email }}{{ user.roles?.length ? ` (${user.roles.join(', ')})` : '' }}
                                </option>
                            </select>
                        </label>
                        <label class="pf-field">
                            <span>Your platform password</span>
                            <input v-model="login.password" type="password" required autocomplete="current-password">
                        </label>
                        <button class="btn btn-primary btn-sm" :disabled="company.status !== 'active' || login.processing">
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
                <div class="pf-card-head"><h2>{{ company.status === 'active' ? 'Lock pharmacy' : 'Unlock pharmacy' }}</h2></div>
                <div class="pf-card-body">
                    <p class="small text-muted mb-3">
                        {{ company.status === 'active' ? 'Locking blocks tenant sign-in on this host.' : 'Unlocking allows tenant sign-in again.' }}
                    </p>
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

            <section class="pf-card">
                <div class="pf-card-head"><h2>Schema</h2></div>
                <div class="pf-card-body">
                    <p class="small mb-3">
                        {{ schema?.status || 'Unknown' }} · {{ (schema?.pending || []).length }} pending
                    </p>
                    <form @submit.prevent="upgrade.post('/platform/schema')">
                        <label class="pf-field">
                            <span>Your platform password</span>
                            <input v-model="upgrade.password" type="password" required autocomplete="current-password">
                        </label>
                        <button class="btn btn-outline-primary btn-sm" :disabled="upgrade.processing">Migrate this pharmacy</button>
                    </form>
                    <p v-if="upgrade.errors.password" class="text-danger small mt-2 mb-0">{{ upgrade.errors.password }}</p>
                </div>
            </section>

            <section class="pf-card">
                <div class="pf-card-head">
                    <h2>Backups</h2>
                    <button class="btn btn-sm btn-outline-primary" @click="$inertia.post(`/platform/backups/${company.id}`)">Backup now</button>
                </div>
                <ul class="list-group list-group-flush small">
                    <li v-if="!files.length" class="list-group-item text-muted">No dumps yet.</li>
                    <li v-for="file in files" :key="file.name" class="list-group-item d-flex justify-content-between align-items-center gap-2">
                        <span class="font-monospace text-truncate">{{ file.name }}</span>
                        <Link :href="`/platform/backups/${company.id}/${file.name}`" class="btn btn-sm btn-link px-0">Download</Link>
                    </li>
                </ul>
            </section>
        </div>

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <h2>Tenant users</h2>
                <span class="small text-muted">{{ users.length }} loaded</span>
            </div>
            <div v-if="!users.length" class="pf-card-body text-muted small">
                No users found in this pharmacy database yet (or the database is not ready).
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
                                    <i class="bi bi-person-badge me-1" />
                                    Login as
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <form class="pf-card" style="max-width: 28rem;" @submit.prevent="remove.delete(`/platform/companies/${company.id}`)">
            <div class="pf-card-head"><h2>Delete pharmacy</h2></div>
            <div class="pf-card-body">
                <p class="small text-muted mb-3">Removes the pharmacy record, tenant database, and hostname when the host script is available.</p>
                <label class="pf-field"><span>Type DELETE</span><input v-model="remove.confirm_text" required></label>
                <label class="pf-field"><span>Your platform password</span><input v-model="remove.password" type="password" required></label>
                <button class="btn btn-outline-danger btn-sm" :disabled="remove.processing">Delete pharmacy</button>
                <p v-if="remove.errors.password" class="text-danger small mt-2 mb-0">{{ remove.errors.password }}</p>
            </div>
        </form>
    </Layout>
</template>
