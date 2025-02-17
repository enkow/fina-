import { wTrans } from 'laravel-vue-i18n';
import { ComputedRef, Ref, ref, shallowRef } from 'vue';
import MegaphoneIcon from '@/Components/Dashboard/Icons/MegaphoneIcon.vue';
import MagnifyingGlassWithChartIcon from '@/Components/Dashboard/Icons/MagnifyingGlassWithChartIcon.vue';
import ReservationBookIcon from '@/Components/Dashboard/Icons/ReservationBookIcon.vue';
import CogIcon from '@/Components/Dashboard/Icons/CogIcon.vue';
import MoneyIcon from '@/Components/Dashboard/Icons/MoneyIcon.vue';
import LofebuoyIcon from '@/Components/Dashboard/Icons/LofebuoyIcon.vue';
import MapIcon from '@/Components/Dashboard/Icons/MapIcon.vue';
import PeopleIcon from '@/Components/Dashboard/Icons/PeopleIcon.vue';
import SofaIcon from '@/Components/Dashboard/Icons/SofaIcon.vue';
import PuzzleIcon from '@/Components/Dashboard/Icons/PuzzleIcon.vue';
import BillardIcon from '@/Components/Dashboard/Icons/BillardIcon.vue';
import EnvelopeIcon from '@/Components/Dashboard/Icons/EnvelopeIcon.vue';
import HourglassIcon from '@/Components/Dashboard/Icons/HourglassIcon.vue';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import PrinterIcon from '@/Components/Dashboard/Icons/PrinterIcon.vue';
import OvenIcon from '@/Components/Dashboard/Icons/OvenIcon.vue';
import { Club, Game, User } from '@/Types/models';
import { usePage } from '@inertiajs/vue3';
import PeopleUnfilledIcon from '@/Components/Dashboard/Icons/PeopleUnfilledIcon.vue';

export interface DropdownItem {
	active: string;
	href: string;
	label: ComputedRef<string> | string | Ref<string>;
	requireManagerRole?: boolean;
	disabled?: boolean;
}

export interface NavigationItem {
	href: string;
	label: string | Ref<string>;
	icon?: HTMLElement;
	active: Array<string>;
	dropdownItems: Array<DropdownItem>;
	showOnlyIfSidebarReduced?: boolean;
	requireManagerRole?: boolean;
	expanded?: boolean;
	disabled?: boolean;
}

function getAdminNavigationItems(): NavigationItem[] {
	return [
		{
			href: route('admin.countries.index'),
			label: ref('Kraje'),
			icon: shallowRef(MapIcon),
			active: route().current('admin.countries.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.translations.index'),
			label: ref('Tłumaczenia'),
			icon: shallowRef(PrinterIcon),
			active: route().current('admin.translations.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.customers.index'),
			label: ref('Klienci'),
			icon: shallowRef(PeopleIcon),
			active: route().current('admin.customers.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.products.index'),
			label: ref('Produkty'),
			icon: shallowRef(PuzzleIcon),
			active: route().current('admin.products.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.clubs.index'),
			label: ref('Kluby'),
			icon: shallowRef(SofaIcon),
			active: route().current('admin.clubs.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.games.index'),
			label: ref('Gry'),
			icon: shallowRef(BillardIcon),
			active: route().current('admin.games.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.reservations.index'),
			label: ref('Rezerwacje'),
			icon: shallowRef(MoneyIcon),
			active: route().current('admin.reservations.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.refunds.index'),
			label: ref('Zwroty'),
			icon: shallowRef(EnvelopeIcon),
			active: route().current('admin.refunds.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.settlements.index'),
			label: ref('Rozliczenia'),
			icon: shallowRef(HourglassIcon),
			active: route().current('admin.settlements.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.help-sections.index'),
			label: ref('Sekcje pomocy'),
			icon: shallowRef(LofebuoyIcon),
			active: route().current('admin.help-sections.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.settings.index'),
			label: ref('Ustawienia'),
			icon: shallowRef(OvenIcon),
			active: route().current('admin.settings.*'),
			dropdownItems: [],
		},
		{
			href: route('admin.statistics.index'),
			label: ref('Statystyki'),
			icon: shallowRef(HashIcon),
			active: route().current('admin.statistics.*'),
			dropdownItems: [],
		},
	];
}

