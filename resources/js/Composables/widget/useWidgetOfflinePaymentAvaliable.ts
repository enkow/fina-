import dayjs from 'dayjs';
import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

export function useWidgetOfflinePaymentAvaliable() {
    const widgetStore = useWidgetStore();

    const currentWeekDay = computed<number>(() => {
        return widgetStore.weekDay(widgetStore.date ?? dayjs());
    });

    const offlineReservationSlotLimit = computed<number | null>(() => {
        let result = null;

        const offlineSlotLimitKey =
            widgetStore.getSettingKey(
                'has_offline_reservation_limits_settings',
                'offline_reservation_slot_limit',
            ) || '';
        const clubSetting = widgetStore.settings[offlineSlotLimitKey]?.value;

        if (
            Array.isArray(clubSetting) &&
            clubSetting[currentWeekDay.value - 1]
        ) {
            result = clubSetting[currentWeekDay.value - 1];
        } else {
            result = (
                getGlobalLimitSettingValue(
                    'offline_reservation_slot_limit',
                ) as (number | null)[]
            )?.[currentWeekDay.value - 1];
        }

        return result;
    });

    const offlineReservationDurationLimit = computed<number | null>(() => {
        let result = null;

        const offlineDurationLimitKey =
            widgetStore.getSettingKey(
                'has_offline_reservation_limits_settings',
                'offline_reservation_duration_limit',
            ) || '';
        const clubSetting =
            widgetStore.settings[offlineDurationLimitKey]?.value;

        if (
            Array.isArray(clubSetting) &&
            clubSetting[currentWeekDay.value - 1]
        ) {
            result = clubSetting[currentWeekDay.value - 1];
        } else {
            result = (
                getGlobalLimitSettingValue(
                    'offline_reservation_duration_limit',
                ) as (number | null)[]
            )?.[currentWeekDay.value - 1];
        }

        return result;
    });

    const offlineReservationDailyLimit = computed<number | null>(() => {
        let result = null;

        const offlineDailyLimitKey =
            widgetStore.getSettingKey(
                'has_offline_reservation_limits_settings',
                'offline_reservation_daily_limit',
            ) || '';
        const clubSetting = widgetStore.settings[offlineDailyLimitKey]?.value;

        if (
            Array.isArray(clubSetting) &&
            clubSetting[currentWeekDay.value - 1]
        ) {
            result = clubSetting[currentWeekDay.value - 1];
        } else {
            result = (
                getGlobalLimitSettingValue(
                    'offline_reservation_daily_limit',
                ) as (number | null)[]
            )?.[currentWeekDay.value - 1];
        }

        return result;
    });

    const isOfflineReservationSlotLimitExceeded = computed<boolean>(() => {
        return (
            !!offlineReservationSlotLimit.value &&
            offlineReservationSlotLimit.value < widgetStore.form.slots_count
        );
    });

    const isOfflineReservationDurationLimitExceeded = computed<boolean>(() => {
        return (
            !!offlineReservationDurationLimit.value &&
            offlineReservationDurationLimit.value <
                widgetStore.form.duration / 60
        );
    });

    const isOfflineReservationDailyLimitExceeded = computed<boolean>(() => {
        return (
            !!offlineReservationDailyLimit.value &&
            offlineReservationDailyLimit.value < widgetStore.form.slots_count
        );
    });

    const offlinePaymentAvailable = computed<boolean>(() => {
        if (
            isOfflineReservationDailyLimitExceeded.value ||
            isOfflineReservationSlotLimitExceeded.value ||
            isOfflineReservationDurationLimitExceeded.value
        ) {
            return false;
        }

        return true;
    });

    const offlineDisabledMessage = computed<string>(() => {
        if (offlinePaymentAvailable.value) {
            return '';
        }

        let translationKey = 'offline-reservation-';

        if (
            isOfflineReservationSlotLimitExceeded.value &&
            isOfflineReservationDurationLimitExceeded.value
        ) {
            translationKey += 'slots-and-hours-limit-exceeded';
        } else if (isOfflineReservationSlotLimitExceeded.value) {
            translationKey += 'slots-limit-exceeded';
        } else if (isOfflineReservationDurationLimitExceeded.value) {
            translationKey += 'hours-limit-exceeded';
        }

        return widgetStore.gameTranslations[translationKey]
            ?.replace(
                ':slots',
                (offlineReservationSlotLimit.value || '').toString(),
            )
            ?.replace(
                ':hours',
                (offlineReservationDurationLimit.value || '').toString(),
            );
    });

    function getGlobalLimitSettingValue(key: string): any {
        return widgetStore.globalSettings[
            (Object.keys(widgetStore.globalSettings).find(
                (settingKey) =>
                    widgetStore.globalSettings[settingKey].feature?.game?.id ===
                        widgetStore.form.game_id && settingKey.includes(key),
            ) as string) ?? ''
        ]?.['value'];
    }

    return { offlinePaymentAvailable, offlineDisabledMessage };
}
