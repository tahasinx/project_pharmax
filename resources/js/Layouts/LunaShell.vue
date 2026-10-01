<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useLunaApp } from '@/Composables/useLunaApp';

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: String,
        default: 'App',
    },
    homeHref: {
        type: String,
        required: true,
    },
});

useLunaApp();

const page = usePage();

const loadScript = (src) => new Promise((resolve, reject) => {
    if (document.querySelector(`script[data-minia="${src}"]`)) {
        resolve();
        return;
    }
    const script = document.createElement('script');
    script.src = src;
    script.dataset.minia = src;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(src));
    document.body.appendChild(script);
});

const bootMinia = async () => {
    const files = [
        '/minia/assets/libs/jquery/jquery.min.js',
        '/minia/assets/libs/bootstrap/js/bootstrap.bundle.min.js',
        '/minia/assets/libs/metismenu/metisMenu.min.js',
        '/minia/assets/libs/simplebar/simplebar.min.js',
        '/minia/assets/libs/node-waves/waves.min.js',
        '/minia/assets/libs/feather-icons/feather.min.js',
        '/minia/assets/libs/pace-js/pace.min.js',
        '/minia/assets/libs/apexcharts/apexcharts.min.js',
        '/minia/assets/js/app.js',
    ];
    for (const file of files) {
        await loadScript(file);
    }
        if (window.jQuery) {
            window.jQuery('#vertical-menu-btn').off('click');
            window.jQuery(document).off('click.minia', '#vertical-menu-btn').on('click.minia', '#vertical-menu-btn', (event) => {
                event.preventDefault();
                document.body.classList.toggle('sidebar-enable');
                if (window.innerWidth >= 992) {
                    const size = document.body.getAttribute('data-sidebar-size');
                    document.body.setAttribute('data-sidebar-size', !size || size === 'lg' ? 'sm' : 'lg');
                }
            });
        }
    window.feather?.replace();
    window.setTimeout(() => window.feather?.replace(), 80);
};

const mountPartials = async () => {
    await bootMinia();
};
const nav = ref(null);
const headerPanel = ref(null);
const darkMode = ref(false);
const scrollKey = 'epharma-luna-nav-scroll';
const openKey = 'epharma-luna-open-sections';
const openSections = ref({});

const appName = computed(() => page.props.app?.name || 'Epharma');
const appInitial = computed(() => String(appName.value).trim().charAt(0).toUpperCase() || 'E');
const logo = computed(() => page.props.app?.logo || '');
const user = computed(() => page.props.auth?.user);
const email = computed(() => user.value?.email || '');
const initials = computed(() => String(user.value?.name || 'U')
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase());

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
    const active = root.querySelector('.active');
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

const readOpen = () => {
    try {
        return JSON.parse(sessionStorage.getItem(openKey) || '{}');
    } catch (e) {
        return {};
    }
};

const syncOpen = () => {
    const next = {};
    for (const group of groupedMenus.value) {
        next[group.label] = group.items.some((item) => item.active);
    }
    openSections.value = next;
};

const toggleSection = (label) => {
    const opening = openSections.value[label] !== true;
    const next = {};
    for (const group of groupedMenus.value) {
        next[group.label] = opening && group.label === label;
    }
    openSections.value = next;
    sessionStorage.setItem(openKey, JSON.stringify(openSections.value));
    nextTick(() => window.feather?.replace());
};

const sectionIsOpen = (group) => openSections.value[group.label] === true;
const sectionHasActive = (group) => group.items.some((item) => item.active);

const singleNames = ['Dashboard', 'POS'];
const flatItems = computed(() => props.groups.flatMap((group) => group.items));
const singleItems = computed(() => singleNames
    .map((name) => flatItems.value.find((item) => item.name === name))
    .filter(Boolean)
    .map((item) => ({
        ...item,
        icon: item.name === 'POS' ? 'shopping-bag' : 'home',
    })));
const groupedMenus = computed(() => props.groups
    .map((group) => ({
        ...group,
        items: group.items.filter((item) => !singleNames.includes(item.name)),
    }))
    .filter((group) => group.items.length));

const sectionIcon = (label) => ({
    Catalog: 'package',
    Inventory: 'archive',
    Sales: 'shopping-cart',
    Buying: 'truck',
    Money: 'dollar-sign',
    Compliance: 'shield',
    Admin: 'settings',
    Other: 'more-horizontal',
    Operate: 'grid',
    Billing: 'credit-card',
    System: 'database',
}[label] || 'circle');

