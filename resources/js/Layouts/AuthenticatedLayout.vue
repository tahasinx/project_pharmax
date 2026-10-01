<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const logo = computed(() => page.props.app?.logo || '');
const sidebarOpen = ref(false);
const nav = ref(null);
const scrollKey = 'epharma-sidebar-scroll';

const rememberScroll = () => {
    if (nav.value) {
        sessionStorage.setItem(scrollKey, String(nav.value.scrollTop));
    }
};

const placeActive = async () => {
    await nextTick();
    const root = nav.value;
    if (!root) {
        return;
    }
    const saved = Number(sessionStorage.getItem(scrollKey) || 0);
    if (saved > 0) {
        root.scrollTop = saved;
    }
    const active = root.querySelector('.is-active');
    if (!active) {
        return;
    }
    const navBox = root.getBoundingClientRect();
    const itemBox = active.getBoundingClientRect();
    if (itemBox.top < navBox.top + 8 || itemBox.bottom > navBox.bottom - 8) {
        active.scrollIntoView({ block: 'nearest' });
        rememberScroll();
    }
};
const initials = computed(() => String(page.props.auth?.user?.name || 'U')
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase());

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

const iconFor = (name) => ({
    Dashboard: 'bi-speedometer2',
    POS: 'bi-bag-check',
    Medicines: 'bi-capsule',
    Generics: 'bi-diagram-3',
    Brands: 'bi-bookmark',
    Categories: 'bi-grid',
    Manufacturers: 'bi-building',
    Stock: 'bi-box-seam',
    Expiry: 'bi-calendar2-x',
    Transfers: 'bi-arrow-left-right',
    Invoices: 'bi-receipt',
    'Sales Returns': 'bi-arrow-return-left',
    Customers: 'bi-people',
    Prescriptions: 'bi-prescription2',
    Purchases: 'bi-cart-check',
    'Purchase Orders': 'bi-clipboard-check',
    Suppliers: 'bi-truck',
    Accounts: 'bi-wallet2',
    Finance: 'bi-cash-stack',
    Reports: 'bi-bar-chart-line',
    'Controlled Register': 'bi-shield-check',
    'Audit Log': 'bi-journal-text',
    Users: 'bi-person-gear',
    Menus: 'bi-list-nested',
    Settings: 'bi-sliders',
    Branches: 'bi-diagram-2',
})[name] || 'bi-circle';

const activeMenu = computed(() => menus.value.find((menu) => isActive(menu.route)));
const activeSection = computed(() => groupedMenus.value.find((section) => section.items.includes(activeMenu.value))?.label || 'Workspace');

onMounted(placeActive);
watch(() => page.url, placeActive);

const switchBranch = (event) => {
    router.post(route('branch.switch'), { branch_id: event.target.value || null });
};
</script>

<template>
    <div class="admin-shell min-h-screen">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-gray-900/40 md:hidden"
            @click="sidebarOpen = false"
        />

            <aside
            class="admin-sidebar fixed inset-y-0 left-0 z-40 flex w-64 flex-col transition-transform md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="admin-brand flex h-16 shrink-0 items-center px-4">
                <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3" @click="sidebarOpen = false">
                    <span class="admin-brand__mark grid h-9 w-9 shrink-0 place-items-center rounded-lg overflow-hidden">
                        <img v-if="logo" :src="logo" alt="" class="h-7 w-7 object-contain">
                        <ApplicationLogo v-else class="block h-5 w-auto fill-current text-white" />
                    </span>
                    <span class="min-w-0">
                        <span class="admin-brand__name block truncate text-sm font-semibold tracking-tight">{{ $page.props.app.name }}</span>
                        <span class="admin-brand__meta block text-[11px]">Pharmacy</span>
                    </span>
                </Link>
            </div>

            <nav ref="nav" class="admin-navigation flex-1 space-y-5 overflow-y-auto px-3 py-3" @scroll.passive="rememberScroll">
                <div v-for="section in groupedMenus" :key="section.label">
                    <p class="admin-section-label px-3 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em]">{{ section.label }}</p>
                    <div class="space-y-0.5">
                        <Link
                            v-for="menu in section.items"
                            :key="menu.id"
                            :href="route(menu.route)"
                            class="admin-nav-link flex h-9 items-center gap-2.5 px-3 text-[13px]"
                            :class="isActive(menu.route) ? 'is-active' : ''"
                            @click="sidebarOpen = false"
                        >
                            <i class="bi w-4 shrink-0 text-center text-[15px] leading-none" :class="iconFor(menu.name)" aria-hidden="true" />
                            <span class="truncate">{{ menu.name }}</span>
                        </Link>
                    </div>
                </div>
                <p v-if="groupedMenus.length === 0" class="px-3 text-sm text-white/50">No menus available</p>
            </nav>
        </aside>

        <div class="md:pl-64">
            <nav class="admin-topbar sticky top-0 z-20 flex h-16 items-center justify-between border-b px-4 sm:px-7">
                <button
                    type="button"
                    class="admin-icon-button inline-flex h-9 w-9 items-center justify-center rounded-lg md:hidden"
                    @click="sidebarOpen = true"
                    aria-label="Open navigation"
                >
                    <svg class="h-[18px] w-[18px]" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="hidden min-w-0 items-center gap-2 text-[13px] md:flex">
                    <span class="text-slate-400">Workspace</span>
                    <i class="bi bi-chevron-right text-[9px] text-slate-300" aria-hidden="true" />
                    <span class="text-slate-500">{{ activeSection }}</span>
                    <template v-if="activeMenu">
                        <i class="bi bi-chevron-right text-[9px] text-slate-300" aria-hidden="true" />
                        <span class="font-semibold text-slate-800">{{ activeMenu.name }}</span>
                    </template>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <select
                        v-if="$page.props.branch?.options?.length"
                        class="admin-branch-select max-w-[12rem] rounded-lg border-slate-200 text-[13px]"
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
                                class="admin-user-trigger inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-left"
                            >
                                <span class="admin-avatar grid h-8 w-8 shrink-0 place-items-center rounded-full text-[11px] font-semibold">{{ initials }}</span>
                                <span class="hidden min-w-0 sm:block">
                                    <span class="block max-w-36 truncate text-[12px] font-semibold text-slate-800">{{ $page.props.auth.user.name }}</span>
                                    <span class="mt-0.5 block text-[10px] text-slate-400">Account</span>
                                </span>
                                <svg class="ms-0.5 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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

            <header v-if="$slots.header" class="admin-page-header">
                <div class="mx-auto max-w-[88rem] px-4 py-5 sm:px-7 lg:px-9">
                    <slot name="header" />
                </div>
            </header>

            <main class="admin-main min-h-[calc(100vh-4rem)]">
                <slot />
            </main>
        </div>
    </div>
</template>
