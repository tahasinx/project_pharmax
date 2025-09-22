<template>
    <Head title="Edit User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit User: {{ user.name }}
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
                                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                                        <input v-model="form.password"
                                               type="password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input v-model="form.password_confirmation"
                                               type="password"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                        <h4 class="text-sm font-medium text-blue-800 mb-2">Current Roles:</h4>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="userRole in user.roles" :key="userRole.id"
                                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ userRole.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <Link :href="route('users.show', user.id)"
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Update User
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
    user: Object,
    roles: Array
})

const form = ref({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles.map(role => role.name)
})

const submitForm = () => {
    router.put(route('users.update', props.user.id), form.value, {
        onSuccess: () => {
            // Redirect to users index
        }
    })
}
</script>
