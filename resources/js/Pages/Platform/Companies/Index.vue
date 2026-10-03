<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import LunaTable from '@/Components/LunaTable.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import FilterSummary from '@/Components/FilterSummary.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import StatusBadge from '@/Components/Platform/StatusBadge.vue';
import Layout from '../Layout.vue';

const props = defineProps({
    companies: Object,
    q: String,
    provision: String,
    baseDomain: String,
});

const showFilters = ref(false);
const filters = reactive({ q: props.q || '', provision: props.provision || '' });
const provisionOptions = [
    { value: '', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'running', label: 'Running' },
    { value: 'active', label: 'Active' },
    { value: 'degraded', label: 'Degraded' },
    { value: 'failed', label: 'Failed' },
];

const chips = computed(() => {
    const list = [];
    if (filters.q) list.push({ key: 'q', label: 'Search', value: filters.q });
    if (filters.provision) list.push({ key: 'provision', label: 'Provision', value: filters.provision });
    return list;
});

const apply = () => {
    showFilters.value = false;
    router.get('/platform/companies', { ...filters }, { preserveState: true, replace: true });
};
const clear = () => {
    filters.q = '';
    filters.provision = '';
    apply();
};
const removeChip = (key) => {
    filters[key] = '';
    apply();
};
</script>

<template>
    <Head title="Pharmacies" />
    <Layout>
        <template #header>
            <div class="d-flex flex-column gap-2 min-w-0">
                <h4 class="mb-0 font-size-18">Pharmacies</h4>
                <p class="text-muted mb-0 font-size-13">{{ companies.total }} tenants on this platform</p>
                <FilterSummary
                    :chips="chips"
                    :show-button="false"
                    @clear="clear"
                    @remove="removeChip"
                />
            </div>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="showFilters = true">
                    <i class="bi bi-funnel me-1" />
                    Filters
                    <span v-if="chips.length" class="badge bg-primary ms-1">{{ chips.length }}</span>
                </button>
                <Link href="/platform/companies/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1" />
                    New pharmacy
                </Link>
            </div>
        </template>

        <FilterDrawer
            :show="showFilters"
            title="Pharmacy filters"
            @close="showFilters = false"
            @apply="apply"
            @clear="clear"
        >
            <div class="filter-field">
                <label class="field-label">Search</label>
                <input v-model="filters.q" type="search" class="field" placeholder="Name, slug, database, email…">
            </div>
            <div class="filter-field">
                <label class="field-label">Provision</label>
                <SearchableSelect v-model="filters.provision" :options="provisionOptions" placeholder="Provision…" />
            </div>
        </FilterDrawer>

        <section class="pf-card">
            <LunaTable title="Companies">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Host</th>
                            <th>Database</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!companies.data.length">
                            <td colspan="5" class="py-5 text-center text-muted">No pharmacies match.</td>
                        </tr>
                        <tr v-for="company in companies.data" :key="company.id">
                            <td>
                                <Link :href="`/platform/companies/${company.id}`" class="fw-semibold text-decoration-none">
                                    {{ company.name }}
                                </Link>
                                <div class="small text-muted">{{ company.email || company.admin_email || 'No contact email' }}</div>
                            </td>
                            <td class="font-monospace small">{{ company.slug }}.{{ baseDomain }}</td>
                            <td class="font-monospace small">{{ company.database_name }}</td>
                            <td>
                                <StatusBadge
                                    :status="company.status"
                                    :provision="company.provision_status"
                                />
                            </td>
                            <td>
                                <div class="dt-actions justify-content-end">
                                    <Link :href="`/platform/companies/${company.id}`" class="btn btn-sm btn-icon btn-soft-primary" title="Control">
                                        <i class="bi bi-sliders" />
                                    </Link>
                                    <Link :href="`/platform/companies/${company.id}/provision`" class="btn btn-sm btn-icon btn-soft-secondary" title="Provision">
                                        <i class="bi bi-cpu" />
                                    </Link>
                                    <Link :href="`/platform/companies/${company.id}/edit`" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit">
                                        <i class="bi bi-pencil" />
                                    </Link>
                                    <Link :href="`/platform/subscriptions?company=${company.id}`" class="btn btn-sm btn-icon btn-soft-success" title="Subscribe">
                                        <i class="bi bi-credit-card" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </LunaTable>
            <div v-if="companies.last_page > 1" class="d-flex justify-content-end align-items-center gap-3 px-3 py-3 border-top">
                <Link v-if="companies.prev_page_url" :href="companies.prev_page_url" class="btn btn-sm btn-outline-secondary">Previous</Link>
                <span class="small text-muted">{{ companies.current_page }} / {{ companies.last_page }}</span>
                <Link v-if="companies.next_page_url" :href="companies.next_page_url" class="btn btn-sm btn-outline-secondary">Next</Link>
            </div>
        </section>
    </Layout>
</template>
