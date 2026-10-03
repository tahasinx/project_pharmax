<template>
    <Head title="Dispense prescription" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Dispense</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('prescriptions.index')" class="btn btn-soft-secondary btn-sm">Back to queue</Link>
            </div>
        </template>

        <div v-if="notice" class="alert alert-warning py-2 font-size-13 mb-3">{{ notice }}</div>

        <section class="med-panel mb-3">
            <header class="med-panel-head">
                <h6>Prescription</h6>
                <p>{{ prescription.customer?.name }} · {{ prescription.doctor_name }}</p>
            </header>
            <span class="status-chip" :class="{ 'is-on': prescription.status === 'ready' || prescription.status === 'preparing' }">
                <span class="status-dot" />
                {{ prescription.status }}
            </span>
        </section>

        <div class="med-form">
            <form
                v-for="line in lines"
                :key="line.id"
                class="med-panel"
                @submit.prevent="dispense(line)"
            >
                <header class="med-panel-head">
                    <h6>{{ line.name }}</h6>
                    <p>
                        Remaining {{ line.quantity - line.dispensed_quantity }} of {{ line.quantity }}
                        · {{ line.dose }} {{ line.frequency }} {{ line.duration }}
                    </p>
                </header>
                <div v-if="line.warnings.length" class="alert alert-danger py-2 font-size-13 mb-2">
                    {{ line.warnings.join(' ') }}
                </div>
                <div v-if="line.alternatives.length" class="med-field">
                    <label class="field-label">Same generic</label>
                    <select v-model="subs[line.id]" class="field">
                        <option value="">Keep prescribed medicine</option>
                        <option v-for="alt in line.alternatives" :key="alt.id" :value="alt.id">
                            {{ alt.name }} {{ alt.strength }}
                        </option>
                    </select>
                </div>
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div class="med-field mb-0 qty-field">
                        <label class="field-label">Quantity</label>
                        <input v-model.number="qty[line.id]" type="number" min="1" class="field">
                    </div>
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm"
                        :disabled="line.dispensed_quantity >= line.quantity"
                    >
                        Dispense
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ prescription: Object, lines: Array, notice: String })

const qty = reactive({})
const subs = reactive({})
props.lines.forEach(line => {
    qty[line.id] = Math.max(line.quantity - line.dispensed_quantity, 1)
    subs[line.id] = ''
})

const dispense = (line) => router.post(route('prescriptions.dispense', props.prescription.id), {
    prescription_item_id: line.id,
    quantity: qty[line.id],
    substitute_medicine_id: subs[line.id] || null,
})
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
    margin-bottom: 0.55rem;
}

.field-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--shell-panel-muted, #495057);
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

.qty-field {
    width: 6rem;
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
    text-transform: capitalize;
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
</style>
