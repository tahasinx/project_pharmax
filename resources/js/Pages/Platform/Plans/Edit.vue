<script setup>
import BillingNav from '@/Components/Platform/BillingNav.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

const props = defineProps({ plan: Object });
const form = useForm({
    name: props.plan.name,
    monthly_amount: props.plan.monthly_amount,
    currency: props.plan.currency,
    status: props.plan.status,
    features_text: (props.plan.features || []).join('\n'),
});
const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'archived', label: 'Archived' },
];
</script>

<template>
    <Head title="Edit plan" />
    <Layout>
        <template #header>
            <div class="min-w-0">
                <h4 class="mb-1 font-size-18">Edit plan</h4>
                <p class="text-muted mb-0 font-size-13">{{ plan.code }}</p>
            </div>
            <div class="page-title-right">
                <Link href="/platform/plans" class="btn btn-outline-secondary btn-sm">Back to plans</Link>
            </div>
        </template>

        <BillingNav />

        <section class="pf-card" style="max-width: 40rem;">
            <div class="pf-card-head">
                <div>
                    <h2>{{ plan.name }}</h2>
                    <p>Update pricing and package details.</p>
                </div>
            </div>
            <div class="pf-card-body">
                <form @submit.prevent="form.put(`/platform/plans/${plan.id}`)">
                    <div class="pf-composer">
                        <label class="pf-field pf-span-8"><span>Name</span><input v-model="form.name" required></label>
                        <label class="pf-field pf-span-4">
                            <span>Status</span>
                            <SearchableSelect v-model="form.status" :options="statusOptions" placeholder="Status…" />
                        </label>
                        <label class="pf-field pf-span-6"><span>Monthly amount</span><input v-model="form.monthly_amount" type="number" step="0.01" required></label>
                        <label class="pf-field pf-span-6"><span>Currency</span><input v-model="form.currency" required></label>
                        <label class="pf-field pf-span-12"><span>Features</span><textarea v-model="form.features_text" rows="4" placeholder="One feature per line" /></label>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Save changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </Layout>
</template>
