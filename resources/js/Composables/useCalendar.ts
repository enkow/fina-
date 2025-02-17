import { usePage } from '@inertiajs/vue3';
import {
	Club,
	Customer,
	DiscountCode,
	Feature,
	Game,
	Reservation,
	ReservationType,
	SelectOption,
	Slot,
	SpecialOffer,
} from '@/Types/models';
import axios from 'axios';
import { computed, ComputedRef, ref } from 'vue';
import { useNumber } from '@/Composables/useNumber';
import dayjs from 'dayjs';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { SettingEntity } from '@/Types/responses';
import { useReservations } from '@/Composables/useReservations';
import { wTrans } from 'laravel-vue-i18n';

export function useCalendar(game: Game) {
	const { formatAmount } = useNumber();
	const { modelOptions } = useSelectOptions();

	let clubSettingsProp: { [key: string]: SettingEntity } = usePage().props.clubSettings as {
		[key: string]: SettingEntity;
	};
	//@ts-ignore
	let clubProp: Club = usePage().props.club;

	const {
		getFeaturesByGame,
		getFormWithFilledCustomer,
		currencySymbols,
		passGameFeatureKeysToFormDictionary,
		formatInputPrice,
		calculatePrice,
		getSpecialOfferAppliesStatus,
		isStartAtInSpecialOfferTimeRanges,
		getFormDictionary,
		getFeaturesBySlot,
	} = useReservations();

	type FeatureMap = {
		[featureType: string]: Feature[];
	};
	const features = computed<FeatureMap>(() => getFeaturesByGame(game)).value;

	function getClubFeatureSetting(gameId: number, featureType: string, settingKey: string): SettingEntity {
		let key = Object.keys(usePage().props.clubSettings as { string: SettingEntity }).find(
			(key) =>
				clubSettingsProp[key]?.feature?.type === featureType &&
				clubSettingsProp[key]?.feature?.game?.id === gameId &&
				key.includes(settingKey),
		);
		return clubSettingsProp?.[key ?? '0'] ?? { value: null };
	}

	function passFeatureKeysToFormDictionary(formDictionary: Object) {
		return passGameFeatureKeysToFormDictionary(formDictionary, features);
	}

	let clubSettings: { [key: string]: number | boolean } = {
		calendarTimeScale: (clubSettingsProp['calendar_time_scale'].value as number) ?? 30,
		pricePerPersonType: getClubFeatureSetting(
			//@ts-ignore
			game.id,
			'price_per_person',
			'price_per_person_type',
		)?.value,
		fullDayReservationStatus:
			features.fixed_reservation_duration.length > 0 &&
			getClubFeatureSetting(
				//@ts-ignore
				game.id,
				'fixed_reservation_duration',
				'fixed_reservation_duration_status',
			)?.value === true &&
			getClubFeatureSetting(
				//@ts-ignore
				game.id,
				'fixed_reservation_duration',
				'fixed_reservation_duration_value',
			)?.value === 24,
		fixedReservationDurationStatus:
			features.fixed_reservation_duration.length > 0 &&
			//@ts-ignore
			getClubFeatureSetting(game.id, 'fixed_reservation_duration', 'fixed_reservation_duration_status')
				?.value,
		visibleCalendarSlots:
			(Object.values(usePage().props.clubSettings as { [key: string]: SettingEntity }).find(
				(setting) =>
					setting.feature &&
					setting.feature.type === 'has_visible_calendar_slots_' + 'quantity_setting' &&
					setting.feature.game?.id === game.id,
			)?.['value'] as number) ?? 0,
		lounges_status:
			features.slot_has_lounge.length > 0 &&
			(getClubFeatureSetting(game.id as number, 'slot_has_lounge', 'lounges_status')?.['value'] as boolean),
		reservationNumberOnCalendarStatus:
			(clubSettingsProp['reservation_number_on_calendar_status'].value as boolean) ?? false,
		reservationNotesOnCalendarStatus:
			(clubSettingsProp['reservation_notes_on_calendar_status'].value as boolean) ?? false,
		timersStatus: (clubSettingsProp?.['timers_status']?.value as boolean) ?? false,
		bulbStatus: getClubFeatureSetting(game.id as number, 'slot_has_bulb', 'bulb_status')?.[
			'value'
		] as boolean,
	};

	const customerSearchResults = ref<{
		[field: string]: Customer[];
	}>({
		last_name: [],
		email: [],
		phone: [],
	});

	function getCustomersResults(searchString: string, field: string): Customer[] {
		axios
			.get(
				route('club.customers.search', {
					search: searchString,
				}),
			)
			.then(function (response: { data: Customer[] }) {
				customerSearchResults.value[field] = response.data;
			});
		return customerSearchResults.value[field];
	}

	const discountCodeOptionsComp = (form: { [key: string]: any }) => {
		return computed(() => {
			let discountCodeCollection = (clubProp.discountCodes as DiscountCode[]).filter(
				(discountCode: DiscountCode) =>
					discountCode.game_id === game.id &&
					discountCode.active &&
					(discountCode.start_at === null || dayjs(discountCode.start_at).isBefore(dayjs())) &&
					(discountCode.end_at === null || dayjs(discountCode.end_at).isAfter(dayjs())),
			);
			let resultCollection: SelectOption[] = [
				{ code: null, label: wTrans('discount-code.no-discount-code').value },
			];
			discountCodeCollection.forEach((discountCode: DiscountCode) => {
				let value;
				if (discountCode.type === 0) {
					value = discountCode.value + '%';
				} else {
					value = formatAmount(discountCode.value * 100);
				}
				resultCollection.push({
					code: discountCode.id,
					label: discountCode.code + ' - ' + value,
				});
			});
			return resultCollection;
		});
	};

	function getDurationOptions(max: number = 300): SelectOption[] {
		let durationOptions: SelectOption[] = [];
		let calendarTimeScale = clubSettings['calendarTimeScale'] as number;
		for (let i = calendarTimeScale; i <= max; i += calendarTimeScale) {
			const hour = Math.floor(i / 60);
			const minute = (i % 60).toString().padStart(2, '0');

			durationOptions.push({
				code: i,
				label: `${hour}:${minute}`,
			});
		}
		return durationOptions;
	}

	function durationNumberToTime(duration: number): string {
		const hour = Math.floor(duration / 60)
			.toString()
			.padStart(2, '0');
		const minute = (duration % 60).toString().padStart(2, '0');

		return `${hour}:${minute}`;
	}

	const specialOfferOptionsComp = (form: { [key: string]: any }) => {
		return computed(() => {
			let resultCollection: SelectOption[] = [
				{ code: null, label: wTrans('special-offer.no-special-offer').value },
			];
			let startAtWeekDay = dayjs(form.start_at).day();
			startAtWeekDay = startAtWeekDay === 0 ? 7 : startAtWeekDay;
			let specialOffersCollection: SpecialOffer[] = (clubProp.specialOffers as SpecialOffer[]).filter(
				(specialOffer) =>
					specialOffer.game_id === game.id &&
					specialOffer.active_week_days.includes(startAtWeekDay) &&
					(specialOffer.slots === null || specialOffer.slots === form.slots_count) &&
					(specialOffer.duration === null || specialOffer.duration === durationNumberToTime(form.duration)) &&
					getSpecialOfferAppliesStatus(specialOffer, form.start_at) &&
					isStartAtInSpecialOfferTimeRanges(specialOffer, form.start_at),
			);
			specialOffersCollection.forEach((specialOffer: SpecialOffer) => {
				resultCollection.push({
					code: specialOffer.id,
					label: specialOffer.name + ' - ' + specialOffer.value + '%',
				});
			});
			return resultCollection;
		});
	};

	let reservationTypeOptionsComp = computed<SelectOption[]>(() => {
		let resultCollection: SelectOption[] = [
			{ code: null, label: wTrans('reservation-type.no-reservation-type').value },
		];
		clubProp.reservationTypes?.forEach((reservationType) => {
			resultCollection.push({ code: reservationType.id as number, label: reservationType.name as string });
		});
		return resultCollection;
	});

	const bulbsOptionsComp = computed<SelectOption[]>(() => {
		const result = [
			{
				code: 'nothing',
				label: wTrans('reservation.bulbs.nothing').value,
			},
			{
				code: 'reservation',
				label: wTrans('reservation.bulbs.reservation-time').value,
			},
			{
				code: 'duration',
				label: wTrans('reservation.bulbs.duration-time').value,
			},
		];

		return result;
	});

	function fillReservationForm(form: any, reservation: Reservation, data: { [key: string]: any }) {
		let customer: Customer = reservation.extended?.customer as Customer;
		if (customer) {
			form = getFormWithFilledCustomer(form, customer);
		} else {
			form.anonymous_reservation = true;
			form.customer.first_name = '';
			form.customer.last_name = '';
			form.customer.email = '';
			form.customer.phone = '';
			form.customer.id = null;
		}

		form.club_reservation = !!reservation.club_reservation;
		form.status = reservation.extended?.status;
		form.reservation_type_id = reservation.extended?.reservationType?.id;
		form.discount_code_id = reservation.extended?.discountCode?.id;
		form.special_offer_id = reservation.extended?.specialOffer?.id;
		form.customer_note = reservation.customer_note;
		form.show_customer_note_on_calendar = reservation.show_customer_note_on_calendar;
		form.club_note = reservation.club_note;
		form.show_club_note_on_calendar = reservation.show_club_note_on_calendar;
		// set max 300 minutes duration if game has full_day_reservations duration to avoid validation errors
		form.duration = Math.min(reservation.extended?.duration ?? 0, 300);
		form.start_at = dayjs(reservation.start_datetime);
		form.slot_ids = [reservation.extended?.slot?.id ?? null];
		form.occupied_status = !!reservation.occupied_status;
		form.parent_slot_id = reservation.extended?.slot.parentSlot?.id;
		form.apply_to_all_reservations = false;

		let calendarTimeScale = clubSettings['calendarTimeScale'] as number;
		if (data.hasOwnProperty('adjustStartAt') && data['adjustStartAt'] === true) {
			let startAtMinute: number = form.start_at.minute();
			form.start_at = form.start_at.minute(startAtMinute - (startAtMinute % calendarTimeScale));
			form.start_at = form.start_at.format('YYYY-MM-DD HH:mm');
			delete data['adjustStartAt'];
		}

		if (data.hasOwnProperty('adjustDuration') && data['adjustDuration'] === true) {
			if (form.duration % calendarTimeScale) {
				form.duration += calendarTimeScale - (form.duration % calendarTimeScale);
			}
			if (form.duration === 0) {
				form.duration = calendarTimeScale;
			}
			delete data['adjustDuration'];
		}

		// Load person count value from <price_per_person> feature payload
		if (features.price_per_person.length) {
			form.features[features.price_per_person[0].id].person_count = reservation.person_count;
		}

		// Load person count value from <person_as_slot> feature payload
		if (features.person_as_slot.length) {
			form.features[features.person_as_slot[0].id].persons_count =
				reservation.extended?.reservation_slots_count;
			form.features[features.person_as_slot[0].id].new_parent_slot_id = null;
		}

		// Load display name value from <reservation_slot_has_display_name> feature payload
		if (features.reservation_slot_has_display_name.length) {
			const pivotData: string = String(
				reservation.extended?.features?.find(
					(feature: Feature) => feature.type === 'reservation_slot_has_display_name',
				)?.pivot?.data ?? JSON.stringify({ display_name: '' }),
			);
			form.features[features.reservation_slot_has_display_name[0].id].display_name =
				JSON.parse(pivotData).display_name;
		}

		// Load parent_slot_id value from <slot_has_parent> feature payload
		if (features.slot_has_parent.length) {
			form.features[features.slot_has_parent[0].id].parent_slot_id = reservation.parent_slot_id;
		}

		const bulbFeature = reservation.extended.features.find(
			(feature: Feature) => feature.type === 'slot_has_bulb',
		);
		if (bulbFeature) {
			form.features[bulbFeature.id].type = JSON.parse(bulbFeature?.pivot?.data || '{}')?.type ?? 'nothing';
		}

		Object.keys(data).forEach((key) => {
			form[key] = data[key];
		});

		return form;
	}

	function mergeOptions(options: SelectOption[], addons: SelectOption[]) {
		let optionsClone = [...options];
		addons.forEach((addon) => {
			optionsClone.push(addon);
		});
		return optionsClone;
	}

	function getSlotItemForDisplay(slot: Slot) {
		let resultItem: SelectOption = {
			code: slot.id,
			label: slot.name,
		};
		// if game has slot_has_lounge filter and club has this feature enabled add min-max person info
		// to slot display name
		if (features.slot_has_lounge.length && clubSettings['lounges_status']) {
			let pivotData: string = slot.features.find((feature: Feature) => feature.type === 'slot_has_lounge')
				?.pivot?.data as string;
			if (pivotData) {
				let slotLoungeFeaturePivotData = JSON.parse(pivotData);
				if (slotLoungeFeaturePivotData.status) {
					resultItem.label =
						resultItem.label +
						` (${slotLoungeFeaturePivotData.min}-${slotLoungeFeaturePivotData.max} ` +
						features.slot_has_lounge[0]?.translations['lounge-capacity-items'] +
						')';
				}
			}
		}
		let pivotData: string = slot.features.find(
			(feature: Feature) => feature.type === 'book_singular_slot_by_capacity',
		)?.pivot?.data as string;
		if (pivotData) {
			let slotCapacityFeaturePivotData = JSON.parse(pivotData);
			resultItem.label = resultItem.label + ` (${slotCapacityFeaturePivotData.capacity}-os)`;
		}
		return resultItem;
	}

	const showCommentsOnCalendarStatusesVisibility = computed<boolean>(() => {
		return (
				features.book_singular_slot_by_capacity.length === 0 ||
				features.has_custom_views[0].data['custom_views']['reservations.calendar'] === null
			) &&
			features.person_as_slot.length === 0 &&
			clubSettings['reservationNotesOnCalendarStatus'];
	});

	return {
		getClubFeatureSetting,
		getFormDictionary,
		features,
		getFeaturesBySlot,
		clubSettings,
		formatInputPrice,
		getFormWithFilledCustomer,
		currencySymbols,
		customerSearchResults,
		getCustomersResults,
		passFeatureKeysToFormDictionary,
		specialOfferOptionsComp,
		fillReservationForm,
		getDurationOptions,
		reservationTypeOptionsComp,
		bulbsOptionsComp,
		calculatePrice,
		discountCodeOptionsComp,
		mergeOptions,
		getSlotItemForDisplay,
		showCommentsOnCalendarStatusesVisibility
	};
}
