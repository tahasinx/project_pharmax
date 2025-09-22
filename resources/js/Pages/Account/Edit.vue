<template>
    <Head title="Edit Account" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Account: {{ account.name }}
                </h2>
                <Link :href="route('accounts.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Accounts
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Information -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
                                        <input v-model="form.name"
                                               type="text"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Code</label>
                                        <input v-model="form.code"
                                               type="text"
                                               placeholder="e.g., ACC001"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Type *</label>
                                        <select v-model="form.type"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required>
                                            <option value="">Select Account Type</option>
                                            <option value="asset">Asset</option>
                                            <option value="liability">Liability</option>
                                            <option value="equity">Equity</option>
                                            <option value="revenue">Revenue</option>
                                            <option value="expense">Expense</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Balance</label>
                                        <input v-model.number="form.balance"
                                               type="number"
                                               step="0.01"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select v-model="form.status"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea v-model="form.description"
                                                  rows="4"
                                                  placeholder="Enter account description..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="mt-4 p-4 bg-blue-50 rounded-md">
                                        <h4 class="text-sm font-medium text-blue-800 mb-2">Account Type Descriptions:</h4>
                                        <ul class="text-sm text-blue-700 space-y-1">
                                            <li><strong>Asset:</strong> Resources owned by the business</li>
                                            <li><strong>Liability:</strong> Debts and obligations</li>
                                            <li><strong>Equity:</strong> Owner's interest in the business</li>
                                            <li><strong>Revenue:</strong> Income from business operations</li>
                                            <li><strong>Expense:</strong> Costs incurred in operations</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <Link :href="route('accounts.show', account.id)"
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Update Account
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    account: Object
})

const form = ref({
    name: props.account.name,
    code: props.account.code || '',
    type: props.account.type,
    balance: props.account.balance || 0,
    description: props.account.description || '',
    status: props.account.status
})

const submitForm = () => {
    router.put(route('accounts.update', props.account.id), form.value, {
        onSuccess: () => {
            // Redirect to account details
        }
    })
}
</script>
