<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const brand = computed(() => page.props.platform?.theme || {
    primary: '#17342b',
    font_family: 'Inter',
    font_href: '',
    font_size: 16,
    font_weight: 400,
    radius: '10px',
});
const logo = computed(() => page.props.app?.logo || '');
const errors = computed(() => page.props.errors || {});
const user = computed(() => page.props.auth?.user);
const path = computed(() => (page.url || '').split('?')[0]);

const groups = computed(() => [
    {
        label: 'Operate',
        items: [
            ['Overview', '/platform', 'grid'],
            ['Pharmacies', '/platform/companies', 'store'],
        ],
    },
    {
        label: 'Billing',
        items: [
            ['Plans', '/platform/plans', 'layers'],
            ['Subscriptions', '/platform/subscriptions', 'repeat'],
            ['Invoices', '/platform/invoices', 'file'],
            ['Billing', '/platform/billing', 'card'],
        ],
    },
    {
        label: 'System',
        items: [
            ['Schema', '/platform/schema', 'db'],
            ['Backups', '/platform/backups', 'archive'],
            ['Commands', '/platform/commands', 'terminal'],
            ...(page.props.platform?.deploy ? [['Deploy', '/platform/deploy', 'upload']] : []),
            ['Settings', '/platform/settings', 'sliders'],
        ],
    },
]);

const links = computed(() => groups.value.flatMap((group) => group.items));
const isActive = (href) => (href === '/platform' ? path.value === '/platform' : path.value === href || path.value.startsWith(`${href}/`));
const current = computed(() => links.value.find(([, href]) => isActive(href))?.[0] || 'Platform');
const initials = computed(() => String(user.value?.name || 'A').split(' ').slice(0, 2).map((part) => part[0]).join('').toUpperCase());
const theme = ref(localStorage.getItem('epharma-theme') || 'auto');
const menu = ref(null);
const openMenu = ref(false);
const openTheme = ref(false);

const resolvedTheme = () => {
    if (theme.value === 'dark') return 'dark';
    if (theme.value === 'light') return 'light';
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};
const appearance = ref('light');

const applyTheme = () => {
    appearance.value = resolvedTheme();
    document.documentElement.classList.toggle('pf-dark', appearance.value === 'dark');
};

const chooseTheme = (value) => {
    theme.value = value;
    localStorage.setItem('epharma-theme', value);
    applyTheme();
    openTheme.value = false;
};

const onDocumentClick = (event) => {
    if (menu.value && !menu.value.contains(event.target)) {
        openMenu.value = false;
        openTheme.value = false;
    }
};

onMounted(() => {
    applyTheme();
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
    document.addEventListener('click', onDocumentClick);
});
onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});
watch(theme, applyTheme);
</script>

