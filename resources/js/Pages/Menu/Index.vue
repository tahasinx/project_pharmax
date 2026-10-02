<template>
    <Head title="Menu Management" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Menu Management
                </h2>
                <Link :href="route('menus.create')"
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Menu Item
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Menu Items Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <LunaTable title="Menu">
<table class="table table-striped table-hover min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Menu Item
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Route
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permission
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Roles
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="menu in menus" :key="menu.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ menu.order }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <span class="text-lg">{{ menu.icon || '📋' }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ menu.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ menu.route }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ menu.permission || 'None' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="role in menu.roles" :key="role.id"
                                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                  :class="getRoleColor(role.name)">
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="toggleStatus(menu.id)"
                                                :class="menu.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-75">
                                            {{ menu.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <Link :href="route('menus.edit', menu.id)"
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </Link>
                                            <button @click="deleteMenu(menu.id)"
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
</LunaTable>
                    </div>
                </div>

                <!-- Role-based Menu Preview -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Menu Preview by Role</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-for="role in roles" :key="role.id" class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</h4>
                                <div class="space-y-1">
                                    <div v-for="menu in getMenusForRole(role.id)" :key="menu.id"
                                         class="flex items-center text-sm">
                                        <span class="mr-2">{{ menu.icon || '📋' }}</span>
                                        <span>{{ menu.name }}</span>
                                    </div>
                                    <div v-if="getMenusForRole(role.id).length === 0" class="text-gray-500 text-sm">
                                        No menu items assigned
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
import LunaTable from '@/Components/LunaTable.vue'

import { Link, router, Head } from '@inertiajs/vue3'
import { destroyRecord } from '@/Composables/confirmDelete'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    menus: Array,
    roles: Array
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

const getMenusForRole = (roleId) => {
    return props.menus.filter(menu =>
        menu.is_active && menu.roles.some(role => role.id === roleId)
    ).sort((a, b) => a.order - b.order)
}

const toggleStatus = (menuId) => {
    router.post(route('menus.toggle-status', menuId))
}

const deleteMenu = (id) => {
    destroyRecord('menus.destroy', id, 'Delete this menu item?', 'The menu item has been deleted.')
}
</script>
