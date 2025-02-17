import { defineStore } from 'pinia';
import { computed, ComputedRef, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    Agreement,
    Announcement,
    Club,
    Country,
    Customer,
    DiscountCode,
    Feature,
    Game,
    Reservation,
    Set,
    Slot,
    SpecialOffer,
} from '@/Types/models';
import { DateOpeningHours, SettingEntity } from '@/Types/responses';
import dayjs, { Dayjs } from 'dayjs';
import { getActiveLanguage, loadLanguageAsync, wTrans } from 'laravel-vue-i18n';
import axios from 'axios';
import { computedAsync, watchDebounced } from '@vueuse/core';
import { useReservations } from '@/Composables/useReservations';
import Pusher from 'pusher-js';
import { formatCalendarDate } from '@/Utils';
import { useRoutingStore } from './routing';

export const useWidgetStore = defineStore('widget', () => {
    const {
        timeStringToMinutes,
        getFeaturesByGame,
        getSpecialOfferAppliesStatus,
        weekDay,
        getFormDictionary,
        passGameFeatureKeysToFormDictionary,
        isStartAtInSpecialOfferTimeRanges,
        isStartAtInSpecialOfferAppliesDates,
        currencySymbols,
        featureTypes,
    } = useReservations();
    const routingStore = useRoutingStore();

    const props = usePage().props;
    const club: Club = props.club as Club;
    const address = props.clubAddress as string;
    const currency: string = (club.country as Country).currency;
    const announcements = club.announcements as Announcement[];
    const countries: Country[] = club.widget_countries as Country[];
    const currentLocale = ref<string>(
        (props.locale ?? props.customer.locale ?? 'en').toLowerCase(),
    );
    const currentCountry = ref<string>(
        countries.find((country) => country.locale === currentLocale.value)
            ?.code ?? 'gb',
    );
    const priceObject = ref<
        | {
              basePrice: Number;
              finalPrice: Number;
              reservationFee: {
                  commisionFixed: Number;
              };
              setsPrice: Number;
              priceBeforeDiscount: Number;
          }
        | {}
    >({});
    setLocale(currentLocale.value);

    const isViewedFromIphoneSafari = computed<boolean>(() => {
        return (
            /iPhone|iPod|iPad/.test(navigator.userAgent) &&
            /Safari/.test(navigator.userAgent) &&
            /Version\/\d+/.test(navigator.userAgent) &&
            !window.MSStream
        );
    });

    const isSlotSelectionEnabled = computed(
        () =>
            showingStatuses.value.slotHasLounge ||
            showingStatuses.value.slotSelection,
    );

    const generalTranslations = computed<Object>(() => {
        return (props.generalTranslations as { [countryId: number]: any })[
            currentLocale.value
        ];
    });
    const gameNames = computed(() => {
        return (props.gamesNames as Record<number, string>)[
            currentLocale.value
        ];
    });

    const globalSettings: SettingsContainer =
        props?.globalSettings as SettingsContainer;
    const settings: SettingsContainer = props?.settings as SettingsContainer;
    const widgetColor: string = settings['widget_color'].value as string;
    const fullLoader = ref<boolean>(false);

    type FeatureMap = {
        [featureType: string]: Feature[];
    };
    let gamesFeatures: {
        [gameId: number]: FeatureMap;
    } = {};
    (usePage().props.games as Game[]).forEach((game) => {
        gamesFeatures[game.id] = featureTypes.reduce<FeatureMap>(
            (acc, type) => {
                acc[type] =
                    game.features?.filter((feature) => feature.type === type) ??
                    [];
                return acc;
            },
            {},
        );
    });
    let gameFeatures = computed(() => {
        let result = JSON.parse(
            JSON.stringify(
                (gamesFeatures[form.game_id ?? 0] as FeatureMap) ?? {},
            ),
        );
        Object.keys(result).forEach((type: string) => {
            Object.keys(result[type]).forEach((featureKey: string) => {
                result[type][featureKey]['translations'] =
                    result[type][featureKey]['translations'][
                        currentLocale.value
                    ];
            });
        });
        return result;
    });
    const featuresData = props.featuresData as { [featureId: number]: any };
    const gameSlotTypes = computed<string[]>(() => {
        return (
            featuresData[gameFeatures.value.slot_has_type[0]?.id]?.types ?? []
        );
    });
    const gameSlotSubtypes = computed<string[]>(() => {
        return featuresData[gameFeatures.value.slot_has_subtype[0].id].subtypes;
    });
    const gameParentSlots = computed<Slot[]>((): Slot[] => {
        let parentSlots =
            featuresData[gameFeatures.value.slot_has_parent[0].id].parentSlots;

        // find parent slot with at least one active slot in given weekday
        if (gameFeatures.value.book_singular_slot_by_capacity.length) {
            let availableParentSlotIds: number[] = [];
            let slotsData =
                featuresData[
                    gameFeatures.value.book_singular_slot_by_capacity[0].id
                ].slots_data;
            Object.keys(slotsData)
                .filter(
                    (slotItemKey: any) =>
                        slotsData[slotItemKey].active[
                            weekDay(form.start_at) - 1
                        ],
                )
                .forEach((slotItemKey: any) => {
                    let currentParentSlotId =
                        slotsData[slotItemKey].parent_slot_id;
                    if (!availableParentSlotIds.includes(currentParentSlotId)) {
                        availableParentSlotIds.push(currentParentSlotId);
                    }
                });
            parentSlots = parentSlots.filter((parentSlot: Slot) =>
                availableParentSlotIds.includes(parentSlot.id),
            );
        }

        // if selected parent slot is not available after changes unset it
        let selectedParentSlotId =
            form.features[gameFeatures.value.slot_has_parent[0].id][
                'parent_slot_id'
            ];
        if (
            !parentSlots.find(
                (parentSlot: Slot) => parentSlot.id === selectedParentSlotId,
            )
        ) {
            form.features[gameFeatures.value.slot_has_parent[0].id][
                'parent_slot_id'
            ] = null;
        }

        // if there is only one available parent slot select it automatically
        if (parentSlots.length === 1) {
            form.features[gameFeatures.value.slot_has_parent[0].id][
                'parent_slot_id'
            ] = parentSlots[0].id;
        }

        return parentSlots;
    });
    const availableGameSlotsCapacities = computed<number[]>((): number[] => {
        let availableCapacities: number[] = [];
        let slotsData =
            featuresData[
                gameFeatures.value.book_singular_slot_by_capacity[0].id
            ].slots_data;

        // load only capacities available in selected parent slot
        Object.keys(slotsData)
            .filter(
                (slotItemKey: any) =>
                    slotsData[slotItemKey].active[weekDay(form.start_at) - 1],
            )
            .forEach((slotItemKey: any) => {
                let selectedParentSlotId =
                    form.features[gameFeatures.value.slot_has_parent[0].id]
                        .parent_slot_id;
                let currentParentSlotId = slotsData[slotItemKey].parent_slot_id;
                let currentCapacity = slotsData[slotItemKey].capacity;
                if (
                    selectedParentSlotId &&
                    currentParentSlotId === selectedParentSlotId
                ) {
                    if (!availableCapacities.includes(currentCapacity)) {
                        availableCapacities.push(parseInt(currentCapacity));
                    }
                }
            });

        // if selected capacity is not available in selected parent slot unset capacity
        let selectedCapacity =
            form.features[
                gameFeatures.value.book_singular_slot_by_capacity[0].id
            ].capacity;
        if (!availableCapacities.includes(selectedCapacity)) {
            form.features[
                gameFeatures.value.book_singular_slot_by_capacity[0].id
            ].capacity = null;
        }

        return availableCapacities.sort();
    });
    const availableGameSlotsConveniences = computed<number[]>((): number[] => {
        let result: number[] = [];
        Object.values(gameFeatures.value.slot_has_convenience).forEach(
            (slotConvenience: Feature) => {
                if (featuresData[slotConvenience.id].available) {
                    result.push(slotConvenience.id);
                }
            },
        );
        return result;
    });
    const gameSlotsCount = computed<number>(() => {
        return (props.gamesData as { [gameId: number]: any })[form.game_id ?? 1]
            .slots_count;
    });
    const gameDateWidgetSlotsMinLimit = computed<number>(() => {
        let limit = 1;
        return limit;
    });
    const gameDateWidgetSlotsMaxLimit = computed<number>(() => {
        let limit = 100;

        if (gameFeatures.value.person_as_slot.length) {
            limit = 10;
            if (
                form.features[gameFeatures.value.slot_has_parent[0].id][
                    'parent_slot_id'
                ]
            ) {
                limit =
                    featuresData[gameFeatures.value.person_as_slot[0].id][
                        'parent_slots_capacities'
                    ][
                        form.features[gameFeatures.value.slot_has_parent[0].id][
                            'parent_slot_id'
                        ]
                    ]?.[weekDay(form.start_at) - 1] ?? 0;
            }
        }

        let gameWidgetSlotsLimit = settings[
            getSettingKey(
                'has_widget_slots_limit_setting',
                'widget_slots_limit',
            ) ?? ''
        ]?.['value'] as any[];
        gameWidgetSlotsLimit =
            gameWidgetSlotsLimit?.[
                weekDay(
                    date.value ?? form.start_at?.split(' ')?.[0] ?? dayjs(),
                ) - 1
            ];

        if (
            gameWidgetSlotsLimit !== undefined &&
            parseInt(gameWidgetSlotsLimit) < limit
        ) {
            limit = parseInt(gameWidgetSlotsLimit);
        }

        if (gameSlotsCount.value && gameSlotsCount.value < limit) {
            limit = gameSlotsCount.value;
        }

        if (
            gameFeatures.value.has_slot_people_limit_settings?.length &&
            gameFeatures.value.price_per_person?.length &&
            (settings[
                getSettingKey('price_per_person', 'price_per_person_type')
            ]?.['value'] ??
                0)
        ) {
            limit = Math.min(
                Math.floor(
                    form.features[gameFeatures.value.price_per_person[0].id][
                        'person_count'
                    ] /
                        settings[
                            getSettingKey(
                                'has_slot_people_limit_settings',
                                'slot_people_min_limit',
                            )
                        ].value,
                ),
                limit,
            );
        }

        if (limit < form.slots_count) {
            form.slots_count = limit;
        }

        return limit;
    });
    const gameDateSlotPeopleMinLimit = computed<number>(() => {
        if (
            settings[
                getSettingKey('price_per_person', 'price_per_person_type')
            ]?.['value'] ??
            0
        ) {
            let result =
                (settings[
                    getSettingKey(
                        'has_slot_people_limit_settings',
                        'slot_people_min_limit',
                    ) ?? ''
                ]?.['value'] as number) ?? 1;
            return Math.max(result, 1);
        }
    });
    const gameDateSlotPeopleMaxLimit = computed<number>(() => {
        if (
            settings[
                getSettingKey('price_per_person', 'price_per_person_type')
            ]?.['value'] ??
            0
        ) {
            return (
                (settings[
                    getSettingKey(
                        'has_slot_people_limit_settings',
                        'slot_people_max_limit',
                    ) ?? ''
                ]?.['value'] as number) ?? 1000
            );
        }
        return 100;
    });
    interface TranslationsContainer {
        [translationKey: string]: string;
    }
    const gameTranslations = computed<TranslationsContainer>(
        (): TranslationsContainer => {
            return (
                props.gamesTranslations as {
                    [locale: string]: {
                        [gameId: number]: TranslationsContainer;
                    };
                }
            )[currentLocale.value][form.game_id ?? 1];
        },
    );

    let formDictionary = getFormDictionary({
        game_id: club.games?.length === 1 ? club.games[0].id : null,
        'notification.mail': true,
        start_at: dayjs().format('YYYY-MM-DD'),
    });
    club.games?.forEach((game: Game) => {
        formDictionary = passGameFeatureKeysToFormDictionary(
            formDictionary,
            getFeaturesByGame(game),
        );
    });
    let form = useForm(JSON.parse(JSON.stringify(formDictionary)), {
        preserveScroll: false,
    });

    let pusher: any = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    });
    let channel = null;
    if (props.channel) {
        channel = pusher.subscribe(props.channel as string);
    }

    const isStartAtRequired = computed<boolean>(() => {
        return (
            parseInt(
                gameFeatures.value.fixed_reservation_duration?.[0]?.value,
            ) !== 24
        );
    });

    const alertContent = ref<string>('');
    function closeAlert(): void {
        alertContent.value = '';
    }

    const displayedClubMapUrl = ref<string>('');
    function closeMap(): void {
        displayedClubMapUrl.value = '';
    }
    function showMap(): void {
        displayedClubMapUrl.value = ('/club-assets/maps/' +
            settings[getSettingKey('has_map_setting', 'club_map') ?? '']
                ?.value) as string;
    }

    const steps = ref<string[]>(
        (props?.availableSteps as string[] as string[]) ?? [],
    );
    const currentStep = ref<string>(steps.value[0]);
    const currentStepIndex = computed<number>(() =>
        steps.value.indexOf(currentStep.value),
    );
    const currentStepLabel = computed(() => {
        if (routingStore.stepIndex === 0) {
            return wTrans('widget.book-long').value;
        }

        return gameNames.value[form.game_id ?? '0'];
    });
    const stepAccess: Record<string, ComputedRef<boolean>> = {
        details: computed(() => {
            return !!form.game_id;
        }),
        time: computed(() => {
            return (
                !!date.value &&
                form.duration &&
                ((gameFeatures.value.slot_has_parent &&
                    gameFeatures.value.slot_has_parent.length === 0) ||
                    form['features'][gameFeatures.value.slot_has_parent[0].id][
                        'parent_slot_id'
                    ]) &&
                ((gameFeatures.value.book_singular_slot_by_capacity &&
                    gameFeatures.value.book_singular_slot_by_capacity.length ===
                        0) ||
                    form['features'][
                        gameFeatures.value.book_singular_slot_by_capacity[0].id
                    ]['capacity']) &&
                ((gameFeatures.value.slot_has_type &&
                    gameFeatures.value.slot_has_type.length === 0) ||
                    form['features'][gameFeatures.value.slot_has_type[0].id][
                        'name'
                    ] !== null) &&
                ((gameFeatures.value.slot_has_subtype &&
                    gameFeatures.value.slot_has_subtype.length === 0) ||
                    form['features'][gameFeatures.value.slot_has_subtype[0].id][
                        'name'
                    ] !== null)
            );
        }),
        sets: computed(() => {
            return (
                (datetimeBlocks.value.length &&
                    datetimeBlocks.value.includes(form.start_at)) ||
                (showingStatuses.value['fullDayReservations'] &&
                    !isStartAtRequired.value &&
                    date.value !== null)
            );
        }),
        summary: computed(() => {
            return (
                (datetimeBlocks.value.length &&
                    datetimeBlocks.value.includes(form.start_at)) ||
                (showingStatuses.value['fullDayReservations'] &&
                    !isStartAtRequired.value &&
                    date.value !== null)
            );
        }),
    };
    const nextStepAccess = computed<boolean>(() => {
        return steps.value[currentStepIndex.value + 1]
            ? stepAccess[steps.value[currentStepIndex.value + 1]]?.value
            : false;
    });
    function previousStep(): void {
        currentStep.value =
            steps.value[Math.max(0, currentStepIndex.value - 1)];
        if (
            currentStep.value === 'sets' &&
            sets.value.filter((set: Set) => (set.available ?? 0) > 0).length ===
                0
        ) {
            currentStep.value = steps.value[currentStepIndex.value - 1];
        }
        if (currentStep.value === 'time' && !isStartAtRequired.value) {
            currentStep.value =
                steps.value[Math.max(0, currentStepIndex.value - 1)];
        }
        if (currentStep.value === 'game') {
            resetForm();
        }
        if (currentStep.value === 'details') {
            form.start_at = date.value + ' 00:00:00';
        }
        logoutUnverifiedUser();
    }

    // If the client has already clicked the transition to a step with time,
    // and this operation was not possible and was moved with the settimeout function,
    // then we do not allow the next step function to be performed again until the loading is complete.
    let timeStepLoading: boolean = false;
    function nextStep(): void {
        // do not allow going to time step if start at dates are loading at the moment
        if (
            steps.value[currentStepIndex.value + 1] === 'time' &&
            (startAtDatesLastLoadingProcess.value ||
                startAtDatesLoadingStatus.value)
        ) {
            timeStepLoading = true;
            setTimeout(nextStep, 100);
        } else {
            timeStepLoading = false;
            if (steps.value[currentStepIndex.value + 1]) {
                currentStep.value = steps.value[currentStepIndex.value + 1];
            }
            if (
                currentStep.value === 'time' &&
                !isStartAtRequired.value &&
                datetimeBlocks.value.length
            ) {
                currentStep.value = steps.value[currentStepIndex.value + 1];
            }
            if (
                currentStep.value === 'sets' &&
                sets.value.filter((set: Set) => (set.available ?? 0) > 0)
                    .length === 0
            ) {
                currentStep.value = steps.value[currentStepIndex.value + 1];
            }
        }
    }

    function firstStep(): void {
        price.value = null;
        loadAvailableStartAtDatesTriggerer.value++;
        routingStore.firstStep();
        selectedPaymentMethodOnlineStatus.value = false;
        reservationFinished.value = false;
        logoutUnverifiedUser();
        resetForm();
    }

    interface SettingsContainer {
        [settingKey: string]: SettingEntity;
    }

    const announcementContent = ref<string | null>(null);
    const parentSlot = ref<Slot | null>(null);

    const customer = ref<Customer | null>(props.customer as Customer);
    const customerSmsLimitReached = ref<boolean>(false);

    const customerOnlineActiveReservationsCount = computed<number>(() => {
        if (customer.value === null) {
            return 1;
        } else if (club.customer_registration_required) {
            return customer.value.online_active_reservations_count as number;
        } else {
            return props.customer_online_active_reservations_count as number;
        }
    });

    const customerOfflineTodayReservationsCount = computed<number>(() => {
        if (customer.value === null) {
            return 1;
        } else if (club.customer_registration_required) {
            return (customer.value.offline_today_reservations_counts[
                form.game_id ?? 1
            ] ?? 0) as number;
        } else {
            return props.offline_today_reservations_count as number;
        }
    });
    const agreements: Agreement[] = props.agreements as Agreement[];

    const sets = computed<Set[]>(
        () =>
            club.sets?.map((set) => {
                set.available = 0;
                set.selected = 0;

                return set;
            }) || [],
    );

    const price = ref<number | null>(null);
    const oldPrice = ref<number | null>(null);
    const priceLoadedStatus = ref<boolean>(false);
    const priceLoadingStatus = ref<boolean>(false);
    const finalPriceValue = computed<number>(() => {
        return sets.value.reduce(
            (prev, { price, selected }) =>
                prev + (price * Math.max(selected ?? 0, 0) ?? 0),
            price.value,
        );
    });
    const finalPrice = computed(() =>
        finalPriceValue.value ? finalPriceValue.value : 0,
    );

    const date = ref<string | null>(dayjs().format('YYYY-MM-DD'));

    const specialOffer = computed(
        () =>
            club.specialOffers?.find(
                (specialOffer) => specialOffer.id === form.special_offer_id,
            ) ?? null,
    );

    let durationStep = 60;
    let key = getSettingKey(null, 'full_hour_start_reservations_status');
    if (key !== null) {
        durationStep = Math.max(settings[key].value ? 60 : 30, 30);
    }

    const gameDateWidgetDurationLimit = computed<number>(() => {
        let settingKey: string = getSettingKey(
            'has_widget_duration_limit_setting',
            'widget_duration_limit',
        );
        let limit: any = settings[settingKey]?.value?.[weekDay(date.value) - 1];

        if (limit === null || limit === undefined) {
            limit = 5;
        } else {
            limit = parseInt(limit);
        }

        if (form.duration > limit * 60) {
            form.duration = limit * 60;
        }
        return limit * 60;
    });

    const gameDateWidgetDurationLimitMinimum = computed<number>(() => {
        const settingKey = getSettingKey(
            'has_widget_duration_limit_minimum_setting',
            'widget_duration_limit_minimum',
        );
        if (settingKey === null || !date.value || !settings[settingKey])
            return 0;
        const limitsPerDay = settings[settingKey].value as [string | number];
        const dayOfWeek = weekDay(date.value) - 1;
        const todayLimit = +limitsPerDay[dayOfWeek];
        if (form.duration < todayLimit * 60) {
            form.duration = todayLimit * 60;
        }
        return todayLimit * 60;
    });

    const cachedFormStartAt = ref('');

    watch(
        date,
        (value) => {
            if (value) {
                cachedFormStartAt.value = value;
            }
        },
        { immediate: true },
    );

    const datetimeBlocks = ref<string[]>([]);
    const freeDay = ref<boolean>(false);
    let startAtDatesLoadingStatus = ref<boolean>(false);
    let startAtDatesLastLoadingProcess = ref<number | null>(null);
    let loadAvailableStartAtDatesTriggerer = ref<number>(0);
    watch(loadAvailableStartAtDatesTriggerer, () => {
        startAtDatesLoadingStatus.value = true;
    });
    watchDebounced(
        loadAvailableStartAtDatesTriggerer,
        () => {
            loadAvailableStartAtDates();
        },
        { debounce: 600, maxWait: 10000 },
    );
    function loadAvailableStartAtDates() {
        price.value = null;
        startAtDatesLoadingStatus.value = true;
        let processId = Math.round(Math.random() * 100000000);
        startAtDatesLastLoadingProcess.value = processId;
        let formTmp = JSON.parse(JSON.stringify(form));
        formTmp['club'] = club;
        formTmp['start_at'] = cachedFormStartAt.value;
        formTmp['game_id'] =
            club.games?.length === 1 ? club.games[0].id : form.game_id;

        axios.get(route('widget.start-at-dates', formTmp)).then((response) => {
            startAtDatesLoadingStatus.value = false;
            if (startAtDatesLastLoadingProcess.value === processId) {
                startAtDatesLastLoadingProcess.value = null;
                datetimeBlocks.value = [];
                response.data.times.forEach((responseDataBlock: number) => {
                    if (
                        specialOffer.value === null ||
                        specialOffer.value?.active_by_default === true ||
                        isStartAtInSpecialOfferTimeRanges(
                            specialOffer.value,
                            responseDataBlock,
                            true,
                            form.duration,
                        )
                    ) {
                        datetimeBlocks.value.push(responseDataBlock);
                    }
                });
            }
            freeDay.value = response.data.isFreeDay == true;
        });
    }

    function selectGame(gameAttr: Game): void {
        form.game_id = gameAttr.id;
        loadAvailableStartAtDatesTriggerer.value++;

        if (
            gameFeatures.value.has_slot_people_limit_settings?.length &&
            gameFeatures.value.price_per_person?.length
        ) {
            form.features[gameFeatures.value.price_per_person[0].id][
                'person_count'
            ] = gameDateSlotPeopleMinLimit.value;
        }
    }

    function selectDatetime(datetimeBlock: string): void {
        discountCode.value = null;
        discountCodeStatus.value = null;
        form.discount_code = null;
        form.start_at = datetimeBlock;
        if (
            specialOffer.value === null ||
            specialOffer.value.active_by_default === true
        ) {
            form.special_offer_id =
                getActiveByDefaultSpecialOfferForGivenTimeBlock(datetimeBlock)
                    ?.id ?? null;
        }
    }
    function selectSpecialOffer(newSpecialOffer: SpecialOffer): void {
        if (form.special_offer_id === newSpecialOffer.id) {
            form.special_offer_id = null;
        } else {
            form.special_offer_id = newSpecialOffer.id;
        }
    }
    let setsLoaded = ref<boolean>(false);
    function reloadSets() {
        setsLoaded.value = false;
        axios
            .get(
                route('widget.sets', {
                    club: club,
                    date: date.value,
                }),
            )
            .then((response) => {
                sets.value.forEach((set) => {
                    let receivedModel = response.data.find(
                        (model: Set) => model.id === set.id,
                    );
                    if (!receivedModel) {
                        set.selected = 0;
                        set.available = 0;
                    } else {
                        set.available =
                            receivedModel['quantity'][
                                weekDay(date.value ?? dayjs()) - 1
                            ] - receivedModel.reservation_slots_count;
                        set.selected = Math.min(
                            set.selected ?? 0,
                            set.available,
                        );
                    }
                });
                setsLoaded.value = true;
            });
    }

    function getActiveByDefaultSpecialOfferForGivenTimeBlock(
        datetimeBlock: string,
    ): SpecialOffer | null {
        return (
            club.specialOffers
                ?.filter(
                    (specialOfferItem: SpecialOffer) =>
                        specialOfferItem.active &&
                        specialOfferItem.active_by_default &&
                        specialOfferItem.game_id === form.game_id &&
                        (specialOfferItem.duration === null ||
                            timeStringToMinutes(specialOfferItem.duration) ===
                                form.duration) &&
                        (specialOfferItem.slots === null ||
                            specialOfferItem.slots === form.slots_count) &&
                        specialOfferItem.active_week_days.includes(
                            weekDay(date.value ?? dayjs().format('YYYY-MM-DD')),
                        ) &&
                        isStartAtInSpecialOfferTimeRanges(
                            specialOfferItem,
                            datetimeBlock,
                            false,
                        ) &&
                        isStartAtInSpecialOfferAppliesDates(
                            specialOfferItem,
                            datetimeBlock,
                        ),
                )
                ?.sort((specialOfferA, specialOfferB) => {
                    return specialOfferB.value - specialOfferA.value;
                })
                ?.at(0) ?? null
        );
    }

    function getSettingKey(
        featureTypeAttr: string | null,
        settingKeyAttr: string,
    ): string | null {
        let matchingKeys = Object.keys(settings).filter((settingKey) => {
            const setting = settings[settingKey];
            const feature: Feature | null = setting?.feature;
            const gameModel = feature?.game;
            const type = feature?.type;

            const matchesFeatureType =
                featureTypeAttr === null || type === featureTypeAttr;
            const matchesGameId =
                featureTypeAttr === null ||
                (gameModel?.id ?? 0) === (form.game_id ?? 1);

            return (
                settingKey.includes(settingKeyAttr) &&
                matchesFeatureType &&
                matchesGameId
            );
        });

        matchingKeys.sort((a, b) => a.length - b.length);

        return matchingKeys[0] || null;
    }

    let slotsUpdateVariablesToWatch = [specialOffer];

    let slotFeatureFilterData = ref<{
        [featureId: number | string]: any;
    }>({});
    (props.games as Game[]).forEach((gameEntity) => {
        gameEntity.features
            ?.filter((feature) => feature.type === 'slot_has_convenience')
            .forEach((feature) => {
                slotFeatureFilterData.value[feature.id] = false;
            });
        gameEntity.features
            ?.filter((feature) => feature.type === 'slot_has_type')
            .forEach((feature) => {
                slotFeatureFilterData.value[feature.id] = null;
            });
        gameEntity.features
            ?.filter((feature) => feature.type === 'slot_has_subtype')
            .forEach((feature) => {
                slotFeatureFilterData.value[feature.id] = null;
            });
        gameEntity.features
            ?.filter((feature) => feature.type === 'slot_has_parent')
            .forEach((feature) => {
                slotFeatureFilterData.value[feature.id] = null;
            });
    });

    const showingStatuses = computed(() => {
        const baseShowingStatuses = {
            fullDayReservations:
                gameFeatures.value.hasOwnProperty(
                    'fixed_reservation_duration',
                ) &&
                gameFeatures.value['fixed_reservation_duration'].length === 1 &&
                (settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_status',
                    ) ?? '0'
                ]?.['value'] as boolean) === true &&
                (settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_value',
                    ) ?? '0'
                ]?.['value'] as number) === 24,
            fixedReservationDuration:
                gameFeatures.value.hasOwnProperty(
                    'fixed_reservation_duration',
                ) &&
                gameFeatures.value['fixed_reservation_duration'].length === 1 &&
                (settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_status',
                    ) ?? '0'
                ]?.['value'] as boolean) === true &&
                (settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_value',
                    ) ?? '0'
                ]?.['value'] as number) > 0 &&
                (settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_value',
                    ) ?? '0'
                ]?.['value'] as number) < 24,
            slotsCount:
                (!gameFeatures.value.hasOwnProperty(
                    'book_singular_slot_by_capacity',
                ) ||
                    gameFeatures.value['book_singular_slot_by_capacity']
                        .length === 0) &&
                (!gameFeatures.value.hasOwnProperty(
                    'book_singular_slot_by_capacity',
                ) ||
                    gameFeatures.value['person_as_slot'].length === 0),
            slotConveniences:
                gameFeatures.value.hasOwnProperty('slot_has_convenience') &&
                gameFeatures.value['slot_has_convenience'].length > 0,
            pricePerPerson:
                gameFeatures.value.hasOwnProperty('price_per_person') &&
                gameFeatures.value['price_per_person'].length > 0 &&
                (settings[
                    getSettingKey(
                        'price_per_person',
                        'price_per_person_type',
                    ) || ''
                ]?.['value'] ??
                    0),
            personAsSlot:
                gameFeatures.value.hasOwnProperty('person_as_slot') &&
                gameFeatures.value['person_as_slot'].length > 0,
            slotHasType:
                gameFeatures.value.hasOwnProperty('slot_has_type') &&
                gameFeatures.value['slot_has_type'].length > 0 &&
                gameSlotTypes.value.length > 1,
            slotHasLounge:
                gameFeatures.value.hasOwnProperty('slot_has_lounge') &&
                gameFeatures.value['slot_has_lounge'].length > 0 &&
                settings[
                    getSettingKey('slot_has_lounge', 'lounges_status') ?? ''
                ]?.['value'] === true,
            slotSelection:
                gameFeatures.value.hasOwnProperty(
                    'has_widget_slots_selection',
                ) &&
                gameFeatures.value['has_widget_slots_selection'].length > 0 &&
                settings[
                    getSettingKey(
                        'has_widget_slots_selection',
                        'slots_selection_status',
                    ) ?? ''
                ]?.['value'] === true,
            slotHasSubType:
                gameFeatures.value.hasOwnProperty('slot_has_subtype') &&
                gameFeatures.value['slot_has_subtype'].length > 0 &&
                form.features[gameFeatures.value['slot_has_type'][0].id][
                    'name'
                ] !== null,
            slotHasSubslots:
                gameFeatures.value.hasOwnProperty('slot_has_parent') &&
                gameFeatures.value['slot_has_parent'].length > 0,
            bookSingularSlotByCapacity:
                gameFeatures.value.hasOwnProperty(
                    'book_singular_slot_by_capacity',
                ) &&
                gameFeatures.value['book_singular_slot_by_capacity'].length > 0,
        };

        const showingStatuses = {
            ...baseShowingStatuses,
            duration:
                !baseShowingStatuses.fixedReservationDuration &&
                !baseShowingStatuses.fullDayReservations,
            slotIds:
                !baseShowingStatuses.slotHasLounge &&
                !baseShowingStatuses.bookSingularSlotByCapacity &&
                !baseShowingStatuses.personAsSlot,
        };

        return showingStatuses as Record<keyof typeof showingStatuses, boolean>;
    });

    const specialOffers = computed<SpecialOffer[] | undefined>(
        (): SpecialOffer[] | undefined => {
            let startAtWeekDay = dayjs(date.value).day();
            let specialOffersCollection: SpecialOffer[] | undefined =
                club.specialOffers?.filter(
                    (specialOffer) =>
                        specialOffer.active &&
                        specialOffer.game_id === form.game_id &&
                        specialOffer.active_by_default === false &&
                        !(
                            specialOffer.applies_default === false &&
                            specialOffer.when_applies.length === 0
                        ),
                );
            if (
                !specialOffersCollection?.find(
                    (specialOfferItem) =>
                        specialOfferItem.id === form.special_offer_id,
                )
            ) {
                form.special_offer_id = null;
            }
            return specialOffersCollection;
        },
    );

    const availableDates = computed<string[]>(() => {
        let result: string[] = [];
        for (
            let i = props.canMakePreviousDateReservation ? -1 : 0;
            i <=
            parseInt(
                (settings['reservation_max_advance_time'].value as string) ?? 0,
            );
            i++
        ) {
            let currentDate: Dayjs = dayjs().add(i, 'day');
            if (
                specialOffer.value === null ||
                (specialOffer.value.active_week_days.includes(
                    weekDay(currentDate),
                ) &&
                    getSpecialOfferAppliesStatus(
                        specialOffer.value,
                        currentDate.format('YYYY-MM-DD'),
                    ))
            ) {
                result.push(currentDate.format('YYYY-MM-DD'));
            }
        }
        return result;
    });

    watch(availableDates, () => {
        if (
            !availableDates.value.includes(
                date?.value ?? dayjs().format('YYYY-MM-DD'),
            ) &&
            availableDates.value.length
        ) {
            date.value = availableDates.value[0];
        }
    });

    const specialOfferDuration = computed<number | null>(() => {
        if (specialOffer.value && specialOffer.value.duration) {
            form.duration = timeStringToMinutes(specialOffer.value.duration);
            return form.duration;
        }
        return null;
    });
    const specialOfferSlotsCount = computed<number | null>(() => {
        if (specialOffer.value && specialOffer.value.slots) {
            form.slots_count = specialOffer.value.slots;
            return specialOffer.value.slots;
        }
        return null;
    });

    const durationLabel = computed<string>(() => {
        let duration: number = form.duration;
        if (showingStatuses.value.fixedReservationDuration) {
            duration =
                settings[
                    getSettingKey(
                        'fixed_reservation_duration',
                        'fixed_reservation_duration_value',
                    ) ?? '0'
                ]?.['value'] * 60;
        }
        let hours: number = Math.floor(duration / 60);
        let minutes: number = duration % 60;
        return (
            `${hours}` +
            wTrans('main.hours-postfix').value +
            (!!minutes ? ` ${minutes}m` : '')
        );
    });

    function logoutUnverifiedUser() {
        if (customer.value && customer.value.verified === false) {
            customer.value = null;
        }
    }

    function resetForm() {
        reservationFinished.value = false;
        reservationData.value = null;
        date.value = dayjs().format('YYYY-MM-DD');
        discountCode.value = null;
        discountCodeStatus.value = null;
        Object.keys(sets.value).forEach((setKey: any) => {
            sets.value[setKey].selected = 0;
        });

        if (customer.value) {
            axios
                .get(
                    route('widget.customers.show', {
                        club: club,
                        encryptedCustomerId: customer.value.encryptedId,
                    }),
                )
                .then((response: { data: { customer: Customer } }) => {
                    customer.value = response.data.customer;
                });
        }

        Object.keys(formDictionary['features']).forEach((featureKey) => {
            Object.keys(formDictionary['features'][featureKey]).forEach(
                (settingKey) => {
                    form['features'][featureKey][settingKey] =
                        formDictionary['features'][featureKey][settingKey];
                },
            );
        });

        Object.keys(formDictionary).forEach((key) => {
            if (!['game_id', 'features', 'encryptedCustomerId'].includes(key)) {
                form[key] = formDictionary[key];
            }
        });
        price.value = null;
    }

    function fullFormReset() {
        form.game_id = formDictionary.game_id;

        firstStep();
        resetForm();
        routingStore.firstStep();
    }

    const gameSlots = computed<Slot[]>(() => {});

    let inactiveAlertTriggerer: any = null;
    let inactiveTriggererWatchVariables = [form, currentStep];
    inactiveTriggererWatchVariables.forEach((watchVariable) => {
        watch(watchVariable, () => {
            clearTimeout(inactiveAlertTriggerer);
            inactiveAlertTriggerer = null;
            inactiveAlertTriggerer = setTimeout(() => {
                router.reload({
                    onSuccess: () => {
                        alertContent.value = wTrans(
                            'widget.reservation-expired',
                        ).value;
                        firstStep();
                    },
                });
            }, 3600000);
        });
    });

    //load price and if it differs from previous one, show an alert
    setInterval(async () => {
        if (
            !(currentStep.value === 'summary' && reservationData.value) &&
            !['game', 'details'].includes(currentStep.value) &&
            form.start_at &&
            Date.now() - discountCodeLastChange > 3000
        ) {
            let oldPrice = structuredClone(price.value);
            await reloadPrice();
            if (
                oldPrice &&
                oldPrice !== price.value &&
                priceLastLoadingProcess.value === null
            ) {
                alertContent.value = wTrans('widget.price-changed').value;
            }
        }
    }, 30000);
    if (customer.value) {
        axios
            .get(
                route('widget.customers.show', {
                    club: club,
                    encryptedCustomerId: customer.value.encryptedId,
                }),
            )
            .then((response: { data: { customer: Customer } }) => {
                customer.value = response.data.customer;
            });
    }

    watch(date, () => {
        form.start_at = date.value;

        if (form.duration === 0) {
            form.duration = Math.min(gameDateWidgetDurationLimit.value, 60);
        }

        if (form.game_id) {
            loadAvailableStartAtDatesTriggerer.value++;
        }

        reloadSets();
    });

    let discountCode = ref<DiscountCode | null>(null);
    let discountCodeStatus = ref<boolean | null>(null);
    let discountCodeLastChange = Date.now();

    watch(
        () => form.discount_code,
        () => {
            discountCodeLastChange = Date.now();
        },
    );

    watchDebounced(
        () => form.discount_code,
        () => {
            if (
                form.game_id === null ||
                form.discount_code === null ||
                form.discount_code === ''
            ) {
                discountCode.value = null;
                discountCodeStatus.value = null;
            } else {
                axios
                    .get(
                        route('widget.discount-codes', {
                            club: club.slug,
                            discount_code: form.discount_code,
                            game_id: form.game_id,
                            start_at: form.start_at,
                            duration: form.duration,
                        }),
                    )
                    .then((response: any) => {
                        if (!discountCodeStatus.value) {
                            oldPrice.value = price.value;
                        }

                        discountCode.value = response.data;
                        discountCodeStatus.value = true;

                        reloadPriceTriggerer.value++;
                    })
                    .catch((response: any) => {
                        discountCode.value = null;
                        discountCodeStatus.value = false;
                    });
            }
        },
        { debounce: 1200, maxWait: 3000 },
    );

    function reloadAnnouncementContent() {
        announcementContent.value = null;
        announcements?.forEach((announcement: Announcement) => {
            if (
                announcement.start_at === date.value &&
                announcement.game_id === form.game_id
            ) {
                announcementContent.value = announcement.content;
            }
        });
    }

    const reservationData = ref<Reservation | null>(null);

    const isSubmitting = ref<boolean>(false);
    const selectedPaymentMethodOnlineStatus = ref<boolean>(false);
    const reservationFinished = ref<boolean>(false);
    const paymentTab = ref<any>(null);
    const paymentSubmitted = computed(
        () =>
            selectedPaymentMethodOnlineStatus.value ||
            reservationFinished.value,
    );

    async function store(paymentType: string) {
        if (paymentSubmitted.value) return;

        selectedPaymentMethodOnlineStatus.value = paymentType === 'online';
        reservationFinished.value = paymentType === 'offline';
        if (isSubmitting.value) {
            return false;
        }
        let formData = JSON.parse(JSON.stringify(form));
        formData['club'] = club;
        formData['payment_type'] = paymentType;
        formData['sets'] = sets.value;
        formData['customer'].locale = currentLocale.value;
        formData['customer'].privacyPolicy = formData['customer'].generalTerms

        isSubmitting.value = true;
        fullLoader.value = true;
        await axios
            .post(
                route('widget.store', {
                    club: club.slug,
                }),
                formData,
            )
            .then((response) => {
                if (response.data.errorKey == 101) {
                    alertContent.value = wTrans(
                        'widget.offline-reservation-limit-exceeded',
                    ).value;
                    firstStep();
                    resetForm();
                }
                if (response.data.errorKey == 102) {
                    alertContent.value = wTrans('widget.too-many-seats').value;
                    firstStep();
                    resetForm();
                }
                reservationData.value = response.data.reservationData;
                if (response.data.paymentUrl) {
                    paymentTab.value = window.open(
                        response.data.paymentUrl,
                        '_blank',
                    );
                }
                isSubmitting.value = false;
                fullLoader.value = false;
            })
            .catch((response) => {
                if (
                    Object.keys(response.response.data.errors).includes(
                        'slots_count',
                    ) ||
                    Object.keys(response.response.data.errors).includes(
                        'slot_id',
                    ) ||
                    Object.keys(response.response.data.errors).includes(
                        'slots_ids',
                    )
                ) {
                    alertContent.value =
                        gameTranslations.value[
                            'widget-slots-no-longer-available-error'
                        ];
                    firstStep();
                    resetForm();
                } else {
                    alertContent.value = wTrans('widget.error-alert').value;
                }
                fullLoader.value = false;
                isSubmitting.value = false;
            });
    }

    function isAnnouncementIsDisplayed(announcement: Announcement) {
        const startAtDate: Dayjs = dayjs(announcement.start_at);
        const endAtDate: Dayjs = dayjs(announcement.end_at);
        const selectedDate: Dayjs = dayjs(date.value);

        return selectedDate >= startAtDate && selectedDate <= endAtDate;
    }

    function reloadAnnouncementContent() {
        announcementContent.value = null;
        announcements?.forEach((announcement: Announcement) => {
            if (
                announcement.game_id === form.game_id &&
                isAnnouncementIsDisplayed(announcement)
            ) {
                announcementContent.value = announcement.content;
            }
        });
    }
    const resetSlots = () => {
        if (!isSlotSelectionEnabled.value) return;

        form.slot_ids = [];

        if (form.game_id) {
            form.slots_count = 0;
        }
    };

    watch(
        () => form.game_id,
        () => {
            reloadAnnouncementContent();
        },
    );

    watch(
        () => form.start_at,
        () => {
            reloadAnnouncementContent();
        },
    );

    watch(
        () => form.slots_count,
        () => {
            if (
                gameFeatures.value.price_per_person?.length > 0 &&
                form.slots_count * gameDateSlotPeopleMaxLimit.value <
                    form.features[gameFeatures.value.price_per_person[0].id][
                        'person_count'
                    ]
            ) {
                form.features[gameFeatures.value.price_per_person[0].id][
                    'person_count'
                ] = Math.max(
                    1,
                    form.slots_count * gameDateSlotPeopleMaxLimit.value,
                );
            }
        },
    );

    watch(customer, () => {
        form.encryptedCustomerId = customer.value?.encryptedId ?? null;
    });

    watch(discountCodeStatus, () => {
        reloadPriceTriggerer.value++;
    });

    let priceLastLoadingProcess = ref<number | null>(null);
    const reloadPriceTriggerer = ref(0);
    watchDebounced(
        reloadPriceTriggerer,
        () => {
            priceLastLoadingProcess.value = Math.round(
                Math.random() * 100000000,
            );
            reloadPrice();
        },
        { debounce: 250, maxWait: 10000 },
    );

    async function reloadPrice() {
        if (form.slots_count === 0) {
            price.value = 0;
            return;
        }

        let processId = Math.round(Math.random() * 100000000);
        priceLastLoadingProcess.value = processId;
        priceLoadingStatus.value = true;
        let formData = JSON.parse(JSON.stringify(form));
        formData['club'] = club;
        formData['parent_slot_id'] = 0;
        if (form.start_at && currentStep.value !== 'status') {
            await axios
                .get(route('widget.calculate-price', formData))
                .then((response) => {
                    price.value = response.data.finalPrice;
                    priceObject.value = response.data;
                })
                .catch(
                    (response: { response: { data: { errors: Object } } }) => {
                        let errors: Object = response.response.data.errors;
                    },
                );
            if (priceLastLoadingProcess.value === processId) {
                priceLastLoadingProcess.value = null;
                priceLoadingStatus.value = false;
            }
        }
    }

    watch(
        () => [form.start_at, form.duration, form.slots_count],
        () => {
            if (form.duration === 0 || form.start_at.split(' ').length === 2) {
                reloadPriceTriggerer.value++;
            }
        },
    );

    slotsUpdateVariablesToWatch.forEach((variable) => {
        watch(variable, () => {
            if (specialOffer.value.active_by_default === false) {
                loadAvailableStartAtDatesTriggerer.value++;
            }
        });
    });

    watch(
        () => form.slots_count,
        () => {
            if (gameFeatures.value.person_as_slot?.length) {
                form.features[gameFeatures.value['person_as_slot'][0].id][
                    'persons_count'
                ] = form.slots_count;
            }
        },
    );

    if (club.games?.length === 1) {
        loadAvailableStartAtDatesTriggerer.value++;
    }

    function setLocale(locale: string) {
        currentLocale.value = locale;

        void axios.post(
            route('widget.set-country', { locale, club: props.club }),
        );
        void loadLanguageAsync(locale);
    }

    function hasSetMap() {
        return (
            gameFeatures.value.has_map_setting.length &&
            settings[getSettingKey('has_map_setting', 'club_map') ?? '']?.[
                'value'
            ]
        );
    }

    function getCurrencySymbol() {
        return currencySymbols[currency];
    }

    function calculateSetsPrice() {
        return finalPrice.value - (price.value ?? 0);
    }

    const availableSets = computed(() =>
        sets.value.filter(({ available }) => available && available > 0),
    );

    const slotHasSubtypeOptions = computed<{ subtype: string }[]>(() =>
        featuresData[gameFeatures.value.slot_has_subtype[0].id].subtypes.filter(
            (model: { type: string; subtype: string }) =>
                form.features[gameFeatures.value.slot_has_type[0].id].name ===
                    null ||
                model.type ===
                    form.features[gameFeatures.value.slot_has_type[0].id].name,
        ),
    );

    function selectType(type: string) {
        const featureId = gameFeatures.value.slot_has_type[0].id;
        const subtypeFeatureId = gameFeatures.value.slot_has_subtype[0].id;

        if (form.features[featureId].name !== type && type !== null) {
            form.features[subtypeFeatureId].name = null;
        }

        form.features[featureId].name = type;

        if (slotHasSubtypeOptions.value.length === 1) {
            form.features[subtypeFeatureId].name =
                slotHasSubtypeOptions.value[0].subtype;
        }
    }

    const slots = ref<Slot[]>([]);

    watch(
        form,
        async () => {
            const { data } = await axios.get<Slot[]>(
                route('widget.slots', { club: club.slug, ...form }),
            );

            slots.value = data;
        },
        { immediate: true },
    );

    const openingHoursLoadingStatus = ref(false);

    const openingHours = computedAsync(async () => {
        openingHoursLoadingStatus.value = true;

        const { data } = await axios.get<DateOpeningHours>(
            route('widget.opening-hours', {
                date: formatCalendarDate(cachedFormStartAt.value),
                club,
            }),
        );

        openingHoursLoadingStatus.value = false;

        return data;
    }, null);

    function getCountryLocaleCode(country: Country | string) {
        let countryModel: Country | undefined;
        if (typeof country === 'string') {
            countryModel = countries.find(
                (countryItem) => countryItem.code === country,
            );
        } else {
            countryModel = country;
        }
        if (countryModel?.code === 'GB') {
            return 'en';
        }
        return countryModel?.code?.toLowerCase() ?? 'en';
    }

    const resendCode = ref<boolean>(false);

    return {
        props,
        gameSlotTypes,
        gameSlotSubtypes,
        form,
        channel,
        priceLoadedStatus,
        closeAlert,
        agreements,
        availableDates,
        specialOfferDuration,
        specialOfferSlotsCount,
        club,
        address,
        displayedClubMapUrl,
        customerOnlineActiveReservationsCount,
        customerOfflineTodayReservationsCount,
        showMap,
        closeMap,
        alertContent,
        datetimeBlocks,
        freeDay,
        gameDateWidgetSlotsMaxLimit,
        gameDateWidgetSlotsMinLimit,
        discountCodeStatus,
        discountCode,
        globalSettings,
        selectSpecialOffer,
        selectDatetime,
        selectGame,
        reloadSets,
        isStartAtInSpecialOfferTimeRanges,
        getActiveByDefaultSpecialOfferForGivenTimeBlock,
        gameDateSlotPeopleMinLimit,
        gameDateSlotPeopleMaxLimit,
        availableGameSlotsCapacities,
        gameParentSlots,
        parentSlot,
        price,
        finalPrice,
        customer,
        customerSmsLimitReached,
        steps,
        currentStep,
        currentStepIndex,
        currentStepLabel,
        stepAccess,
        nextStepAccess,
        announcements,
        specialOffers,
        setsLoaded,
        weekDay,
        sets,
        gameTranslations,
        date,
        specialOffer,
        announcementContent,
        reloadAnnouncementContent,
        durationLabel,
        nextStep,
        previousStep,
        firstStep,
        getSettingKey,
        settings,
        currency,
        widgetColor,
        durationStep,
        gameDateWidgetDurationLimit,
        gameFeatures,
        showingStatuses,
        currencySymbols,
        store,
        isSubmitting,
        reservationData,
        availableGameSlotsConveniences,
        currentCountry,
        currentLocale,
        gameNames,
        selectedPaymentMethodOnlineStatus,
        reservationFinished,
        generalTranslations,
        paymentTab,
        priceLoadingStatus,
        startAtDatesLastLoadingProcess,
        featuresData,
        isStartAtRequired,
        startAtDatesLoadingStatus,
        availableSets,
        slotHasSubtypeOptions,
        slots,
        cachedFormStartAt,
        openingHoursLoadingStatus,
        openingHours,
        loadAvailableStartAtDatesTriggerer,
        reloadPriceTriggerer,
        isSlotSelectionEnabled,
        oldPrice,
        fullFormReset,
        setLocale,
        hasSetMap,
        getCurrencySymbol,
        calculateSetsPrice,
        resetForm,
        selectType,
        resetSlots,
        logoutUnverifiedUser,
        getCountryLocaleCode,
        paymentSubmitted,
        resendCode,
        fullLoader,
        priceObject,
        gameDateWidgetDurationLimitMinimum,
    };
});
