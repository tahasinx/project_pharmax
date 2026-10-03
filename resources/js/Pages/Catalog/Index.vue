<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">{{ title }}</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm" @click="openCreate">
                    Add {{ itemLabel }}
                </button>
            </div>
        </template>

        <div v-if="flash.error" class="alert alert-danger py-2">{{ flash.error }}</div>
        <div v-if="flash.success" class="alert alert-success py-2">{{ flash.success }}</div>

        <LunaTable :title="title" :empty-text="`No ${title.toLowerCase()} found`">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Medicines</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.id">
                        <td>
                            <div class="fw-semibold">{{ row.name }}</div>
                        </td>
                        <td>{{ row.medicines_count ?? 0 }}</td>
                        <td>
                            <div class="dt-actions">
                                <button type="button" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit" @click="openEdit(row)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="remove(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>

        <FormScreen
            v-if="modalOpen"
            :title="modalTitle"
            size="md"
            @close="closeModal"
        >
            <form id="catalog-form" class="med-form" @submit.prevent="save">
                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>{{ itemLabel }} details</h6>
                        <p>Catalog name used across medicines</p>
                    </header>
                    <div class="med-field">
                        <label class="field-label" for="catalog-name">Name <span class="req">*</span></label>
                        <input
                            id="catalog-name"
                            v-model="name"
                            type="text"
                            class="field"
                            required
                            autocomplete="off"
                            autofocus
                        >
                        <p v-if="formError" class="field-error">{{ formError }}</p>
                    </div>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="catalog-form" class="btn btn-primary" :disabled="saving">
                    {{ saving ? 'Saving…' : (editingId ? 'Update' : 'Save') }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { confirmDelete, notify } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import LunaTable from '@/Components/LunaTable.vue'

const props = defineProps({
    title: String,
    itemLabel: { type: String, default: null },
    rows: Array,
    storeRoute: String,
    updateRoute: String,
    destroyRoute: String,
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const formError = computed(() => page.props.errors?.name || '')

const itemLabel = computed(() => {
    if (props.itemLabel) return props.itemLabel
    const title = props.title || 'Item'
    if (title.endsWith('ies')) return `${title.slice(0, -3)}y`
    if (title.endsWith('s')) return title.slice(0, -1)
    return title
})

const modalOpen = ref(false)
const editingId = ref(null)
const name = ref('')
const saving = ref(false)

const modalTitle = computed(() => (editingId.value ? `Edit ${itemLabel.value}` : `Add ${itemLabel.value}`))

const openCreate = () => {
    editingId.value = null
    name.value = ''
    modalOpen.value = true
}

const openEdit = (row) => {
    editingId.value = row.id
    name.value = row.name
    modalOpen.value = true
}

const closeModal = () => {
    modalOpen.value = false
    editingId.value = null
    name.value = ''
    saving.value = false
}

const save = () => {
    saving.value = true
    const options = {
        onSuccess: () => closeModal(),
        onFinish: () => { saving.value = false },
        onError: () => { saving.value = false },
    }

    if (editingId.value) {
        router.put(route(props.updateRoute, editingId.value), { name: name.value }, options)
        return
    }

    router.post(route(props.storeRoute), { name: name.value }, options)
}

const remove = (row) => {
    confirmDelete({
        title: `Delete this ${itemLabel.value.toLowerCase()}?`,
        text: `"${row.name}" will be removed permanently.`,
    }).then((result) => {
        if (!result.isConfirmed) return
        router.delete(route(props.destroyRoute, row.id), {
            onSuccess: () => notify({ title: 'Deleted', text: `${itemLabel.value} has been deleted.` }),
            onError: () => notify({ title: 'Could not delete', text: 'Something went wrong while deleting.', tone: 'error' }),
        })
    })
}
</script>

<style scoped>
.med-form {
    display: flex;
    flex-direction: column;
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
    margin-bottom: 0;
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
    padding: 0.45rem 0.65rem;
    font-size: 0.9rem;
    line-height: 1.35;
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
</style>
