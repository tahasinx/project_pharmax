<template>
    <Head title="Create User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New User
                </h2>
                <Link :href="route('users.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Users
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
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                        <input v-model="form.name"
                                               type="text"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                        <input v-model="form.email"
                                               type="email"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                        <input v-model="form.password"
                                               type="password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                        <input v-model="form.password_confirmation"
                                               type="password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>
                                </div>

                                <!-- Roles -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Roles</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Roles *</label>
                                        <div class="space-y-2">
                                            <div v-for="role in roles" :key="role.id" class="flex items-center">
                                                <input :id="`role-${role.id}`"
                                                       v-model="form.roles"
                                                       :value="role.name"
                                                       type="checkbox"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label :for="`role-${role.id}`" class="ml-2 text-sm text-gray-900">
                                                    {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-4 bg-blue-50 rounded-md">
                                        <h4 class="text-sm font-medium text-blue-800 mb-2">Role Descriptions:</h4>
                                        <ul class="text-sm text-blue-700 space-y-1">
                                            <li><strong>Admin:</strong> Full system access</li>
                                            <li><strong>Manager:</strong> Management and reporting access</li>
                                            <li><strong>Cashier:</strong> POS and customer management</li>
                                            <li><strong>Pharmacist:</strong> Medicine and prescription management</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <Link :href="route('users.index')"
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create User
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
    roles: Array
})

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: []
})

const submitForm = () => {
    router.post(route('users.store'), form.value, {
        onSuccess: () => {
            // Redirect to users index
        }
    })
}
</script>
