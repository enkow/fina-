import { defineStore } from 'pinia';
import Pusher from 'pusher-js';
import { usePage } from '@inertiajs/vue3';
import { Game, Reservation, User } from '@/Types/models';
import { ref } from 'vue';
import { watch } from 'vue';
import dayjs from 'dayjs';
import { useModal } from '@/Composables/useModal';

export const usePanelStore = defineStore('widget', () => {
	const page: any = usePage();
	let props: Record<string, any> = page.props;
	let user: User = props.user;
	let timezone: string = user.club?.country?.timezone ?? 'Europe/Warsaw';

	let pusher: any = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
		cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
	});
	let channel: any = null;

	const channelCalendarLoad = async () => {
		let calendarChannelName: string = 'calendar' + (usePage().props.user as User).club?.id;
		return await pusher.subscribe(calendarChannelName);
	};

	if ((usePage().props.user as User).club?.id !== undefined) {
		let calendarChannelName: string = 'calendar' + (usePage().props.user as User).club?.id;
		channel = pusher.subscribe(calendarChannelName);
	}

	let lastVisitedCalendarGame: Game | null = user.club?.games?.[0] ?? {};
	let lastVisitedCalendarDate: string = dayjs().format('YYYY-MM-DD');

	const unpaidInvoiceModalShowing = ref<boolean>(false);
	useModal(unpaidInvoiceModalShowing);
	if (
		page.props.user.club?.preview_mode &&
		!page.props.lastInvoicePaid &&
		page.props.user.club.invoice_autopay === true
	) {
		unpaidInvoiceModalShowing.value = true;
	}

	watch(
		() => window.location,
		() => {
			props = usePage().props;
			user = props.user;
		},
	);

	function isUserRole(roles: (string | undefined)[]): boolean {
		return roles.includes((usePage().props.user as User).type);
	}

	function authorizeLinkByRole(link: string, roles: (string | undefined)[]): string | null {
		return isUserRole(roles) ? link : null;
	}

	let customTablePreferences = ref<Object>({});

	const calendarNextTimeToScroll = ref<string | null>(null);
	const calendarStatusModalShowing = ref<boolean>(false);
	const calendarStatusModalHeading = ref<string | null>(null);
	const calendarStatusModalContent = ref<string | null>(null);
	const calendarStatusModalType = ref<string | null>('success');

	function clearCalendarMemoryVariables() {
		calendarNextTimeToScroll.value = null;
		calendarStatusModalShowing.value = false;
		calendarStatusModalHeading.value = null;
		calendarStatusModalContent.value = null;
		calendarStatusModalType.value = 'success';
	}

	const currentShowingReservation = ref<Reservation | null>(null);

	return {
		page,
		props,
		user,
		lastVisitedCalendarGame,
		lastVisitedCalendarDate,
		channel,
		customTablePreferences,
		calendarNextTimeToScroll,
		calendarStatusModalShowing,
		calendarStatusModalHeading,
		calendarStatusModalContent,
		calendarStatusModalType,
		clearCalendarMemoryVariables,
		isUserRole,
		authorizeLinkByRole,
		timezone,
		currentShowingReservation,
		unpaidInvoiceModalShowing,
		channelCalendarLoad,
	};
});
