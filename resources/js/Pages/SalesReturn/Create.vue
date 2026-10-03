<template>
    <Head title="Sales return" />
    <AuthenticatedLayout>
        <FormScreen title="Sales return" :close-href="route('invoices.index')">
            <form id="sales-return-form" class="med-form" @submit.prevent="save">
                <section class="med-panel">
                    <header class="med-panel-head">
                        <h6>Return details</h6>
                        <p>The original batch stays quarantined unless you choose to restock it.</p>
                    </header>

                    <div class="med-field">
                        <label class="field-label" for="sr-invoice">Invoice <span class="req">*</span></label>
                        <select id="sr-invoice" v-model="invoiceId" class="field" required>
                            <option value="">Select invoice</option>
                            <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.id">
                                {{ invoice.invoice_no }} · {{ invoice.customer?.name }}
                            </option>
                        </select>
                    </div>

                    <div class="med-field">
                        <label class="field-label" for="sr-line">Line <span class="req">*</span></label>
                        <select id="sr-line" v-model="form.invoice_item_id" class="field" required>
                            <option value="">Select line</option>
                            <option v-for="item in lines" :key="item.id" :value="item.id">
                                {{ item.medicine?.name }} · sold {{ item.quantity }}
                            </option>
                        </select>
                    </div>

                    <div class="med-row med-row-2">
                        <div class="med-field">
                            <label class="field-label" for="sr-qty">Quantity <span class="req">*</span></label>
                            <input id="sr-qty" v-model.number="form.quantity" type="number" min="1" class="field" required>
                        </div>
                        <div class="med-field">
                            <label class="field-label" for="sr-condition">Condition</label>
                            <select id="sr-condition" v-model="form.condition" class="field">
                                <option value="resalable">Looks resalable</option>
                                <option value="damaged">Damaged</option>
                                <option value="expired">Expired</option>
                                <option value="opened">Opened</option>
                            </select>
                        </div>
                    </div>

                    <label class="check-row font-size-13 mt-2">
                        <input v-model="form.restock" type="checkbox">
                        Put it back into available stock
                    </label>
                </section>
            </form>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="sales-return-form" class="btn btn-primary">Record return</button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'

const props = defineProps({ invoices: Array })

const invoiceId = ref('')
const lines = computed(() => props.invoices.find(invoice => invoice.id === invoiceId.value)?.items || [])
const form = reactive({ invoice_item_id: '', quantity: 1, condition: 'resalable', restock: false })

const save = () => router.post(route('sales-returns.store'), {
    ...form,
    invoice_id: invoiceId.value,
    return_date: new Date().toISOString().slice(0, 10),
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

.med-field:last-child {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
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

.check-row {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--shell-panel-muted, #74788d);
}

@media (max-width: 991.98px) {
    .med-row-2 {
        grid-template-columns: 1fr;
    }
}
</style>
