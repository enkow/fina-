import './bootstrap';
import '../css/app.pcss';

import { createApp, DefineComponent, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import VueClickAway from 'vue3-click-away';
import { i18nVue } from 'laravel-vue-i18n';
import dayjs from 'dayjs';
import utc from 'dayjs-plugin-utc';
import timezone from 'dayjs/plugin/timezone';
import duration from 'dayjs/plugin/duration';
import VueClipboard from 'vue3-clipboard';
import Toast, { POSITION } from 'vue-toastification';
import { createPinia } from 'pinia';
import VueTippy from 'vue-tippy';
import * as Sentry from '@sentry/vue';
import { BrowserTracing } from '@sentry/tracing';

dayjs.extend(utc);
dayjs.extend(duration);
dayjs.extend(timezone);

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Bookgame';

createInertiaApp({
	title: (title) => `${title} - ${appName}`,
	progress: {
		color: '#4B5563',
	},
	resolve: (name: string) => {
		const page = resolvePageComponent<DefineComponent>(
			`./Pages/${name}.vue`,
			// @ts-ignore
			import.meta.glob('./Pages/**/*.vue'),
		);
		page.then((module) => {
			module.default.layout = module.default.layout;
		});
		return page;
	},
	setup({ el, App, props, plugin }) {
		const app = createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(ZiggyVue, Ziggy)
			.use(VueClickAway)
			.use(createPinia())
			.use(VueTippy)
			.use(i18nVue, {
				resolve: async (lang: string) => {
					const langs = import.meta.glob('../../lang/*.json');
					return await langs[`../../lang/php_${lang}.json`]();
				},
			})
			.use(VueClipboard, {
				autoSetContainer: true,
				appendToBody: true,
			})
			.use(Toast, {
				transition: 'Vue-Toastification__fade',
				maxToasts: 5,
				newestOnTop: true,
				position: POSITION.BOTTOM_RIGHT,
				timeout: 3000,
				closeOnClick: true,
				pauseOnFocusLoss: false,
				pauseOnHover: false,
				draggable: true,
				draggablePercent: 0.5,
				showCloseButtonOnHover: false,
				hideProgressBar: false,
				closeButton: 'button',
				icon: true,
				rtl: false,
			});

		Sentry.init({
			app,
			dsn: import.meta.env.VITE_SENTRY_DSN_PUBLIC,
			trackComponents: true,
			logErrors: true,
			integrations: [new BrowserTracing()],
			tracesSampleRate: 0.3,
		});
		app.mount(el);
	},
});
