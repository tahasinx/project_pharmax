<template>
    <Head title="User Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Details: {{ user.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('users.edit', user.id)"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit User
                    </Link>
                    <Link :href="route('users.index')"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Users
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                        <p class="text-sm text-gray-900">{{ user.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <p class="text-sm text-gray-900">{{ user.email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                                        <p class="text-sm text-gray-900">{{ user.id }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned Roles</h3>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="role in user.roles" :key="role.id"
                                          class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                          :class="getRoleColor(role.name)">
                                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                    </span>
                                </div>
                                <div v-if="user.roles.length === 0" class="text-gray-500 text-sm">
                                    No roles assigned
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Statistics</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                                        <p class="text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                                        <p class="text-sm text-gray-900">{{ formatDate(user.updated_at) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Verified</label>
                                        <span :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ user.email_verified_at ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <Link :href="route('users.edit', user.id)"
                                          class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Edit User
                                    </Link>
                                    <button @click="deleteUser(user.id)"
                                            :disabled="user.id === $page.props.auth.user.id"
                                            class="block w-full bg-red-500 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded text-center">
                                        Delete User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
                                <div class="space-y-3">
                                    <div v-for="role in user.roles" :key="role.id" class="border-l-4 border-blue-500 pl-3">
                                        <h4 class="text-sm font-medium text-gray-900">{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</h4>
                                        <p class="text-xs text-gray-500">{{ getRoleDescription(role.name) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    user: Object
})

const getRoleColor = (roleName) => {
    const colors = {
        'admin': 'bg-red-100 text-red-800',
        'manager': 'bg-blue-100 text-blue-800',
        'cashier': 'bg-green-100 text-green-800',
        'pharmacist': 'bg-purple-100 text-purple-800'
    }
    return colors[roleName] || 'bg-gray-100 text-gray-800'
}

const getRoleDescription = (roleName) => {
    const descriptions = {
        'admin': 'Full system access and user management',
        'manager': 'Management and reporting capabilities',
        'cashier': 'POS operations and customer management',
        'pharmacist': 'Medicine and prescription management'
    }
    return descriptions[roleName] || 'Standard user access'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', id))
    }
}
</script>
