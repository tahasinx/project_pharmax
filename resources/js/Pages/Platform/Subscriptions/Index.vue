<script setup>
import { computed } from 'vue';
import LunaTable from '@/Components/LunaTable.vue';
import BillingNav from '@/Components/Platform/BillingNav.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ subscriptions: Array, companies: Array, plans: Array, companyId: [String, Number] });
const form = useForm({ company_id: props.companyId || '', platform_plan_id: '', starts_on: '', ends_on: '' });

const companyOptions = computed(() => [
    { value: '', label: 'Select pharmacy' },
    ...(props.companies || []).map((company) => ({ value: company.id, label: company.name })),
]);
const planOptions = computed(() => [
    { value: '', label: 'Select plan' },
    ...(props.plans || []).map((plan) => ({ value: plan.id, label: plan.name })),
]);
</script>

<template>
    <Head title="Subscriptions" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Subscriptions</h4>
                <p class="text-muted mb-0 font-size-13">{{ subscriptions.length }} active commercial link{{ subscriptions.length === 1 ? '' : 's' }}</p>
            </div>
        </template>

        <BillingNav />

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <div>
                    <h2>Assign plan</h2>
                    <p>Connect a pharmacy to a billing package.</p>
                </div>
            </div>
            <div class="pf-card-body">
                <form @submit.prevent="form.post('/platform/subscriptions')">
                    <div class="pf-composer">
                        <label class="pf-field pf-span-4">
                            <span>Pharmacy</span>
                            <SearchableSelect v-model="form.company_id" :options="companyOptions" placeholder="Select pharmacy…" required />
                        </label>
                        <label class="pf-field pf-span-3">
                            <span>Plan</span>
                            <SearchableSelect v-model="form.platform_plan_id" :options="planOptions" placeholder="Select plan…" required />
                        </label>
                        <label class="pf-field pf-span-2">
                            <span>Starts</span>
                            <input v-model="form.starts_on" type="date" required>
                        </label>
                        <label class="pf-field pf-span-3">
                            <span>Ends</span>
                            <input v-model="form.ends_on" type="date">
                        </label>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Add subscription' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="pf-card">
            <LunaTable title="Subscriptions">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Pharmacy</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Term</th>
                            <th class="text-end">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!subscriptions.length">
                            <td colspan="5">
                                <div class="pf-empty">
                                    <i class="bi bi-arrow-repeat" />
                                    <strong>No subscriptions yet</strong>
                                    <span>Create a plan, then assign it to a pharmacy.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="row in subscriptions" :key="row.id">
                            <td class="fw-semibold">{{ row.company?.name }}</td>
                            <td>{{ row.plan?.name }}</td>
                            <td><span class="pf-money">{{ row.amount }}</span></td>
                            <td class="small text-muted">{{ row.starts_on }} → {{ row.ends_on || 'open' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                    <form class="d-inline-flex align-items-center gap-1" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/upgrade`, { platform_plan_id: $event.target.plan.value })">
                                        <select name="plan" class="form-select form-select-sm" style="width: 8.5rem;">
                                            <option v-for="plan in plans" :key="plan.id" :value="plan.id" :selected="plan.id === row.platform_plan_id">{{ plan.name }}</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-soft-primary">Upgrade</button>
                                    </form>
                                    <form class="d-inline-flex align-items-center gap-1" @submit.prevent="$inertia.post(`/platform/subscriptions/${row.id}/expiry`, { ends_on: $event.target.ends_on.value || null })">
                                        <input name="ends_on" type="date" class="form-control form-control-sm" style="width: 8.5rem;" :value="row.ends_on || ''">
                                        <button type="submit" class="btn btn-sm btn-soft-secondary">Expiry</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-soft-success" @click="$inertia.post(`/platform/subscriptions/${row.id}/invoice`)">Invoice</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
