import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.es.js';
import BsToastHost from './Components/BsToastHost.vue';
import ThemeHost from './Components/ThemeHost.vue';
import ContentModalBridge from './Components/ContentModalBridge.vue';
import { useErrorHandler } from './Composables/useErrorHandler';

const appName = () => document.querySelector('meta[name="app-name"]')?.content
    || import.meta.env.VITE_APP_NAME
    || 'Epharma';

createInertiaApp({
    title: (title) => (title ? `${appName()} | ${title}` : appName()),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => [h(ThemeHost), h(App, props), h(ContentModalBridge), h(BsToastHost)],
        })
            .use(plugin)
            .use(ZiggyVue)

        // Setup global error handling
        const { setupGlobalHandlers } = useErrorHandler()
        setupGlobalHandlers()

        return app.mount(el);
    },
    progress: {
        color: '#17342b',
    },
});
