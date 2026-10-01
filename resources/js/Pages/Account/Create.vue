<template>
    <Head title="New account" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-[#1c1915]">New account</h2>
                <Link :href="route('accounts.index')" class="text-[13px] text-[#746d63]">Back</Link>
            </div>
        </template>

        <div class="py-12">
            <form class="mx-auto max-w-3xl space-y-3 px-4 sm:px-7" @submit.prevent="submitForm">
                <section class="rounded-lg border border-[#e7e1d6] bg-white">
                    <header class="border-b border-[#eee8de] px-4 py-3">
                        <h3 class="text-sm font-semibold">Identity</h3>
                        <p class="text-[12px] text-[#746d63]">The name staff will see on invoices and journals.</p>
                    </header>
                    <div class="grid gap-3 p-4 sm:grid-cols-2">
                        <label class="block text-[12px] text-[#746d63] sm:col-span-2">Account name
                            <input v-model="form.name" class="sheet-field mt-1 w-full" required>
                        </label>
                        <label class="block text-[12px] text-[#746d63]">Code
                            <input v-model="form.code" class="sheet-field mt-1 w-full" placeholder="CASH001">
                        </label>
                        <label class="block text-[12px] text-[#746d63]">Opening balance
                            <input v-model.number="form.balance" type="number" step="0.01" class="sheet-field mt-1 w-full">
                        </label>
                    </div>
                </section>

                <section class="rounded-lg border border-[#e7e1d6] bg-white">
                    <header class="border-b border-[#eee8de] px-4 py-3">
                        <h3 class="text-sm font-semibold">Classification</h3>
                        <p class="text-[12px] text-[#746d63]">Type decides where the balance appears in the books.</p>
                    </header>
                    <div class="grid gap-3 p-4 sm:grid-cols-2">
                        <label class="block text-[12px] text-[#746d63]">Type
                            <SearchableSelect v-model="form.type" class="mt-1" :options="typeOptions" placeholder="Choose a type" />
                        </label>
                        <label class="block text-[12px] text-[#746d63]">Status
                            <SearchableSelect v-model="form.status" class="mt-1" :options="statusOptions" placeholder="Status" />
                        </label>
                        <label class="block text-[12px] text-[#746d63] sm:col-span-2">Description
                            <textarea v-model="form.description" rows="3" class="sheet-field mt-1 w-full" />
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2">
                    <Link :href="route('accounts.index')" class="rounded-md border border-[#e7e1d6] bg-white px-3 py-1.5 text-[13px]">Cancel</Link>
                    <button class="rounded-md bg-[#141a17] px-3 py-1.5 text-[13px] text-white">Save account</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const form = ref({ name: '', code: '', type: '', balance: 0, description: '', status: true })
const typeOptions = [
    { value: 'asset', label: 'Asset' },
    { value: 'liability', label: 'Liability' },
    { value: 'equity', label: 'Equity' },
    { value: 'revenue', label: 'Revenue' },
    { value: 'expense', label: 'Expense' },
]
const statusOptions = [
    { value: true, label: 'Active' },
    { value: false, label: 'Inactive' },
]
const submitForm = () => router.post(route('accounts.store'), form.value)
</script>