const itemIcon = (name) => ({
    'Medicine List': 'bi-capsule',
    'Generic Name': 'bi-prescription2',
    'Medicine Type': 'bi-bookmark',
    Category: 'bi-tags',
    Manufacturer: 'bi-buildings',
    Units: 'bi-rulers',
    Stock: 'bi-box-seam',
    Expiry: 'bi-calendar-x',
    Transfers: 'bi-arrow-left-right',
    Invoices: 'bi-receipt',
    'Sales Returns': 'bi-arrow-return-left',
    Customers: 'bi-people',
    Prescriptions: 'bi-file-medical',
    Purchases: 'bi-bag',
    'Purchase Orders': 'bi-clipboard-check',
    Suppliers: 'bi-truck',
    Accounts: 'bi-wallet2',
    Finance: 'bi-graph-up-arrow',
    Reports: 'bi-bar-chart',
    'Controlled Register': 'bi-shield-lock',
    'Audit Log': 'bi-journal-text',
    Users: 'bi-person',
    Menus: 'bi-list-ul',
    Settings: 'bi-gear',
    Branches: 'bi-diagram-3',
    Overview: 'bi-speedometer2',
    Pharmacies: 'bi-shop',
    Plans: 'bi-layers',
    Subscriptions: 'bi-credit-card',
    Billing: 'bi-cash-coin',
    Schema: 'bi-database',
    Backups: 'bi-cloud-arrow-up',
    Commands: 'bi-terminal',
    Deploy: 'bi-rocket-takeoff',
    POS: 'bi-bag-check',
    Dashboard: 'bi-house',
}[name] || 'bi-dot');

const hasRoute = (name) => {
    try {
        return route().has(name);
    } catch (e) {
        return false;
    }
};

const settingsHref = computed(() => {
    if (hasRoute('settings.index')) {
        return route('settings.index');
    }
    if (hasRoute('platform.settings')) {
        return route('platform.settings');
    }
    return '';
});

const alertLinks = computed(() => {
    const links = [];
    if (hasRoute('stocks.alerts')) {
        links.push({ name: 'Stock alerts', href: route('stocks.alerts'), icon: 'bi-exclamation-triangle' });
    }
    if (hasRoute('stocks.expiry')) {
        links.push({ name: 'Expiring stock', href: route('stocks.expiry'), icon: 'bi-calendar-x' });
    }
    const audit = flatItems.value.find((item) => item.name === 'Audit Log');
    if (audit) {
        links.push({ name: audit.name, href: audit.href, icon: 'bi-journal-text' });
    }
    return links;
});

const shortcutGroups = computed(() => [
    { label: 'Open', items: singleItems.value },
    ...groupedMenus.value,
].filter((group) => group.items.length));

const toggleNav = (event) => {
    event.preventDefault();
    document.body.classList.toggle('sidebar-enable');
    if (window.innerWidth >= 992) {
        const size = document.body.getAttribute('data-sidebar-size');
        document.body.setAttribute('data-sidebar-size', size === 'sm' ? 'lg' : 'sm');
    }
};

const closeMobileNav = () => {
    if (window.innerWidth < 768) {
        document.body.classList.remove('sidebar-enable');
    }
};

const closeHeader = (event) => {
    if (!event.target.closest('.profile-menu, .apps-menu, .alerts-menu')) {
        headerPanel.value = null;
    }
};

const togglePanel = (name) => {
    headerPanel.value = headerPanel.value === name ? null : name;
};

const toggleTheme = () => {
    darkMode.value = !darkMode.value;
    const mode = darkMode.value ? 'dark' : 'light';
    document.body.setAttribute('data-bs-theme', mode);
    document.body.setAttribute('data-layout-mode', mode);
    document.body.setAttribute('data-topbar', mode);
    document.body.setAttribute('data-sidebar', mode);
    nextTick(() => window.feather?.replace());
};

const logout = () => {
    headerPanel.value = null;
    router.post(route('logout'));
};

onMounted(() => {
    syncOpen();
    darkMode.value = document.body.getAttribute('data-bs-theme') === 'dark';
    document.addEventListener('click', closeHeader);
    mountPartials();
});
onUnmounted(() => {
    document.removeEventListener('click', closeHeader);
    document.body.classList.remove('sidebar-enable');
    document.body.removeAttribute('data-sidebar-size');
});
watch(() => page.url, () => {
    closeMobileNav();
    headerPanel.value = null;
    syncOpen();
    placeActive();
    nextTick(() => window.feather?.replace());
});
</script>

