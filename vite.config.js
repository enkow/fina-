import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vueJsx from '@vitejs/plugin-vue-jsx';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
import dns from 'dns'

const isDevTools = process.env.VITE_IS_DEVTOOLS;

dns.setDefaultResultOrder('verbatim')
export default defineConfig({
	define: {
		__VUE_PROD_DEVTOOLS__: Number(isDevTools) === 1,
	},
	server: {
		host: '0.0.0.0',
		hmr: {
			host: 'localhost'
		}
	},
	plugins: [
		laravel({
			input: 'resources/js/app.ts',
			refresh: true,
		}),
		vueJsx(),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
		i18n(),
	],
	css: {
		postcss: {
			plugins: [
				require('postcss-hexrgba'),
				require('tailwindcss/nesting'),
				require('tailwindcss'),
				require('autoprefixer'),
			],
		},
	},
	resolve: {
		alias: {
			'@': '/resources/js',
			'@as': '/resources/assets',
			'@sHelpItemImages': '/storage/helpItemImages',
		},
	},
});
