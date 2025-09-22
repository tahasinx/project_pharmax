<template>
    <Head title="Edit Menu Item" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Menu Item: {{ menu.name }}
                </h2>
                <Link :href="route('menus.index')"
                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Menus
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
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Menu Information</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name *</label>
                                        <input v-model="form.name"
                                               type="text"
                                               placeholder="e.g., Dashboard, Medicines"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Route Name *</label>
                                        <input v-model="form.route"
                                               type="text"
                                               placeholder="e.g., dashboard, medicines.index"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji or Class)</label>
                                        <input v-model="form.icon"
                                               type="text"
                                               placeholder="e.g., 🏠, 📊, 💊"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Order *</label>
                                        <input v-model.number="form.order"
                                               type="number"
                                               min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Permission Required</label>
                                        <select v-model="form.permission"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">No Permission Required</option>
                                            <option value="view-dashboard">View Dashboard</option>
                                            <option value="manage-medicines">Manage Medicines</option>
                                            <option value="manage-customers">Manage Customers</option>
                                            <option value="manage-invoices">Manage Invoices</option>
                                            <option value="manage-purchases">Manage Purchases</option>
                                            <option value="manage-accounts">Manage Accounts</option>
                                            <option value="manage-categories">Manage Categories</option>
                                            <option value="manage-manufacturers">Manage Manufacturers</option>
                                            <option value="manage-banks">Manage Banks</option>
                                            <option value="manage-users">Manage Users</option>
                                            <option value="manage-menus">Manage Menus</option>
                                            <option value="pos-access">POS Access</option>
                                            <option value="view-reports">View Reports</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center">
                                        <input v-model="form.is_active"
                                               type="checkbox"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label class="ml-2 text-sm text-gray-900">Active</label>
                                    </div>
                                </div>

                                <!-- Role Assignment -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assign to Roles</h3>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Roles *</label>
                                        <div class="space-y-2">
                                            <div v-for="role in roles" :key="role.id" class="flex items-center">
                                                <input :id="`role-${role.id}`"
                                                       v-model="form.roles"
                                                       :value="role.id"
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
                                            <span v-for="menuRole in menu.roles" :key="menuRole.id"
                                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ menuRole.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <Link :href="route('menus.index')"
                                      class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Update Menu Item
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
    menu: Object,
    roles: Array
})

const form = ref({
    name: props.menu.name,
    route: props.menu.route,
    icon: props.menu.icon || '',
    order: props.menu.order,
    is_active: props.menu.is_active,
    permission: props.menu.permission || '',
    roles: props.menu.roles.map(role => role.id)
})

const submitForm = () => {
    router.put(route('menus.update', props.menu.id), form.value, {
        onSuccess: () => {
            // Redirect to menus index
        }
    })
}
</script>
