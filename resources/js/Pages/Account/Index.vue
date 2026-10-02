<template>
    <Head title="Accounts" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-[#1c1915]">Accounts</h2>
                    <p class="mt-0.5 text-[13px] text-[#746d63]">{{ filtered.length }} of {{ accounts.length }}</p>
                </div>
                <Link :href="route('accounts.create')" class="rounded-md bg-[#141a17] px-3 py-1.5 text-[13px] font-medium text-white">New account</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-[88rem] px-4 sm:px-7">
                <section class="mb-3 rounded-lg border border-[#e7e1d6] bg-white px-3 py-2.5">
                    <div class="grid items-end gap-2 md:grid-cols-[minmax(0,1.4fr)_14rem_8rem_auto]">
                        <label class="block text-[12px] text-[#746d63]">
                            Search
                            <input v-model="search" class="sheet-field mt-1 w-full" placeholder="Name or code">
                        </label>
                        <label class="block text-[12px] text-[#746d63]">
                            Type
                            <SearchableSelect v-model="typeFilter" class="mt-1" :options="typeOptions" placeholder="All types" />
                        </label>
                        <label class="block text-[12px] text-[#746d63]">
                            Rows
                            <select v-model.number="pageSize" class="sheet-field mt-1 w-full">
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </label>
                        <button type="button" class="h-8 rounded-md border border-[#e7e1d6] px-3 text-[12px]" @click="clearFilters">Clear</button>
                    </div>
                </section>

                <section class="overflow-hidden rounded-lg border border-[#e7e1d6] bg-white">
                    <div class="max-h-[68vh] overflow-auto">
                        <LunaTable title="Account">
<table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th v-for="column in columns" :key="column.key" class="cursor-pointer select-none" @click="sortBy(column.key)">
                                        {{ column.label }}
                                        <span v-if="sortKey === column.key">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pageRows.length === 0">
                                    <td colspan="6" class="py-8 text-center text-[#746d63]">No accounts found</td>
                                </tr>
                                <tr v-for="account in pageRows" :key="account.id">
                                    <td class="font-medium">{{ account.name }}</td>
                                    <td class="font-mono text-[12px]">{{ account.code || '—' }}</td>
                                    <td>{{ account.type || '—' }}</td>
                                    <td class="text-right tabular-nums">{{ money(account.balance) }}</td>
                                    <td>{{ account.status ? 'Active' : 'Inactive' }}</td>
                                    <td class="text-right">
                                        <Link :href="route('accounts.show', account.id)" class="mr-2">View</Link>
                                        <Link :href="route('accounts.edit', account.id)" class="mr-2">Edit</Link>
                                        <button type="button" class="text-[#9b2c2c]" @click="deleteAccount(account.id)">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
</LunaTable>
                    </div>
                    <div class="flex items-center justify-between border-t border-[#eee8de] px-3 py-2 text-[12px] text-[#746d63]">
                        <span>{{ rangeLabel }}</span>
                        <div class="flex gap-2">
                            <button type="button" class="px-2" :disabled="page === 1" @click="page--">Previous</button>
                            <button type="button" class="px-2" :disabled="page === pageCount" @click="page++">Next</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { computed, ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const props = defineProps({ accounts: Array })
const pageStore = usePage()
const search = ref('')
const typeFilter = ref('')
const sortKey = ref('name')
const sortDir = ref('asc')
const page = ref(1)
const pageSize = ref(25)
const typeOptions = [
    { value: '', label: 'All types' },
    { value: 'asset', label: 'Asset' },
    { value: 'liability', label: 'Liability' },
    { value: 'equity', label: 'Equity' },
    { value: 'revenue', label: 'Revenue' },
    { value: 'expense', label: 'Expense' },
]
const columns = [
    { key: 'name', label: 'Name' },
    { key: 'code', label: 'Code' },
    { key: 'type', label: 'Type' },
    { key: 'balance', label: 'Balance' },
    { key: 'status', label: 'Status' },
]

const filtered = computed(() => {
    const needle = search.value.trim().toLowerCase()
    const rows = props.accounts.filter((account) => {
        const matchesText = !needle || account.name.toLowerCase().includes(needle) || String(account.code || '').toLowerCase().includes(needle)
        const matchesType = typeFilter.value === '' || account.type === typeFilter.value
        return matchesText && matchesType
    })
    const dir = sortDir.value === 'asc' ? 1 : -1
    return [...rows].sort((a, b) => String(a[sortKey.value] ?? '').localeCompare(String(b[sortKey.value] ?? ''), undefined, { numeric: true }) * dir)
})
const pageCount = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)))
const pageRows = computed(() => filtered.value.slice((page.value - 1) * pageSize.value, page.value * pageSize.value))
const rangeLabel = computed(() => {
    if (!filtered.value.length) return '0 rows'
    const start = (page.value - 1) * pageSize.value + 1
    const end = Math.min(page.value * pageSize.value, filtered.value.length)
    return `${start}–${end} of ${filtered.value.length}`
})

watch([search, typeFilter, pageSize], () => { page.value = 1 })
watch(pageCount, (count) => { if (page.value > count) page.value = count })

const sortBy = (key) => {
    if (sortKey.value === key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    else { sortKey.value = key; sortDir.value = 'asc' }
}
const clearFilters = () => { search.value = ''; typeFilter.value = '' }
const money = (amount) => {
    const ui = pageStore.props.ui || {}
    const value = Number(amount || 0).toFixed(2)
    return ui.currency_position === 'after' ? `${value}${ui.currency_symbol || ''}` : `${ui.currency_symbol || ''}${value}`
}
const deleteAccount = (id) => {
    destroyRecord('accounts.destroy', id, 'Delete this account?', 'The account has been deleted.')
}
</script>
