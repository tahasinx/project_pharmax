<template>
    <AuthenticatedLayout>
        <FormScreen title="Edit customer" :close-href="route('customers.index')">
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
                <Link :href="route('customers.show', customer.id)" class="btn btn-outline-primary btn-sm">View</Link>
            </template>
            <form id="customer-edit-form" class="med-form" @submit.prevent="submitForm">
                <div class="med-form-grid">
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Basic information</h6>
                            <p>Name and contact details</p>
                        </header>
                        <div class="med-field">
                            <label class="field-label" for="cust-name">Customer name <span class="req">*</span></label>
                            <input id="cust-name" v-model="form.name" type="text" class="field" required autocomplete="name">
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="cust-mobile">Mobile <span class="req">*</span></label>
                                <input id="cust-mobile" v-model="form.mobile" type="tel" class="field" required autocomplete="tel">
                                <p v-if="form.errors.mobile" class="field-error">{{ form.errors.mobile }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="cust-email">Email</label>
                                <input id="cust-email" v-model="form.email" type="email" class="field" autocomplete="email">
                                <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                            </div>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="cust-phone">Phone</label>
                                <input id="cust-phone" v-model="form.phone" type="tel" class="field" autocomplete="tel">
                                <p v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="cust-fax">Fax</label>
                                <input id="cust-fax" v-model="form.fax" type="tel" class="field">
                                <p v-if="form.errors.fax" class="field-error">{{ form.errors.fax }}</p>
                            </div>
                        </div>
                    </section>
                    <section class="med-panel">
                        <header class="med-panel-head">
                            <h6>Address</h6>
                            <p>Location details</p>
                        </header>
                        <div class="med-field">
                            <label class="field-label" for="cust-address">Address</label>
                            <textarea id="cust-address" v-model="form.address" rows="3" class="field"></textarea>
                            <p v-if="form.errors.address" class="field-error">{{ form.errors.address }}</p>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="cust-city">City</label>
                                <input id="cust-city" v-model="form.city" type="text" class="field">
                                <p v-if="form.errors.city" class="field-error">{{ form.errors.city }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="cust-state">State</label>
                                <input id="cust-state" v-model="form.state" type="text" class="field">
                                <p v-if="form.errors.state" class="field-error">{{ form.errors.state }}</p>
                            </div>
                        </div>
                        <div class="med-row med-row-2">
                            <div class="med-field">
                                <label class="field-label" for="cust-zip">ZIP code</label>
                                <input id="cust-zip" v-model="form.zip" type="text" class="field">
                                <p v-if="form.errors.zip" class="field-error">{{ form.errors.zip }}</p>
                            </div>
                            <div class="med-field">
                                <label class="field-label" for="cust-country">Country</label>
                                <input id="cust-country" v-model="form.country" type="text" class="field">
                                <p v-if="form.errors.country" class="field-error">{{ form.errors.country }}</p>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Cancel</button>
                <button type="submit" form="customer-edit-form" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Update customer' }}
                </button>
            </template>
        </FormScreen>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
const props = defineProps({
    customer: Object,
})
const form = useForm({
    name: props.customer.name,
    mobile: props.customer.mobile,
    email: props.customer.email || '',
    phone: props.customer.phone || '',
    fax: props.customer.fax || '',
    address: props.customer.address || '',
    city: props.customer.city || '',
    state: props.customer.state || '',
    zip: props.customer.zip || '',
    country: props.customer.country || '',
    status: !!props.customer.status,
})
const submitForm = () => {
    form.put(route('customers.update', props.customer.id))
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