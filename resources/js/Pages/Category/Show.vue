<template>
    <AuthenticatedLayout>
        <FormScreen :title="category.name" :close-href="route('categories.index')">
            <template #header-actions>
                <span class="status-chip" :class="{ 'is-on': category.status }">
                    <span class="status-dot" />
                    {{ category.status ? 'Active' : 'Inactive' }}
                </span>
                <Link :href="route('categories.edit', category.id)" class="btn btn-primary btn-sm">Edit</Link>
            </template>

            <div class="med-view">
                <div class="med-view-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Category information</h6>
                            <p>Overview and counts</p>
                        </header>
                        <dl class="med-facts">
                            <div class="med-fact">
                                <dt>Category</dt>
                                <dd>{{ category.name }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Medicines</dt>
                                <dd>{{ category.medicines_count || 0 }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Created</dt>
                                <dd>{{ formatDate(category.created_at) }}</dd>
                            </div>
                            <div class="med-fact">
                                <dt>Description</dt>
                                <dd>{{ category.description || '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Quick actions</h6>
                            <p>Common tasks for this category</p>
                        </header>
                        <div class="d-flex flex-wrap gap-2">
                            <Link :href="route('categories.edit', category.id)" class="btn btn-sm btn-primary">
                                Edit category
                            </Link>
                            <Link :href="route('medicines.create')" class="btn btn-sm btn-soft-primary">
                                Add medicine
                            </Link>
                        </div>
                    </section>
                </div>

                <section v-if="category.medicines && category.medicines.length > 0" class="med-panel">
                    <header class="med-panel-head">
                        <div>
                            <h6>Medicines in this category</h6>
                            <p>Products assigned to this category</p>
                        </div>
                    </header>

                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Manufacturer</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="medicine in category.medicines" :key="medicine.id">
                                <td>
                                    <div class="fw-semibold">{{ medicine.name }}</div>
                                    <div class="text-muted font-size-12">{{ medicine.generic_name || 'No generic name' }}</div>
                                </td>
                                <td>{{ medicine.manufacturer?.name || 'N/A' }}</td>
                                <td>{{ money(medicine.price) }}</td>
                                <td>
                                    <span class="status-chip" :class="{ 'is-on': medicine.status }">
                                        <span class="status-dot" />
                                        {{ medicine.status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
                <Link :href="route('categories.edit', category.id)" class="btn btn-primary">Edit</Link>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

defineProps({
    category: Object,
})

const page = usePage()

const money = (value) => {
    const ui = page.props.ui || {}
    const amount = Number(value || 0).toFixed(2)
    return ui.currency_position === 'after'
        ? `${amount}${ui.currency_symbol || ''}`
        : `${ui.currency_symbol || ''}${amount}`
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<style scoped>
.med-view {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
}

.med-panel {
    background: var(--shell-panel-bg, #f8f9fc);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    padding: 1rem 1.1rem 1.15rem;
}

.med-panel-head {
    margin-bottom: 0.85rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem 1rem;
    margin: 0;
}

.med-fact {
    min-width: 0;
}

.med-fact dt {
    margin: 0 0 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.med-fact dd {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    word-break: break-word;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    border-radius: var(--pf-radius, 999px);
    padding: 0.22rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #74788d);
}

.status-chip.is-on {
    border-color: var(--shell-panel-accent-border, #b7ebd6);
    background: var(--shell-panel-accent-bg, #e8f8f1);
    color: var(--shell-panel-accent-text, #1e8f68);
}

.status-dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 999px;
    background: var(--shell-panel-muted, #adb5bd);
}

.status-chip.is-on .status-dot {
    background: #34c38f;
}

@media (max-width: 991.98px) {
    .med-view-grid,
    .med-facts {
        grid-template-columns: 1fr;
    }
}
</style>
