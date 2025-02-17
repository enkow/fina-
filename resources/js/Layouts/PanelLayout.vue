<template>
	<Head :title="breadcrumbs?.[breadcrumbs.length - 1]?.label ?? title" />
	<MobileNav :is-expanded="isMobileNavExpanded" :items="navigationItems" />
	<div :class="{ sidebarReduced: isSidebarReduced }" class="w-full">
		<Sidebar
			:is-reduced="isSidebarReduced"
			:navigation-items="navigationItems"
			@toggle="toggleSidebarReduction"
			@account-modal-toggle="accountModalShowing = !accountModalShowing" />

		<div class="app-wrapper">
			<div class="flex w-full justify-between bg-white">
				<div v-if="breadcrumbs.length > 0" class="breadcrumbs">
					<div v-for="breadcrumbsItem in breadcrumbs" class="breadcrumbs-item">
						<Link :href="breadcrumbsItem.href">{{ breadcrumbsItem.label }}</Link>
						<BreadcrumbsSeparator
							v-if="breadcrumbsItem !== breadcrumbs[breadcrumbs.length - 1]"
							class="ml-1.5 mr-1.5 hidden xs:block" />
					</div>
				</div>
				<div v-if="usePage().props.user.type === 'admin'" class="flex h-20 items-center">
					<SimpleSelect v-model="country" :options="countryOptions" class="w-72" />
				</div>
			</div>
			<slot />
		</div>
		<AccountModal :showing="accountModalShowing" @logout="logout" @close="accountModalShowing = false" />
		<FirstLoginModal :showing="firstModalShowing" @close="firstModalShowing = false" />
		<UnpaidInvoiceModal
			:showing="panel.unpaidInvoiceModalShowing"
			@close="panel.unpaidInvoiceModalShowing = false" />
	</div>
</template>

<style scoped>
.app-wrapper {
	@apply ml-0 w-full transition transition-all lg:ml-72 lg:w-[calc(100%-18rem)];
}

.sidebarReduced .app-wrapper {
	@apply ml-0 w-full lg:ml-18 lg:w-[calc(100%-4.5rem)];
}

.breadcrumbs {
	@apply mt-0 flex flex-wrap items-center pl-5 text-lg font-medium shadow-sm xs:h-20 xs:flex-nowrap xs:space-y-0 lg:pl-10;

	.breadcrumbs-item {
		@apply flex w-full items-center xs:w-fit;
	}

	.breadcrumbs-item:not(:first-child) {
		@apply -mt-5 xs:mt-0;
	}
}
</style>

<script lang="ts" setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
	nextTick,
	onBeforeMount,
	onMounted,
	onUnmounted,
	Ref,
	ref,
	UnwrapRef,
	watch,
	watchEffect,
} from 'vue';
import { useNavigation } from '@/Composables/useNavigation';
import BreadcrumbsSeparator from '@/Components/Dashboard/Icons/BreadcrumbsSeparator.vue';
import MobileNav from '@/Partials/Pages/Club/MobileNav.vue';
import Sidebar from '@/Partials/Pages/Club/Sidebar.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import UnpaidInvoiceModal from '@/Components/Dashboard/Modals/UnpaidInvoiceModal.vue';
import AccountModal from '@/Components/Dashboard/Modals/AccountModal.vue';
import { useModal } from '@/Composables/useModal';
import FirstLoginModal from '@/Components/Dashboard/Modals/FirstLoginModal.vue';
import { TYPE, useToast } from 'vue-toastification';
import { Club, User } from '@/Types/models';
import axios from 'axios';
import 'vue-toastification/dist/index.css';
import { getActiveLanguage, loadLanguageAsync } from 'laravel-vue-i18n';
import dayjs from 'dayjs';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		breadcrumbs?: { href: string; label: string }[];
		title?: string;
		countrySelect?: boolean;
		club?: Club;
	}>(),
	{
		breadcrumbs: () => [],
		title: '',
	},
);

const emit = defineEmits<{
	(e: 'toggleSidebarReductionStatus'): void;
}>();

const $toast = useToast();
const page = usePage<{
	flash: {
		message?: string;
		type?: TYPE;
		timeout?: number;
	};
	user: User;
	lastInvoicePaid: Boolean;
}>();

const accountModalShowing = ref<boolean>(false);
useModal(accountModalShowing);

const firstModalShowing = ref<boolean>(false);
useModal(firstModalShowing);

if (
	['manager', 'employee'].includes(page.props.user.type!) &&
	page.props.user.club?.first_login_message &&
	!page.props.user.club?.first_login_message_showed
) {
	firstModalShowing.value = true;
}

const isSidebarReduced: Ref<UnwrapRef<boolean | undefined>> = ref(page.props.user.sidebar_reduced);
const isMobileNavExpanded: Ref<boolean> = ref(false);
const url: Ref<string> = ref('#');

const country = ref(page.props.user.country_id);

watch(country, (newCountry, oldCountry) => {
	router.visit(route('admin.countries.change-admin-country-id'), {
		data: { country: newCountry },
		preserveScroll: true,
		method: 'post',
	});
});

const { countryOptions } = useSelectOptions();

function toggleSidebarReduction() {
	axios.post(route('global.toggle-sidebar-reduction-status')).then(function (response: {
		data: { sidebar_reduced: boolean };
	}) {
		isSidebarReduced.value = response.data.sidebar_reduced;
		setTimeout(() => emit('toggleSidebarReductionStatus'), 1);
	});
}

function logout() {
	accountModalShowing.value = false;
	router.post(route('logout'));
}

const navigationItems = useNavigation(page.props.user);

watchEffect(() => {
	if (page.props.flash) {
		if (!page.props.flash.message || !page.props.flash.type) {
			return;
		}

		let options: Record<string, any> = {
			type: page.props.flash.type,
		};
		if (page.props.flash.timeout) {
			options['timeout'] = page.props.flash.timeout;
		}

		nextTick(() => {
			$toast(page.props.flash.message, options);
			page.props.flash.message = null;
		});
	}
});

onBeforeMount(() => {
	if (page.props?.user?.club) {
		if (page.props.user.club.country.locale !== getActiveLanguage()) {
			loadLanguageAsync(page.props.user.club.country.locale);
		}
	} else {
		loadLanguageAsync('pl');
	}
});

const panel = usePanelStore();
</script>

<style>
html {
	font-size: 15.5px;
}
@media (max-width: 1636px) {
	html {
		font-size: 14px;
	}
}
@media (max-width: 1536px) {
	html {
		font-size: 13px;
	}
}
</style>