<template>
    <div
        class="pf admin-shell min-h-screen"
        :class="{ 'is-dark': appearance === 'dark' }"
        :style="{
            '--pf-accent': brand.primary,
            '--pf-font': `'${brand.font_family}', sans-serif`,
            '--pf-size': `${brand.font_size}px`,
            '--pf-weight': brand.font_weight,
            '--pf-radius': brand.radius,
        }"
    >
        <link v-if="brand.font_href" rel="stylesheet" :href="brand.font_href">
        <div class="flex min-h-screen">
            <aside class="admin-sidebar sticky top-0 hidden h-screen w-64 shrink-0 flex-col md:flex">
                <div class="admin-brand flex h-16 items-center gap-3 px-4">
                    <span class="admin-brand__mark grid h-9 w-9 place-items-center overflow-hidden rounded-lg">
                        <img v-if="logo" :src="logo" alt="" class="h-7 w-7 object-contain">
                        <span v-else class="text-sm font-semibold text-white">E</span>
                    </span>
                    <span>
                        <span class="admin-brand__name block text-sm font-semibold leading-4">{{ page.props.app?.name || 'Epharma' }}</span>
                        <span class="admin-brand__meta block text-[11px] leading-4">Platform</span>
                    </span>
                </div>
                <nav class="admin-navigation flex-1 space-y-5 overflow-y-auto px-3 py-3">
                    <div v-for="group in groups" :key="group.label">
                        <p class="admin-section-label px-3 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em]">{{ group.label }}</p>
                        <div class="space-y-0.5">
                            <Link
                                v-for="[label, href, icon] in group.items"
                                :key="href"
                                :href="href"
                                class="admin-nav-link flex h-9 items-center gap-2.5 px-3 text-[13px]"
                                :class="isActive(href) ? 'is-active' : ''"
                            >
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <template v-if="icon === 'grid'"><rect x="4" y="4" width="6.5" height="6.5" rx="1.2" /><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.2" /><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.2" /><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.2" /></template>
                                    <template v-else-if="icon === 'store'"><path d="M4 10h16v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9z" /><path d="M3 10l1.6-5h14.8L21 10" /><path d="M9 20v-5h6v5" /></template>
                                    <template v-else-if="icon === 'layers'"><path d="M12 4l8 4-8 4-8-4 8-4z" /><path d="M4 12l8 4 8-4" /><path d="M4 16l8 4 8-4" /></template>
                                    <template v-else-if="icon === 'repeat'"><path d="M7 7h10l-2-2" /><path d="M17 7v4" /><path d="M17 17H7l2 2" /><path d="M7 17v-4" /></template>
                                    <template v-else-if="icon === 'file'"><path d="M7 3h7l5 5v13H7z" /><path d="M14 3v5h5" /><path d="M9 13h6M9 17h6" /></template>
                                    <template v-else-if="icon === 'card'"><rect x="3" y="6" width="18" height="12" rx="2" /><path d="M3 10h18" /></template>
                                    <template v-else-if="icon === 'db'"><ellipse cx="12" cy="7" rx="7" ry="3" /><path d="M5 7v10c0 1.7 3.1 3 7 3s7-1.3 7-3V7" /><path d="M5 12c0 1.7 3.1 3 7 3s7-1.3 7-3" /></template>
                                    <template v-else-if="icon === 'archive'"><rect x="3" y="4" width="18" height="5" rx="1" /><path d="M5 9v10h14V9" /><path d="M10 13h4" /></template>
                                    <template v-else-if="icon === 'terminal'"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M7 9l3 3-3 3M12 15h5" /></template>
                                    <template v-else-if="icon === 'upload'"><path d="M12 16V6" /><path d="M8 9l4-4 4 4" /><path d="M5 19h14" /></template>
                                    <template v-else><path d="M4 8h10M4 12h16M4 16h8" /></template>
                                </svg>
                                {{ label }}
                            </Link>
                        </div>
                    </div>
                </nav>
                <div class="border-t border-white/10 p-3">
                    <div class="flex items-center gap-2.5 px-2 py-2">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="h-8 w-8 shrink-0 rounded-full object-cover">
                        <span v-else class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/10 text-[11px] font-semibold text-white">{{ initials }}</span>
                            <Link href="/profile" class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-medium text-[#f7f3ec]">{{ user?.name || 'Admin' }}</span>
                                <span class="text-[12px] text-[#9aa297]">Profile</span>
                            </Link>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="admin-topbar sticky top-0 z-20 flex h-14 items-center justify-between px-4 sm:px-6">
                    <p class="text-sm font-semibold tracking-tight">{{ current }}</p>
                    <div ref="menu" class="flex items-center gap-1">
                        <div class="relative">
                            <button type="button" class="grid h-9 w-9 place-items-center rounded-md text-[#3f3f46] hover:bg-[#f4f4f5]" aria-label="Color theme" title="Color theme" @click="openTheme = !openTheme; openMenu = false">
                                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                    <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16" />
                                </svg>
                            </button>
                            <div v-if="openTheme" class="pf-menu absolute right-0 z-30 mt-1 w-36 rounded-lg border py-1 text-sm shadow-lg">
                                <button v-for="option in [['light', 'Light'], ['dark', 'Dark'], ['auto', 'Auto']]" :key="option[0]" type="button" class="flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-[#f4f4f5]" @click="chooseTheme(option[0])">
                                    <span>{{ option[1] }}</span>
                                    <span v-if="theme === option[0]" class="text-[#17342b]">✓</span>
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <button type="button" class="flex h-9 items-center gap-2 rounded-md px-1.5 hover:bg-[#f4f4f5]" aria-label="Account" @click="openMenu = !openMenu; openTheme = false">
                                <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="h-7 w-7 rounded-full object-cover">
                                <span v-else class="grid h-7 w-7 place-items-center rounded-full bg-[#17342b] text-[10px] font-semibold text-white">{{ initials }}</span>
                            </button>
                            <div v-if="openMenu" class="pf-menu absolute right-0 z-30 mt-1 w-52 rounded-lg border py-1 text-sm shadow-lg">
                                <p class="truncate px-3 py-2 text-[12px] text-[#71717a]">{{ user?.email }}</p>
                                <Link :href="route('profile.edit')" class="block px-3 py-2 hover:bg-[#f4f4f5]" @click="openMenu = false">Profile</Link>
                                <Link href="/logout" method="post" as="button" class="block w-full px-3 py-2 text-left text-[#b91c1c] hover:bg-[#f4f4f5]">Sign out</Link>
                            </div>
                        </div>
                    </div>
                </header>
                <nav class="flex gap-1.5 overflow-x-auto border-b border-[#e4e4e7] bg-white px-3 py-2 md:hidden">
                    <Link
                        v-for="[label, href] in links"
                        :key="href"
                        :href="href"
                        class="shrink-0 rounded-md px-2.5 py-1 text-[13px]"
                        :class="isActive(href) ? 'bg-[#17342b] text-white' : 'bg-[#f4f4f5] text-[#3f3f46]'"
                    >
                        {{ label }}
                    </Link>
                </nav>
                <main class="min-w-0 flex-1 px-4 py-5 sm:px-6">
                    <p v-for="(message, key) in errors" :key="key" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ message }}</p>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pf {
    font-family: var(--pf-font);
    font-weight: var(--pf-weight);
}
.pf :deep(.pf-accent) {
    background: var(--pf-accent);
    color: #fff;
}
.pf :deep(h1) {
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: #18181b;
}
.pf :deep(input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):not([type="range"])),
.pf :deep(select),
.pf :deep(textarea) {
    border: 1px solid #e4e4e7;
    border-radius: var(--pf-radius, 0.5rem);
    background: #fff;
    min-height: 2.25rem;
    padding: 0.4rem 0.7rem;
    font-size: 0.875rem;
    color: #18181b;
}
.pf :deep(input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]):not([type="range"]):focus),
.pf :deep(select:focus),
.pf :deep(textarea:focus) {
    outline: 2px solid var(--pf-accent);
    outline-offset: 1px;
}
.pf :deep(table) {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    border: 1px solid #e4e4e7;
    border-radius: var(--pf-radius, 0.75rem);
    background: #fff;
    font-size: 0.875rem;
}
.pf :deep(th),
.pf :deep(td) {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #f4f4f5;
    vertical-align: middle;
}
.pf :deep(tr:last-child td) {
    border-bottom: 0;
}
.pf :deep(th) {
    background: #fafafa;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #71717a;
}
.pf-menu {
    background: #fff;
    border: 1px solid #e4e4e7;
    color: #18181b;
}
.pf-on {
    background: color-mix(in srgb, var(--pf-accent) 14%, white);
    color: var(--pf-accent);
}
.pf.is-dark {
    background: #09090b;
    color: #fafafa;
}
.pf.is-dark aside,
.pf.is-dark header {
    background: #111113;
    border-color: #27272a;
    color: #fafafa;
}
.pf.is-dark .pf-menu {
    background: #18181b;
    border-color: #3f3f46;
    color: #fafafa;
}
.pf.is-dark .pf-on {
    background: #243f36;
    color: #fff;
}
.pf.is-dark aside a:hover {
    background: #27272a;
}
.pf.is-dark .pf-on:hover {
    background: #243f36;
}
.pf.is-dark .pf-menu a:hover,
.pf.is-dark .pf-menu button:hover {
    background: #27272a;
}
.pf.is-dark :deep(h1) {
    color: #fafafa;
}
.pf.is-dark :deep(input),
.pf.is-dark :deep(select),
.pf.is-dark :deep(textarea) {
    background: #27272a;
    border-color: #3f3f46;
    color: #fafafa;
}
.pf.is-dark :deep(th) {
    background: #27272a;
    color: #a1a1aa;
}
.pf.is-dark :deep(td) {
    border-color: #27272a;
    color: #e4e4e7;
}
.pf.is-dark :deep(input::placeholder),
.pf.is-dark :deep(textarea::placeholder) {
    color: #71717a;
}
.pf.is-dark :deep(.bg-white),
.pf.is-dark :deep(pre) {
    background-color: #18181b !important;
    color: #e4e4e7 !important;
}
.pf.is-dark :deep(.text-\[\#18181b\]),
.pf.is-dark :deep(.text-\[\#71717a\]),
.pf.is-dark :deep(.text-\[\#3f3f46\]),
.pf.is-dark :deep(.text-\[\#5c6b63\]) {
    color: #a1a1aa !important;
}
</style>
