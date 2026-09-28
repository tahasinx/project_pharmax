<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const sidebarOpen = ref(false);

const menus = computed(() => {
    const raw = page.props.menus || [];
    return Array.isArray(raw) ? raw : Object.values(raw);
});

const sections = [
    { label: 'Counter', names: ['Dashboard', 'POS'] },
    { label: 'Catalog', names: ['Medicines', 'Generics', 'Brands', 'Categories', 'Manufacturers'] },
    { label: 'Inventory', names: ['Stock', 'Expiry', 'Transfers'] },
    { label: 'Sales', names: ['Invoices', 'Sales Returns', 'Customers', 'Prescriptions'] },
    { label: 'Buying', names: ['Purchases', 'Purchase Orders', 'Suppliers'] },
    { label: 'Money', names: ['Accounts', 'Finance', 'Reports'] },
    { label: 'Compliance', names: ['Controlled Register', 'Audit Log'] },
    { label: 'Admin', names: ['Users', 'Menus', 'Settings', 'Branches'] },
];

const groupedMenus = computed(() => {
    const used = new Set();
    const groups = sections.map((section) => {
        const items = menus.value.filter((menu) => section.names.includes(menu.name));
        items.forEach((menu) => used.add(menu.id));
        return { label: section.label, items };
    }).filter((section) => section.items.length);

    const rest = menus.value.filter((menu) => !used.has(menu.id));
    if (rest.length) {
        groups.push({ label: 'Other', items: rest });
    }
    return groups;
});

const isActive = (name) => {
    try {
        return route().current(name) || route().current(`${name}*`);
    } catch (e) {
        return false;
    }
};

const switchBranch = (event) => {
    router.post(route('branch.switch'), { branch_id: event.target.value || null });
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-900/40 md:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-14 shrink-0 items-center gap-2 border-b border-gray-100 px-4">
                <Link :href="route('dashboard')" class="flex items-center gap-2" @click="sidebarOpen = false">
                    <ApplicationLogo class="block h-8 w-auto fill-current text-gray-800" />
                    <span class="text-base font-semibold text-gray-900">{{ $page.props.app.name }}</span>
                </Link>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                <div v-for="section in groupedMenus" :key="section.label">
                    <p class="px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ section.label }}</p>
                    <div class="mt-1 space-y-0.5">
                        <Link
                            v-for="menu in section.items"
                            :key="menu.id"
                            :href="route(menu.route)"
                            class="flex items-center gap-2 rounded-md px-2 py-2 text-sm"
                            :class="isActive(menu.route) ? 'eph-on font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            @click="sidebarOpen = false"
                        >
                            <span v-if="menu.icon" class="w-5 text-center">{{ menu.icon }}</span>
                            <span>{{ menu.name }}</span>
                        </Link>
                    </div>
                </div>
                <p v-if="groupedMenus.length === 0" class="px-2 text-sm text-gray-500">No menus available</p>
            </nav>
        </aside>

        <div class="md:pl-64">
            <nav class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-gray-200 bg-white px-4">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 md:hidden"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="hidden md:block" />

                <div class="flex items-center gap-3">
                    <select
                        v-if="$page.props.branch?.options?.length"
                        class="max-w-[12rem] rounded-md border-gray-300 text-sm"
                        :value="$page.props.branch.current || ''"
                        @change="switchBranch"
                    >
                        <option v-if="$page.props.branch.canSwitch" value="">All branches</option>
                        <option v-for="branch in $page.props.branch.options" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-gray-600 hover:text-gray-900"
                            >
                                {{ $page.props.auth.user.name }}
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
