<template>
    <Head title="Finance" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Finance</h4>
        </template>

        <section class="med-panel mb-3">
            <header class="med-panel-head">
                <h6>Period summary</h6>
                <p>Profit after cost and sales returns</p>
            </header>
            <div class="fw-semibold font-size-16">{{ profit }}</div>

            <div class="mt-3 pt-3 border-top">
                <h6 class="section-label">Customer receipt</h6>
                <form class="med-row med-row-4 mt-2" @submit.prevent="receive">
                    <div class="med-field">
                        <label class="field-label">Open invoice <span class="req">*</span></label>
                        <select v-model="receipt.invoice_id" class="field" required>
                            <option value="">Select invoice</option>
                            <option v-for="inv in openInvoices" :key="inv.id" :value="inv.id">
                                {{ inv.invoice_no }} · due {{ inv.due_amount }}
                            </option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Amount <span class="req">*</span></label>
                        <input v-model.number="receipt.amount" type="number" min="0.01" step="0.01" class="field" required>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Method</label>
                        <select v-model="receipt.method" class="field">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                        </select>
                    </div>
                    <div class="med-field d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Record receipt</button>
                    </div>
                </form>
            </div>

            <div class="mt-3 pt-3 border-top">
                <h6 class="section-label">Supplier payment</h6>
                <form class="med-row med-row-4 mt-2" @submit.prevent="paySupplier">
                    <div class="med-field">
                        <label class="field-label">Supplier <span class="req">*</span></label>
                        <select v-model="payment.supplier_id" class="field" required>
                            <option value="">Select supplier</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Amount <span class="req">*</span></label>
                        <input v-model.number="payment.amount" type="number" min="0.01" step="0.01" class="field" required>
                    </div>
                    <div class="med-field">
                        <label class="field-label">Method</label>
                        <select v-model="payment.method" class="field">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="med-field d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary btn-sm w-100">Pay supplier</button>
                    </div>
                </form>
            </div>
        </section>

        <LunaTable title="Chart of accounts" empty-text="No accounts found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in accounts" :key="row.code">
                        <td>{{ row.code }}</td>
                        <td class="fw-semibold">{{ row.name }}</td>
                        <td>{{ row.type }}</td>
                        <td>{{ row.balance }}</td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'

defineProps({
    accounts: Array,
    profit: Number,
    openInvoices: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
})

const receipt = reactive({ invoice_id: '', amount: '', method: 'cash' })
const payment = reactive({ supplier_id: '', amount: '', method: 'cash' })
const receive = () => router.post(route('finance.customer-receipt'), receipt)
const paySupplier = () => router.post(route('finance.supplier-payment'), payment)
</script>

<style scoped>
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

.section-label {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--shell-panel-text, #343747);
}

.med-field {
    margin-bottom: 0;
}

.med-row {
    display: grid;
    gap: 0.55rem;
}

.med-row-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
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
    height: 2rem;
    min-height: 2rem;
    border-radius: var(--pf-radius, 0.35rem);
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0 0.65rem;
    font-size: 0.82rem;
    line-height: calc(2rem - 2px);
    color: var(--shell-panel-text, #343747);
}

.med-panel .btn,
.med-panel .btn-sm {
    height: 2rem;
    min-height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-top: 0;
    padding-bottom: 0;
}

.field:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

@media (max-width: 991.98px) {
    .med-row-4 {
        grid-template-columns: 1fr;
    }
}
</style>
