<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LunaShell from '@/Layouts/LunaShell.vue';

const page = usePage();

const sections = [
    { label: 'Counter', names: ['Dashboard', 'POS', 'Customers'] },
    {
        label: 'Products',
        names: [
            'Medicine List',
            'Medicines',
            'Category',
            'Categories',
            'Manufacturer',
            'Manufacturers',
            'Generic Name',
            'Generics',
            'Brands',
            'Medicine Type',
            'Units',
        ],
    },
    { label: 'Inventory', names: ['Stock', 'Expiry', 'Transfers'] },
    { label: 'Sales', names: ['Invoices', 'Sales Returns', 'Prescriptions'] },
    { label: 'Buying', names: ['Purchases', 'Purchase Orders', 'Suppliers'] },
    { label: 'Money', names: ['Accounts', 'Finance', 'Reports'] },
    { label: 'Compliance', names: ['Controlled Register', 'Audit Log', 'Clinical Rules'] },
    { label: 'HRM', names: ['Employees', 'Departments', 'Payroll'] },
    { label: 'Admin', names: ['Users', 'Menus', 'Settings', 'Theme', 'Branches'] },
];

const normalizeMenuName = (name) => String(name || '').trim().toLowerCase();

const menus = computed(() => {
    const raw = page.props.menus || [];
    return Array.isArray(raw) ? raw : Object.values(raw);
});

const isActive = (name) => {
    try {
        return route().current(name) || route().current(`${name}*`);
    } catch (e) {
        return false;
    }
};

const menuHref = (routeName) => {
    try {
        return route(routeName);
    } catch (e) {
        return '#';
    }
};

const groups = computed(() => {
    const used = new Set();
    const grouped = sections.map((section) => {
        const wanted = section.names.map(normalizeMenuName);
        const items = menus.value
            .filter((menu) => wanted.includes(normalizeMenuName(menu.name)))
            .sort((a, b) => wanted.indexOf(normalizeMenuName(a.name)) - wanted.indexOf(normalizeMenuName(b.name)))
            .map((menu) => ({
                key: menu.id,
                name: menu.name,
                href: menuHref(menu.route),
                active: isActive(menu.route),
            }));
        items.forEach((item) => used.add(item.key));
        return { label: section.label, items };
    }).filter((section) => section.items.length);

    const rest = menus.value
        .filter((menu) => !used.has(menu.id))
        .map((menu) => ({
            key: menu.id,
            name: menu.name,
            href: menuHref(menu.route),
            active: isActive(menu.route),
        }));

    if (rest.length) {
        grouped.push({ label: 'Other', items: rest });
    }

    return grouped;
});

const branchOpen = ref(false);
const currentBranchLabel = computed(() => page.props.branch?.label || 'Branch');
const closeBranch = (event) => {
    if (!event.target.closest('.branch-menu')) {
        branchOpen.value = false;
    }
};
const chooseBranch = (id) => {
    branchOpen.value = false;
    router.post(route('branch.switch'), { branch_id: id || null }, {
        preserveScroll: false,
        preserveState: false,
    });
};
onMounted(() => document.addEventListener('click', closeBranch));
onUnmounted(() => document.removeEventListener('click', closeBranch));
</script>

<template>
    <LunaShell :groups="groups" meta="Pharmacy" :home-href="route('dashboard')">
        <template #tools>
            <div v-if="$page.props.branch?.options?.length" class="dropdown branch-menu">
                <button type="button" class="branch-control" :aria-expanded="branchOpen" @click.stop="branchOpen = !branchOpen">
                    <span>{{ currentBranchLabel }}</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="dropdown-menu" :class="{ show: branchOpen }">
                    <button
                        v-if="$page.props.branch.canSwitch"
                        type="button"
                        class="dropdown-item"
                        :class="{ active: $page.props.branch.sees_all || !$page.props.branch.current }"
                        @click="chooseBranch('')"
                    >All branches</button>
                    <button
                        v-for="branch in $page.props.branch.options"
                        :key="branch.id"
                        type="button"
                        class="dropdown-item"
                        :class="{ active: !$page.props.branch.sees_all && String($page.props.branch.current) === String(branch.id) }"
                        @click="chooseBranch(branch.id)"
                    >{{ branch.name }}</button>
                </div>
            </div>
        </template>
        <template v-if="$slots.header" #header>
            <slot name="header" />
        </template>
        <slot />
    </LunaShell>
</template>
