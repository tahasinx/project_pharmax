<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import FormScreen from '@/Components/FormScreen.vue';
import Layout from '../Layout.vue';

const props = defineProps({ company: Object });
const form = useForm({
    name: props.company.name,
    email: props.company.email || '',
    admin_email: props.company.admin_email || '',
    phone: props.company.phone || '',
    address: props.company.address || '',
    status: props.company.status,
});
</script>

<template>
    <Head title="Edit pharmacy" />
    <Layout>
        <FormScreen :title="`Edit ${company.name}`" :close-href="route('platform.companies.show', company.id)">
            <form id="platform-company-edit" class="pf-card" @submit.prevent="form.put(`/platform/companies/${company.id}`)">
                <div class="pf-card-body">
                    <p class="small text-muted mb-3">
                        Slug <code>{{ company.slug }}</code> and database <code>{{ company.database_name }}</code> stay fixed after provisioning.
                    </p>
                    <div class="row g-3">
                        <div class="col-12"><label class="pf-field mb-0"><span>Pharmacy name</span><input v-model="form.name" required></label></div>
                        <div class="col-md-6"><label class="pf-field mb-0"><span>Contact email</span><input v-model="form.email" type="email"></label></div>
                        <div class="col-md-6"><label class="pf-field mb-0"><span>Admin email</span><input v-model="form.admin_email" type="email"></label></div>
                        <div class="col-md-6"><label class="pf-field mb-0"><span>Phone</span><input v-model="form.phone"></label></div>
                        <div class="col-md-6">
                            <label class="pf-field mb-0">
                                <span>Status</span>
                                <select v-model="form.status">
                                    <option value="active">Active</option>
                                    <option value="locked">Locked</option>
                                </select>
                            </label>
                        </div>
                        <div class="col-12"><label class="pf-field mb-0"><span>Address</span><textarea v-model="form.address" rows="2" /></label></div>
                    </div>
                </div>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button form="platform-company-edit" class="btn btn-primary" :disabled="form.processing">Save</button>
            </template>
        </FormScreen>
    </Layout>
</template>
