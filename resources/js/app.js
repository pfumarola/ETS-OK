import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initializeTheme } from './Composables/useTheme.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Inizializza il tema prima di creare l'app Vue
initializeTheme();

// Dopo il login (navigazione Inertia) window.Ziggy ha ancora solo le route guest; aggiorniamo
// Ziggy da ogni risposta Inertia PRIMA del render (capture phase) così route('dashboard') funziona.
document.addEventListener('inertia:beforeUpdate', (event) => {
    const ziggy = event.detail?.page?.props?.ziggy;
    if (ziggy && typeof window !== 'undefined') {
        window.Ziggy = { ...ziggy };
        if (typeof globalThis !== 'undefined') globalThis.Ziggy = window.Ziggy;
    }
}, true);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
