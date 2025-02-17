import { Customer, Feature, Game, Slot, SpecialOffer } from '@/Types/models';
import axios from 'axios';
import dayjs, { Dayjs } from 'dayjs';

export function useReservations() {
    const featureTypes: string[] = [
        'price_per_person',
        'fixed_reservation_duration',
        'full_day_reservations',
        'has_map_setting',
        'has_timers',
        'has_custom_views',
        'has_price_announcements_settings',
        'has_widget_slots_selection',
        'has_slot_people_limit_settings',
        'person_as_slot',
        'slot_has_parent',
        'slot_has_lounge',
        'slot_has_type',
        'slot_has_subtype',
        'slot_has_convenience',
        'slot_has_active_status_per_weekday',
        'parent_slot_has_online_status',
        'reservation_slot_has_display_name',
        'book_singular_slot_by_capacity',
        'reservation_slot_has_occupied_status',
        'slot_has_bulb',
    ];
    type FeatureMap = {
        [featureType: string]: Feature[];
    };

    function getFeaturesByGame(game: Game): FeatureMap {
        return featureTypes.reduce<FeatureMap>((acc, type) => {
            acc[type] =
                //@ts-ignore
                game.features?.filter((feature) => feature.type === type) ?? [];
            return acc;
        }, {});
    }

    function getFeaturesBySlot(slot: Slot): FeatureMap {
        return featureTypes.reduce<FeatureMap>((acc, type) => {
            acc[type] =
                //@ts-ignore
                slot.features?.filter((feature) => feature.type === type) ?? [];
            return acc;
        }, {});
    }

    function getFormWithFilledCustomer(form: any, customer: Customer) {
        form.customer.first_name = customer.first_name as string;
        form.customer.last_name = customer.last_name as string;
        form.customer.email = customer.email as string;
        form.customer.phone = customer.phone as string;
        form.customer.id = customer.id;
        if (customer.tags) {
            form.customer.tags = customer.tags.map(function (value, index) {
                return value['name'];
            });
        }
        form.anonymous_reservation = false;
        return form;
    }

    const currencySymbols: Record<string, string> = {
        EUR: '€',
        USD: '$',
        GBP: '£',
        PLN: 'zł',
        UAH: '₴',
        DKK: 'kr',
    };

    function passGameFeatureKeysToFormDictionary(
        formDictionary: Record<string, any>,
        gameFeatures: FeatureMap,
    ): Record<string, any> {
        gameFeatures?.price_per_person?.forEach((feature) => {
            formDictionary['features'][feature.id] = {
                person_count: 1,
            };
        });
        gameFeatures?.reservation_slot_has_display_name?.forEach((feature) => {
            formDictionary['features'][feature.id] = {
                display_name: '',
            };
        });
        gameFeatures?.person_as_slot?.forEach((feature) => {
            formDictionary['features'][feature.id] = {
                persons_count: 1,
                new_parent_slot_id: 1,
            };
        });
        gameFeatures?.book_singular_slot_by_capacity?.forEach((feature) => {
            formDictionary['features'][feature.id] = { capacity: null };
        });
        gameFeatures?.fixed_reservation_duration?.forEach((feature) => {
            formDictionary['features'][feature.id] = { empty: true };
        });
        gameFeatures?.parent_slot_has_online_status?.forEach((feature) => {
            formDictionary['features'][feature.id] = { empty: true };
        });
        gameFeatures?.slot_has_type?.forEach((feature) => {
            formDictionary['features'][feature.id] = { name: null };
        });
        gameFeatures?.slot_has_subtype?.forEach((feature) => {
            formDictionary['features'][feature.id] = { name: null };
        });
        gameFeatures?.slot_has_parent?.forEach((feature) => {
            formDictionary['features'][feature.id] = { parent_slot_id: null };
        });
        gameFeatures?.slot_has_convenience?.forEach((feature) => {
            formDictionary['features'][feature.id] = { status: false };
        });
        gameFeatures?.slot_has_active_status_per_weekday?.forEach((feature) => {
            formDictionary['features'][feature.id] = { status: false };
        });
        gameFeatures?.slot_has_bulb?.forEach((feature) => {
            formDictionary['features'][feature.id] = { type: 'nothing' };
        });
        return formDictionary;
    }

    function timeStringToMinutes(timeString: string): number {
        let timeStringParts = timeString.split(':');
        return parseInt(timeStringParts[0]) * 60 + parseInt(timeStringParts[1]);
    }

    function formatInputPrice(price: string | number): string {
        if (typeof price !== 'string') {
            price = price.toFixed(2);
        }
        return price.replace('.', ',');
    }

    async function calculatePrice(
        form: any,
        customPrice: number,
        data: { [key: string]: any },
    ): Promise<{
        price: number | null;
        form: any;
        result: number | null;
        setsPrice: number | null;
    }> {
        let price: number | null = null;
        let result: number | null = null;
        let setsPrice: number | null = null;

        let formTmp: Record<string, any> = JSON.parse(JSON.stringify(form));
        if (customPrice) {
            if (formTmp['price']) {
                formTmp['price'] = formTmp['price'];
            }
        } else {
            delete formTmp['price'];
        }

        Object.keys(data).forEach((key) => {
            formTmp[key] = data[key];
        });

        let newErrors = {};
        try {
            const response = await axios.get(
                route('club.reservations.calculate-price', formTmp),
            );
            price = response.data['basePrice'];
            result = response.data['finalPrice'];
            setsPrice = response.data['setsPrice'];
            form.errors = {};
            return { form, price, result, setsPrice };
        } catch (error: any) {
            if (error.response && error.response.status === 422) {
                const validationErrors: Object = error.response.data.errors;
                newErrors = Object.fromEntries(
                    Object.entries(validationErrors).map(([key, value]) => [
                        key,
                        value[0],
                    ]),
                );
            }
            form.errors = newErrors;
            return { form, price, result, setsPrice };
        }
    }

    interface TimeRange {
        from: string;
        to: string;
    }

    function getSpecialOfferAppliesStatus(
        specialOffer: SpecialOffer,
        date: string,
    ): boolean {
        const formStartAt: Dayjs = dayjs(date);

        if (specialOffer.applies_default) {
            const exclusionApplies: boolean =
                specialOffer.when_not_applies.some(
                    (exclusionRange: TimeRange) => {
                        const from = dayjs(exclusionRange['from']);
                        const to = dayjs(exclusionRange['to']);

                        return (
                            from.isBefore(formStartAt.add(1, 'day')) &&
                            to.isAfter(formStartAt.subtract(1, 'day'))
                        );
                    },
                );

            return !exclusionApplies;
        } else {
            return specialOffer.when_applies.some((inclusionRange) => {
                const from = dayjs(inclusionRange['from']);
                const to = dayjs(inclusionRange['to']);

                return (
                    from.isBefore(formStartAt.add(1, 'day')) &&
                    to.isAfter(formStartAt.subtract(1, 'day'))
                );
            });
        }
    }

    function weekDay(date: string | Dayjs) {
        if (typeof date === 'string') {
            date = dayjs(date);
        }
        let weekDay = date.day();
        return weekDay === 0 ? 7 : weekDay;
    }

    function isStartAtInSpecialOfferTimeRanges(
        specialOffer: SpecialOffer,
        date: string,
        fullReservationDiscount = false,
        duration = 60,
    ): boolean {
        let startAtDayjsInstance: Dayjs = dayjs(date);
        let endAtDayjsInstance: Dayjs = startAtDayjsInstance;
        if (fullReservationDiscount) {
            endAtDayjsInstance = endAtDayjsInstance.add(duration, 'minute');
        } else {
            endAtDayjsInstance = endAtDayjsInstance.add(duration, 'minute');
        }

        let specialOfferTimeRanges: TimeRange[] =
            specialOffer.time_range[specialOffer.time_range_type];

        let result: boolean = !specialOfferTimeRanges.length;

        specialOfferTimeRanges.forEach((specialOfferTimeRange: TimeRange) => {
            let specialOfferTimeRangeFromDayjsInstance = dayjs(
                startAtDayjsInstance.format('YYYY-MM-DD') +
                    ' ' +
                    specialOfferTimeRange['from'],
            );
            let specialOfferTimeRangeToDayjsInstance = dayjs(
                startAtDayjsInstance.format('YYYY-MM-DD') +
                    ' ' +
                    specialOfferTimeRange['to'],
            );
            if (!result) {
                if (specialOffer.time_range_type === 'start') {
                    if (
                        specialOffer.active_week_days.includes(
                            weekDay(startAtDayjsInstance),
                        ) &&
                        specialOfferTimeRangeFromDayjsInstance.isBefore(
                            startAtDayjsInstance.add(1, 'minute'),
                        ) &&
                        specialOfferTimeRangeToDayjsInstance.isAfter(
                            startAtDayjsInstance.subtract(1, 'minute'),
                        )
                    ) {
                        result = true;
                    } else {
                        result = false;
                    }
                } else {
                    if (
                        specialOffer.active_week_days.includes(
                            weekDay(startAtDayjsInstance),
                        )
                    ) {
                        if (specialOffer.active_by_default) {
                            if (
                                specialOfferTimeRangeFromDayjsInstance.isBefore(
                                    startAtDayjsInstance.add(1, 'minute'),
                                ) &&
                                specialOfferTimeRangeToDayjsInstance.isAfter(
                                    endAtDayjsInstance.subtract(
                                        duration - 1,
                                        'minute',
                                    ),
                                )
                            ) {
                                result = true;
                            } else {
                                result = false;
                            }
                        } else {
                            if (
                                specialOfferTimeRangeFromDayjsInstance.isBefore(
                                    startAtDayjsInstance.add(1, 'minute'),
                                ) &&
                                specialOfferTimeRangeToDayjsInstance.isAfter(
                                    endAtDayjsInstance.subtract(1, 'minute'),
                                )
                            ) {
                                result = true;
                            } else {
                                result = false;
                            }
                        }
                    } else {
                        result = false;
                    }
                }
            }
        });

        return result;
    }

    function isStartAtInSpecialOfferAppliesDates(
        specialOffer: SpecialOffer,
        datetimeBlock: string,
    ): boolean {
        if (specialOffer.applies_default) {
            let result = true;
            if (specialOffer.when_not_applies.length) {
                specialOffer.when_not_applies.forEach(
                    (whenNotAppliesItem: { from: string; to: string }) => {
                        let whenNotAppliesItemDayjsInstanceFrom = dayjs(
                            whenNotAppliesItem.from,
                        );
                        let whenNotAppliesItemDayjsInstanceTo = dayjs(
                            whenNotAppliesItem.to,
                        );
                        if (
                            whenNotAppliesItemDayjsInstanceFrom
                                .subtract(1, 'day')
                                .isBefore(dayjs(datetimeBlock)) &&
                            whenNotAppliesItemDayjsInstanceTo
                                .add(1, 'day')
                                .isAfter(dayjs(datetimeBlock))
                        ) {
                            result = false;
                        }
                    },
                );
            }
            return result;
        } else {
            let result = false;
            specialOffer.when_applies.forEach(
                (whenAppliesItem: { from: string; to: string }) => {
                    let whenAppliesItemDayjsInstanceFrom = dayjs(
                        whenAppliesItem.from,
                    );
                    let whenAppliesItemDayjsInstanceTo = dayjs(
                        whenAppliesItem.to,
                    );
                    if (
                        whenAppliesItemDayjsInstanceFrom
                            .subtract(1, 'day')
                            .isBefore(dayjs(datetimeBlock)) &&
                        whenAppliesItemDayjsInstanceTo
                            .add(1, 'day')
                            .isAfter(dayjs(datetimeBlock))
                    ) {
                        result = true;
                    }
                },
            );
            return result;
        }
    }

    function getFormDictionary(data: Record<string, any>): Record<string, any> {
        return {
            customer: {
                id: null,
                first_name: '',
                last_name: '',
                phone: '',
                email: '',
                locale: null,
            },
            custom_price: data['custom_price'] ?? false,
            game_id: data['game_id'] ?? null,
            status: data['status'] ?? 0,
            price: 0,
            start_at: data['start_at'] ?? null,
            slot_ids: [null],
            slots_count: data['slots_count'] ?? 1,
            occupied_status: data['occupied_status'] ?? true,
            duration: data['duration'] ?? 60,
            payment_type: 'offline',

            reservation_type_id: null,
            discount_code_id: null,
            discount_code: null,
            special_offer_id: null,

            anonymous_reservation: false,
            club_reservation: false,

            customer_note: '',
            show_customer_note_on_calendar: false,

            club_note: '',
            show_club_note_on_calendar: false,

            apply_to_all_reservations: false,

            notification: {
                sms: false,
                mail: data['notification.mail'] ?? false,
            },

            features: [],
        };
    }

    return {
        featureTypes,
        getFeaturesByGame,
        getFeaturesBySlot,
        getFormWithFilledCustomer,
        currencySymbols,
        passGameFeatureKeysToFormDictionary,
        timeStringToMinutes,
        formatInputPrice,
        getSpecialOfferAppliesStatus,
        isStartAtInSpecialOfferTimeRanges,
        isStartAtInSpecialOfferAppliesDates,
        calculatePrice,
        weekDay,
        getFormDictionary,
    };
}