function getClubNavigationItems(user: User): NavigationItem[] {
	const slotLinks: DropdownItem[] = [];
	user?.club?.games?.forEach((game: Game) => {
		slotLinks.push({
			active: route().current('club.games.slots.*', { game: game.id }),
			href: route('club.games.slots.index', { game: game.id }),
			label: shallowRef(
				(usePage().props.gameTranslations as Record<number, { [key: string]: string }>)[game.id][
					'slot-plural'
				],
			),
		});
	});

	return [
		{
			href: route('club.games.reservations.calendar', { game: user?.club?.games?.[0]?.name }),
			label: wTrans('reservation.plural'),
			icon: shallowRef(ReservationBookIcon),
			active: route().current('club.games.reservations.calendar'),
			dropdownItems: [],
		},
		{
			href: route('club.games.statistics.main', {
				game: user?.club?.games?.[0].id,
			}),
			label: wTrans('statistics.plural'),
			icon: shallowRef(MagnifyingGlassWithChartIcon),
			active:
				route().current('club.games.statistics.main') ||
				route().current('club.reservations.index') ||
				route().current('club.games.statistics.weekly') ||
				route().current('club.statistics.sets'),
			dropdownItems: [
				{
					active: route().current('club.games.statistics.main'),
					href: route('club.games.statistics.main', {
						game: user?.club?.games?.[0].id,
					}),
					label: wTrans('statistics.plural'),
					requireManagerRole: true,
				},
				{
					active: route().current('club.games.statistics.weekly'),
					href: route('club.games.statistics.weekly', {
						game: user?.club?.games?.[0].id,
					}),
					label: wTrans('statistics.weekly-statistics'),
					requireManagerRole: true,
				},
				{
					active: route().current('club.reservations.index'),
					href: route('club.reservations.index'),
					label: wTrans('reservation.reservation-search'),
				},
				{
					active: route().current('club.statistics.sets'),
					href: route('club.statistics.sets'),
					label: wTrans('statistics.sets-statistics'),
					disabled: !user?.club?.sets_enabled,
					requireManagerRole: true,
				},
			],
		},
		{
			href: route('club.discount-codes.index'),
			label: wTrans('main.marketing'),
			icon: shallowRef(MegaphoneIcon),
			active: route().current('club.discount-codes.*') || route().current('club.special-offers.*'),
			dropdownItems: [
				{
					active: route().current('club.discount-codes.*'),
					href: route('club.discount-codes.index'),
					label: wTrans('discount-code.plural'),
				},
				{
					active: route().current('club.special-offers.*'),
					href: route('club.special-offers.index'),
					label: wTrans('special-offer.plural'),
				},
			],
		},
		{
			href: route('club.settlements.index'),
			label: wTrans('customer.nav-parent'),
			icon: shallowRef(PeopleUnfilledIcon),
			active: route().current('club.customers.*') || route().current('club.rates.*'),
			requireManagerRole: true,
			dropdownItems: [
				{
					active: route().current('club.customers.*'),
					href: route('club.customers.index'),
					label: wTrans('customer.plural'),
					requireManagerRole: true,
				},
				{
					active: route().current('club.rates.*'),
					href: route('club.rates.index'),
					label: wTrans('statistics.customer-ratings'),
					requireManagerRole: true,
				},
			],
		},
		{
			// 'href': route('club.daily-reports.index'),
			href: route('club.settlements.index'),
			label: wTrans('settlement.plural'),
			icon: shallowRef(MoneyIcon),
			active:
				route().current('club.settlements.*') ||
				route().current('club.refunds.*') ||
				route().current('club.billing.*') ||
				route().current('club.invoices.*'),
			dropdownItems: [
				// {
				//     'activeRoute': 'club.daily-reports',
				//     'href': route('club.daily-reports.index'),
				//     'label': wTrans('nav.daily-reports'),
				// },
				{
					active: route().current('club.settlements.*'),
					href: route('club.settlements.index'),
					label: wTrans('settlement.plural'),
					requireManagerRole: true,
				},
				{
					active: route().current('club.refunds.*'),
					href: route('club.refunds.index'),
					label: wTrans('refund.plural'),
					disabled: (user.club as Club).online_payments_enabled === 'disabled',
				},
				{
					href: route('club.billing.index'),
					label: wTrans('billing.singular'),
					active: route().current('club.billing.index'),
					requireManagerRole: true,
					disabled:
						user.club?.invoice_autopay === false ||
						!(
							(!!user?.club?.games?.filter((game) => game.pivot.include_on_invoice).length &&
								!!user?.club?.games?.filter((game) => game.pivot.include_on_invoice).length) ||
							!!user?.club?.products.length
						) ||
						(user.club?.invoice_next_month === null && user.club?.invoice_next_year === null && user.club?.preview_mode === false)
					,
				},
				{
					href: route('club.invoices.index'),
					label: wTrans('billing.invoices'),
					active: route().current('club.invoices.index'),
					requireManagerRole: true,
					disabled: user.club?.invoice_autopay === true,
				},
			],
		},
		{
			href: route('club.opening-hours.show'),
			label: wTrans('settings.plural'),
			icon: shallowRef(CogIcon),
			active:
				route().current('club.opening-hours.*') ||
				route().current('club.opening-hours-exceptions.*') ||
				route().current('club.employees.*') ||
				route().current('club.online-payments.*') ||
				route().current('club.settings.reservation') ||
				route().current('club.settings.calendar') ||
				route().current('club.sets.*') ||
				route().current('club.agreements.*') ||
				route().current('club.games.pricelists.*') ||
				route().current('club.games.slots.*'),
			dropdownItems: [
				...[
					{
						active:
							route().current('club.opening-hours.*') || route().current('club.opening-hours-exceptions.*'),
						href: route('club.opening-hours.show'),
						label: wTrans('opening-hours.singular'),
					},
					{
						active: route().current('club.employees.*'),
						href: route('club.employees.index'),
						label: wTrans('employee.plural'),
						requireManagerRole: true,
					},
					...(user?.club?.online_payments_enabled === 'external'
						? [
								{
									active: route().current('club.online-payments.*'),
									href: route('club.online-payments.index'),
									label: wTrans('settlement.online-payments'),
									requireManagerRole: true,
								},
						  ]
						: []),
					{
						active: route().current('club.settings.reservation'),
						href: route('club.settings.reservation'),
						label: wTrans('reservation.online-reservations'),
					},
					{
						active: route().current('club.settings.calendar'),
						href: route('club.settings.calendar'),
						label: wTrans('reservation.club-calendar'),
					},
					{
						active: route().current('club.sets.*'),
						href: route('club.sets.index'),
						label: wTrans('set.plural'),
						disabled: !user?.club?.sets_enabled,
					},
					{
						active: route().current('club.agreements.*'),
						href: route('club.agreements.index'),
						label: wTrans('agreement.plural'),
					},
					{
						active: route().current('club.games.pricelists.*'),
						href: route('club.games.pricelists.index', {
							game: user?.club?.games?.[0].id,
						}),
						label: wTrans('pricelist.plural'),
					},
				],
				...slotLinks,
			],
		},
		{
			href: route('club.help-sections.index'),
			label: wTrans('help.title'),
			icon: shallowRef(LofebuoyIcon),
			showOnlyIfSidebarReduced: true,
			active: route().current('club.help-sections.*'),
			dropdownItems: [],
			disabled: !(usePage().props.helpEnabled as boolean),
		},
	];
}

export function useNavigation(user: User): NavigationItem[] {
	return (user.type === 'admin' ? getAdminNavigationItems() : getClubNavigationItems(user))
		.map((item) => {
			if (item.requireManagerRole && user.type !== 'manager') {
				item.disabled = true;
			}

			item.dropdownItems = item.dropdownItems.filter((dropdownItem: DropdownItem) => {
				return !dropdownItem.disabled && (!dropdownItem.requireManagerRole || user.type === 'manager');
			});

			if (item.active && item.dropdownItems.length) {
				item.expanded = true;
			}

			return item;
		})
		.filter((item) => !item.disabled && (!item.requireManagerRole || user.type === 'manager'));
}
