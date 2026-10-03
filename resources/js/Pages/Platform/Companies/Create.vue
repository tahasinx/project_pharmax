<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
const check = ref('');
const checkOk = ref(null);

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
</script>

<template>
    <Head title="New pharmacy" />
    <Layout>
        <FormScreen title="New pharmacy" :close-href="route('platform.companies.index')">
            <form id="platform-company-create" class="d-grid gap-3" @submit.prevent="form.post('/platform/companies')">
                <section class="pf-card">
                    <div class="pf-card-head"><h2>Pharmacy identity</h2></div>
                    <div class="pf-card-body">
                        <p class="small text-muted mb-3">
                            Host <strong>{{ form.slug || 'name' }}.{{ baseDomain }}</strong>.
                            Creates the tenant database, pharmacy admin, menus, and
                            {{ hostEnabled ? 'the server hostname and certificate.' : 'asks the server for a hostname when the host script is installed.' }}
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="pf-field mb-0"><span>Pharmacy name</span><input v-model="form.name" required></label></div>
                            <div class="col-md-6">
                                <label class="pf-field mb-0">
                                    <span>Slug</span>
                                    <input v-model="form.slug" required placeholder="city-care" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                                </label>
                            </div>
                            <div class="col-md-6"><label class="pf-field mb-0"><span>Contact email</span><input v-model="form.email" type="email" required autocomplete="off"></label></div>
                            <div class="col-md-6"><label class="pf-field mb-0"><span>Phone</span><input v-model="form.phone"></label></div>
                            <div class="col-12"><label class="pf-field mb-0"><span>Address</span><textarea v-model="form.address" rows="2" /></label></div>
                        </div>
                        <p v-if="form.errors.slug" class="text-danger small mt-2 mb-0">{{ form.errors.slug }}</p>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card-head"><h2>Tenant database</h2></div>
                    <div class="pf-card-body">
                        <p class="small text-muted mb-3">Leave blank to use {{ prefix }}{{ (form.slug || 'name').replaceAll('-', '_') }}.</p>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-7">
                                <label class="pf-field mb-0">
                                    <span>Database name</span>
                                    <input v-model="form.database_name" :placeholder="prefix + (form.slug || 'name').replaceAll('-', '_')">
                                </label>
                            </div>
                            <div class="col-md-5">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="validateDatabase">Check availability</button>
                                <p v-if="check" class="small mt-2 mb-0" :class="checkOk ? 'text-success' : 'text-danger'">{{ check }}</p>
                                <p v-if="form.errors.database_name" class="small text-danger mt-2 mb-0">{{ form.errors.database_name }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card-head"><h2>Pharmacy admin</h2></div>
                    <div class="pf-card-body">
                        <p class="small text-muted mb-3">Created inside the new pharmacy database — not the platform admin.</p>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="pf-field mb-0"><span>Admin name</span><input v-model="form.admin_name" required autocomplete="off"></label></div>
                            <div class="col-md-4"><label class="pf-field mb-0"><span>Admin email</span><input v-model="form.admin_email" type="email" required autocomplete="off"></label></div>
                            <div class="col-md-4"><label class="pf-field mb-0"><span>Admin password</span><input v-model="form.admin_password" type="password" required minlength="8" autocomplete="new-password"></label></div>
                        </div>
                    </div>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button form="platform-company-create" class="btn btn-primary" :disabled="form.processing">Start provisioning</button>
            </template>
        </FormScreen>
    </Layout>
</template>
