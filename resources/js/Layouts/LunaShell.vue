<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useLunaApp } from '@/Composables/useLunaApp';
import { useTheme } from '@/Composables/useTheme';

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
const sidebarKey = 'epharma-sidebar-size';
const sidebarCollapsed = ref(false);

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
                    const nextSize = !size || size === 'lg' ? 'sm' : 'lg';
                    document.body.setAttribute('data-sidebar-size', nextSize);
                    localStorage.setItem(sidebarKey, nextSize);
                    sidebarCollapsed.value = nextSize === 'sm';
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
const scrollKey = 'epharma-luna-nav-scroll';
const openKey = 'epharma-luna-open-sections';
const openSections = ref({});

const appName = computed(() => page.props.app?.name || 'Epharma');
const appInitial = computed(() => String(appName.value).trim().charAt(0).toUpperCase() || 'E');
const logo = computed(() => page.props.app?.logo || '');
const footerMeta = computed(() => {
    const tagline = page.props.platform?.identity?.tagline;
    if (tagline) {
        return tagline;
    }
    return props.meta;
});
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
    const saved = readOpen();
    const next = {};
    let activeLabel = null;
    for (const group of groupedMenus.value) {
        if (group.items.some((item) => item.active)) {
            activeLabel = group.label;
            break;
        }
    }
    for (const group of groupedMenus.value) {
        next[group.label] = activeLabel
            ? group.label === activeLabel
            : saved[group.label] === true;
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

const singleNames = ['Dashboard', 'POS', 'Customers'];
const singleIcon = (name) => ({
    Dashboard: 'bi-speedometer2',
    POS: 'bi-bag-check',
    Customers: 'bi-people',
}[name] || 'bi-dot');
const flatItems = computed(() => props.groups.flatMap((group) => group.items));
const singleItems = computed(() => singleNames
    .map((name) => flatItems.value.find((item) => item.name === name))
    .filter(Boolean)
    .map((item) => ({
        ...item,
        icon: singleIcon(item.name),
    })));
const groupedMenus = computed(() => props.groups
    .map((group) => ({
        ...group,
        items: group.items.filter((item) => !singleNames.includes(item.name)),
    }))
    .filter((group) => group.items.length));

const sectionIcon = (label) => ({
    Products: 'bi-grid-3x3-gap',
    Catalog: 'bi-grid-3x3-gap',
    Inventory: 'bi-box-seam',
    Sales: 'bi-cart3',
    Buying: 'bi-basket',
    Money: 'bi-cash-coin',
    Compliance: 'bi-shield-check',
    HRM: 'bi-people',
    Admin: 'bi-gear',
    Other: 'bi-three-dots',
    Operate: 'bi-grid',
    Billing: 'bi-credit-card',
    System: 'bi-database',
}[label] || 'bi-circle');

const itemIcon = (name) => ({
    'Medicine List': 'bi-capsule',
    Medicines: 'bi-capsule',
    'Generic Name': 'bi-prescription2',
    Generics: 'bi-prescription2',
    Brands: 'bi-badge-tm',
    'Medicine Type': 'bi-bookmark',
    Category: 'bi-tags',
    Categories: 'bi-tags',
    Manufacturer: 'bi-buildings',
    Manufacturers: 'bi-buildings',
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
    'Clinical Rules': 'bi-clipboard2-pulse',
    Employees: 'bi-person-badge',
    Departments: 'bi-building',
    Payroll: 'bi-cash-stack',
    Users: 'bi-person',
    Menus: 'bi-list-ul',
    Settings: 'bi-gear',
    Theme: 'bi-palette',
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
        const nextSize = size === 'sm' ? 'lg' : 'sm';
        document.body.setAttribute('data-sidebar-size', nextSize);
        localStorage.setItem(sidebarKey, nextSize);
        sidebarCollapsed.value = nextSize === 'sm';
    }
};

const closeMobileNav = () => {
    if (window.innerWidth < 768) {
        document.body.classList.remove('sidebar-enable');
    }
};

const closeHeader = (event) => {
    if (!event.target.closest('.profile-menu, .apps-menu, .alerts-menu, .theme-menu')) {
        headerPanel.value = null;
    }
};

const togglePanel = (name) => {
    headerPanel.value = headerPanel.value === name ? null : name;
};

const { preference: theme, appearance, mode, palette, setPreference, syncSystem } = useTheme();
const themeQuery = window.matchMedia('(prefers-color-scheme: dark)');

const themeIcon = computed(() => ({
    light: 'bi-sun',
    dark: 'bi-moon',
    night: 'bi-moon-stars',
    system: 'bi-laptop',
}[theme.value] || 'bi-laptop'));

const applyTheme = () => {
    const next = appearance.value;
    const colorMode = mode.value;
    document.documentElement.setAttribute('data-bs-theme', colorMode);
    document.documentElement.setAttribute('data-theme', next);
    document.documentElement.style.colorScheme = colorMode;
    document.body.setAttribute('data-bs-theme', colorMode);
    document.body.setAttribute('data-theme', next);
    document.body.setAttribute('data-layout-mode', colorMode);
    document.body.setAttribute('data-topbar', colorMode);
    document.body.setAttribute('data-sidebar', colorMode);
    document.body.style.setProperty('--shell-card-bg', palette.value.card);
    document.body.style.setProperty('--shell-text', palette.value.text);
    document.body.style.setProperty('--shell-muted', palette.value.muted);
    document.body.style.setProperty('--shell-border', palette.value.grid);
};

const setTheme = (value) => {
    setPreference(value);
    headerPanel.value = null;
    applyTheme();
};

const onSystemTheme = () => {
    syncSystem();
    if (theme.value === 'system') {
        applyTheme();
    }
};

applyTheme();

const logout = () => {
    headerPanel.value = null;
    router.post(route('logout'));
};

onMounted(() => {
    syncOpen();
    applyTheme();
    if (window.innerWidth >= 992) {
        document.body.setAttribute('data-sidebar-size', localStorage.getItem(sidebarKey) === 'sm' ? 'sm' : 'lg');
        sidebarCollapsed.value = localStorage.getItem(sidebarKey) === 'sm';
    }
    themeQuery.addEventListener('change', onSystemTheme);
    document.addEventListener('click', closeHeader);
    mountPartials();
});
onUnmounted(() => {
    themeQuery.removeEventListener('change', onSystemTheme);
    document.removeEventListener('click', closeHeader);
    document.body.classList.remove('sidebar-enable');
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
    <div id="layout-wrapper" :data-layout-mode="mode" :data-theme="appearance">
        <div ref="topbar"></div>
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center nav-rail">
                    <div class="navbar-brand-box">
                        <Link :href="homeHref" class="logo shell-logo">
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
                        <i class="fa fa-fw" :class="sidebarCollapsed ? 'fa-indent' : 'fa-outdent'"></i>
                    </button>
                    </div>
                    <div class="d-none d-lg-flex align-items-center ms-2">
                        <slot name="tools" />
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown d-none d-sm-inline-block theme-menu">
                        <button type="button" class="btn header-item header-icon" aria-label="Color mode" :aria-expanded="headerPanel === 'theme'" @click.stop="togglePanel('theme')">
                            <i class="bi" :class="themeIcon"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end theme-list" :class="{ show: headerPanel === 'theme' }">
                            <button type="button" class="theme-option" :class="{ 'is-current': theme === 'light' }" @click="setTheme('light')">
                                <i class="bi bi-sun"></i>
                                <span>Light</span>
                                <i v-if="theme === 'light'" class="bi bi-check-lg ms-auto"></i>
                            </button>
                            <button type="button" class="theme-option" :class="{ 'is-current': theme === 'dark' }" @click="setTheme('dark')">
                                <i class="bi bi-moon"></i>
                                <span>Dark</span>
                                <i v-if="theme === 'dark'" class="bi bi-check-lg ms-auto"></i>
                            </button>
                            <button type="button" class="theme-option" :class="{ 'is-current': theme === 'night' }" @click="setTheme('night')">
                                <i class="bi bi-moon-stars"></i>
                                <span>Night</span>
                                <i v-if="theme === 'night'" class="bi bi-check-lg ms-auto"></i>
                            </button>
                            <button type="button" class="theme-option" :class="{ 'is-current': theme === 'system' }" @click="setTheme('system')">
                                <i class="bi bi-laptop"></i>
                                <span>System</span>
                                <i v-if="theme === 'system'" class="bi bi-check-lg ms-auto"></i>
                            </button>
                        </div>
                    </div>
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
                    <div class="dropdown d-inline-block profile-menu nav-rail">
                        <button type="button" class="btn header-item" :aria-expanded="headerPanel === 'profile'" @click.stop="togglePanel('profile')">
                            <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="rounded-circle header-profile-user">
                            <span v-else class="rounded-circle header-profile-user d-inline-flex align-items-center justify-content-center bg-primary text-white fw-medium">{{ initials }}</span>
                            <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ user?.name }}</span>
                            <i class="bi bi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" :class="{ show: headerPanel === 'profile' }">
                            <div class="profile-email">{{ email }}</div>
                            <Link class="dropdown-item" :href="route('profile.edit')" @click="headerPanel = null">
                                <i class="bi bi-person"></i> Profile
                            </Link>
                            <div class="dropdown-divider"></div>
                            <button type="button" class="dropdown-item" @click="logout">
                                <i class="bi bi-box-arrow-right"></i> Log out
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
                        <li v-for="item in singleItems" :key="item.key || item.name" :class="{ 'mm-active': item.active }">
                            <Link :href="item.href" :class="{ active: item.active }" @click="closeMobileNav">
                                <i class="bi" :class="item.icon"></i>
                                <span>{{ item.name }}</span>
                            </Link>
                        </li>
                        <li v-for="group in groupedMenus" :key="group.label" :class="{ 'mm-active': sectionHasActive(group) }">
                            <a href="javascript:void(0);" class="has-arrow" :aria-expanded="sectionIsOpen(group) ? 'true' : 'false'" @click.prevent="toggleSection(group.label)">
                                <i class="bi" :class="sectionIcon(group.label)"></i>
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
                            <div class="text-sm-end d-none d-sm-block">{{ footerMeta }}</div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
