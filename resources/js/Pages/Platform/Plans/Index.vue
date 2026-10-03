<script setup>
import LunaTable from '@/Components/LunaTable.vue';
import BillingNav from '@/Components/Platform/BillingNav.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Layout from '../Layout.vue';

const props = defineProps({ plans: Array, q: String, status: String });
const form = useForm({ name: '', code: '', monthly_amount: 0, currency: 'BDT', features_text: '' });
const filters = reactive({ q: props.q || '', status: props.status || '' });
const statusOptions = [
    { value: '', label: 'All' },
    { value: 'active', label: 'Active' },
    { value: 'archived', label: 'Archived' },
];

function apply() {
    router.get('/platform/plans', filters, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Plans" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Plans</h4>
                <p class="text-muted mb-0 font-size-13">Pricing a pharmacy can be subscribed to.</p>
            </div>
        </template>

        <BillingNav />

        <form class="pf-toolbar" @submit.prevent="apply">
            <label class="pf-field" style="flex: 1 1 14rem;">
                <span>Search</span>
                <input v-model="filters.q" type="search" placeholder="Name or code">
            </label>
            <label class="pf-field" style="min-width: 9rem;">
                <span>Status</span>
                <SearchableSelect v-model="filters.status" :options="statusOptions" placeholder="Status…" />
            </label>
            <button type="submit" class="btn btn-outline-secondary btn-sm">Apply</button>
        </form>

        <section class="pf-card mb-3">
            <div class="pf-card-head">
                <div>
                    <h2>New plan</h2>
                    <p>Create a monthly package pharmacies can subscribe to.</p>
                </div>
            </div>
            <div class="pf-card-body">
                <form @submit.prevent="form.post('/platform/plans')">
                    <div class="pf-composer">
                        <label class="pf-field pf-span-4">
                            <span>Name</span>
                            <input v-model="form.name" placeholder="Starter" required>
                        </label>
                        <label class="pf-field pf-span-3">
                            <span>Code</span>
                            <input v-model="form.code" placeholder="starter" required>
                        </label>
                        <label class="pf-field pf-span-3">
                            <span>Monthly</span>
                            <input v-model="form.monthly_amount" type="number" step="0.01" min="0" required>
                        </label>
                        <label class="pf-field pf-span-2">
                            <span>Currency</span>
                            <input v-model="form.currency" required>
                        </label>
                        <label class="pf-field pf-span-12">
                            <span>Features</span>
                            <textarea v-model="form.features_text" rows="2" placeholder="One feature per line" />
                        </label>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Save plan' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="pf-card">
            <LunaTable title="Catalog">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!plans.length">
                            <td colspan="4">
                                <div class="pf-empty">
                                    <i class="bi bi-tags" />
                                    <strong>No plans yet</strong>
                                    <span>Add a plan above to start billing pharmacies.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="plan in plans" :key="plan.id">
                            <td>
                                <div class="fw-semibold">{{ plan.name }}</div>
                                <div class="small text-muted">{{ plan.code }}</div>
                                <div v-if="plan.features?.length" class="small text-muted mt-1">{{ plan.features.join(' · ') }}</div>
                            </td>
                            <td>
                                <span class="pf-money">{{ plan.monthly_amount }}</span>
                                <span class="text-muted small ms-1">{{ plan.currency }}/mo</span>
                            </td>
                            <td>
                                <StatusBadge :status="plan.status" />
                                <div class="small text-muted mt-1">{{ plan.subscriptions_count || 0 }} subscriptions</div>
                            </td>
                            <td class="text-end text-nowrap">
                                <Link :href="`/platform/plans/${plan.id}/edit`" class="btn btn-sm btn-soft-primary">Edit</Link>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-soft-secondary ms-1"
                                    @click="$inertia.post(`/platform/plans/${plan.id}/archive`)"
                                >{{ plan.status === 'active' ? 'Archive' : 'Restore' }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
        </section>
    </Layout>
</template>
