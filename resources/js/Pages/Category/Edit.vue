<template>
    <AuthenticatedLayout>
        <FormScreen title="Edit category" :close-href="route('categories.index')">
            <template #header-actions>
                <button
                    type="button"
                    class="status-chip"
                    :class="{ 'is-on': form.status }"
                    role="switch"
                    :aria-checked="form.status"
                    @click="form.status = !form.status"
                >
                    <span class="status-dot" />
                    {{ form.status ? 'Active' : 'Inactive' }}
                </button>
            </template>

            <form id="category-edit-form" class="med-form" @submit.prevent="submitForm">
                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Category details</h6>
                        <p>Name and description for this category</p>
                    </header>

                    <div class="med-field">
                        <label class="field-label" for="cat-name">Category name <span class="req">*</span></label>
                        <input id="cat-name" v-model="form.name" type="text" class="field" required autocomplete="off">
                        <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                    </div>

                    <div class="med-field">
                        <label class="field-label" for="cat-description">Description</label>
                        <textarea
                            id="cat-description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Enter category description..."
                            class="field"
                        ></textarea>
                        <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
                    </div>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="category-edit-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Update category' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({
    category: Object,
})

const form = useForm({
    name: props.category.name,
    description: props.category.description || '',
    status: !!props.category.status,
})

const submitForm = () => {
    form.put(route('categories.update', props.category.id))
}
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.med-form-grid {
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
    margin-bottom: 0.65rem;
    padding-bottom: 0.45rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.med-panel-head h6 {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-panel-head p {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #74788d);
}

.med-field {
    margin-bottom: 0.55rem;
}

.med-field:last-child {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
    margin-bottom: 0.55rem;
}

.med-row:last-child {
    margin-bottom: 0;
}

.med-row-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
}

.req {
    color: #f46a6a;
}

.field {
    width: 100%;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0.32rem 0.55rem;
    font-size: 0.82rem;
    line-height: 1.3;
    color: var(--shell-panel-text, #343747);
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.field-error {
    margin: 0.2rem 0 0;
    font-size: 0.7rem;
    color: #f46a6a;
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
    .med-form-grid,
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