<template>
    <div id="layout-wrapper">
        <div ref="topbar"></div>
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <div class="navbar-brand-box">
                        <Link :href="homeHref" class="logo logo-dark">
                            <span class="logo-sm">
                                <span class="logo-mark">
                                    <img v-if="logo" :src="logo" :alt="appName">
                                    <span v-else>{{ appInitial }}</span>
                                </span>
                            </span>
                            <span class="logo-lg">
                                <span class="logo-mark">
                                    <img v-if="logo" :src="logo" :alt="appName">
                                    <span v-else>{{ appInitial }}</span>
                                </span>
                                <span class="logo-txt">{{ appName }}</span>
                            </span>
                        </Link>
                    </div>
                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn" aria-label="Toggle navigation">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                    <div class="d-none d-lg-flex align-items-center ms-2">
                        <slot name="tools" />
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn header-item header-icon d-none d-sm-inline-flex" aria-label="Color mode" @click="toggleTheme">
                        <i :data-feather="darkMode ? 'sun' : 'moon'" class="icon-lg"></i>
                    </button>
                    <div class="dropdown d-none d-lg-inline-block apps-menu">
                        <button type="button" class="btn header-item header-icon" aria-label="Shortcuts" :aria-expanded="headerPanel === 'apps'" @click.stop="togglePanel('apps')">
                            <i data-feather="grid" class="icon-lg"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shortcuts-menu" :class="{ show: headerPanel === 'apps' }">
                            <div v-for="group in shortcutGroups" :key="group.label" class="shortcut-section">
                                <div class="shortcut-title">{{ group.label }}</div>
                                <div class="shortcut-grid">
                                    <Link v-for="item in group.items" :key="item.key || item.href" :href="item.href" class="dropdown-item" @click="headerPanel = null">
                                        <i class="bi" :class="itemIcon(item.name)"></i>
                                        <span>{{ item.name }}</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown d-inline-block alerts-menu">
                        <button type="button" class="btn header-item header-icon noti-icon position-relative" aria-label="Alerts" :aria-expanded="headerPanel === 'alerts'" @click.stop="togglePanel('alerts')">
                            <i data-feather="bell" class="icon-lg"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end alerts-list" :class="{ show: headerPanel === 'alerts' }">
                            <div class="shortcut-title">Alerts</div>
                            <Link v-for="item in alertLinks" :key="item.href" :href="item.href" class="dropdown-item" @click="headerPanel = null">
                                <i class="bi" :class="item.icon"></i>
                                <span>{{ item.name }}</span>
                            </Link>
                            <div v-if="!alertLinks.length" class="profile-email">Nothing waiting</div>
                        </div>
                    </div>
                    <button v-if="settingsHref" type="button" class="btn header-item header-icon d-none d-sm-inline-flex" aria-label="Settings" @click="router.visit(settingsHref)">
                        <i data-feather="settings" class="icon-lg"></i>
                    </button>
                    <div class="dropdown d-inline-block profile-menu">
                        <button type="button" class="btn header-item bg-soft-light border-start border-end" :aria-expanded="headerPanel === 'profile'" @click.stop="togglePanel('profile')">
                            <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="rounded-circle header-profile-user">
                            <span v-else class="rounded-circle header-profile-user d-inline-flex align-items-center justify-content-center bg-primary text-white fw-medium">{{ initials }}</span>
                            <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ user?.name }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" :class="{ show: headerPanel === 'profile' }">
                            <div class="profile-email">{{ email }}</div>
                            <Link class="dropdown-item" :href="route('profile.edit')" @click="headerPanel = null">
                                <i class="mdi mdi-face-profile font-size-16 align-middle me-1"></i> Profile
                            </Link>
                            <div class="dropdown-divider"></div>
                            <button type="button" class="dropdown-item" @click="logout">
                                <i class="mdi mdi-logout font-size-16 align-middle me-1"></i> Log out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="vertical-menu">
            <div data-simplebar class="h-100" ref="nav" @scroll.passive="rememberScroll">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Menu</li>
                        <li v-for="item in singleItems" :key="item.key || item.name" :class="{ 'mm-active': item.active }">
                            <Link :href="item.href" :class="{ active: item.active }" @click="closeMobileNav">
                                <i :data-feather="item.icon"></i>
                                <span>{{ item.name }}</span>
                            </Link>
                        </li>
                        <li v-for="group in groupedMenus" :key="group.label" :class="{ 'mm-active': sectionHasActive(group) }">
                            <a href="javascript:void(0);" class="has-arrow" :aria-expanded="sectionIsOpen(group) ? 'true' : 'false'" @click.prevent="toggleSection(group.label)">
                                <i :data-feather="sectionIcon(group.label)"></i>
                                <span>{{ group.label }}</span>
                            </a>
                            <ul class="sub-menu mm-collapse sub-section" :class="{ 'mm-show': sectionIsOpen(group) }" :aria-expanded="sectionIsOpen(group) ? 'true' : 'false'">
                                <li v-for="item in group.items" :key="item.key || item.href" :class="{ 'mm-active': item.active }">
                                    <Link :href="item.href" :class="{ active: item.active }" @click="closeMobileNav">
                                        <i class="bi" :class="itemIcon(item.name)"></i>
                                        <span>{{ item.name }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div v-if="$slots.header" class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <slot name="header" />
                            </div>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">{{ new Date().getFullYear() }} © {{ appName }}</div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">{{ meta }}</div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
