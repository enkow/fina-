<template>
    <ol
        class="grid grid-cols-1 gap-3 xxs:grid-cols-2 xs:grid-cols-4 sm:grid-cols-7 md:grid-cols-9 lg:grid-cols-12"
        v-if="widgetStore.datetimeBlocks.length !== 0"
    >
        <TimeSelectorListItem
            v-for="date in dates"
            :date="date"
            :value="dayjs(date).format('HH:mm')"
            :selected="isSelected(date)"
            :disabled="
                !widgetStore.datetimeBlocks.includes(
                    formatCalendarDateWithTime(date),
                )
            "
			:on-click="(date) => handleTimeClick(date)" />
    </ol>

    <WidgetAlert v-if="widgetStore.datetimeBlocks.length === 0">
        <template #title>{{
            wTrans('announcement.announcement').value
        }}</template>
        <template #description>
            {{ wTrans('widget.day-closed').value }}
        </template>
    </WidgetAlert>
</template>

<script lang="ts" setup>
import TimeSelectorListItem from './TimeSelectorListItem.vue';
import dayjs from 'dayjs';
import { useToast } from 'vue-toastification';
import { useWidgetStore } from '@/Stores/widget';
import {
    formatCalendarDate,
    formatCalendarDateWithTime,
    generateTimePickerDates,
    hasTime,
} from '@/Utils';
import { wTrans } from 'laravel-vue-i18n';
import { computed, watch } from 'vue';
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
import { useWidgetDurationSetting } from '@/Composables/widget/useWidgetDurationSetting';

const { min } = useWidgetDurationSetting(false);

const toast = useToast();
const widgetStore = useWidgetStore();

const MINUTES_DURATION = widgetStore.settings.full_hour_start_reservations_status.value ? 60 : 30;

const dates = computed(() =>
    widgetStore.openingHours
        ? generateTimePickerDates(
              {
                  date: formatCalendarDate(widgetStore.cachedFormStartAt),
                  start: widgetStore.openingHours.reservation_start,
                  end: widgetStore.openingHours.reservation_end,
              },
              MINUTES_DURATION,
          )
        : [],
);

const calculateIndexDifference = (date: Date) => {
    const baseDateIndex = dates.value.findIndex(
		(date) => date.getTime() === dayjs(widgetStore.form.start_at).toDate().getTime(),
    );
  const selectedDateIndex = dates.value.findIndex(
      (d) => d.getTime() === date.getTime(),
  );


  return selectedDateIndex - baseDateIndex;
};

const calculateSpaceDifference = () => (widgetStore.form.duration - MINUTES_DURATION) / MINUTES_DURATION + 1;

const isSelected = (date: Date) => {
    const { start_at: startAt } = widgetStore.form;

    if (!hasTime(startAt)) {
        return false;
    }

    if (startAt === formatCalendarDateWithTime(date)) {
        return true;
    }

    const indexDifference = calculateIndexDifference(date);
    const spaceDifference = calculateSpaceDifference();

    return indexDifference > 0 && indexDifference < spaceDifference;
};

const handleTimeClick = (date: Date) => {
    const { start_at: startAt, duration } = widgetStore.form;

    widgetStore.resetSlots();

	if (!hasTime(startAt)) {
		widgetStore.form.duration = MINUTES_DURATION;
		widgetStore.selectDatetime(formatCalendarDateWithTime(date));

		return;
	}

    if (formatCalendarDateWithTime(date) === startAt) {
        widgetStore.form.duration = 0;
        widgetStore.selectDatetime(formatCalendarDate(date));

        return;
    }

    if (
        widgetStore.showingStatuses.fixedReservationDuration ||
        widgetStore.showingStatuses.fullDayReservations
    ) {
        // const remainingDates = dates.value.slice(dates.value.indexOf(date));
        // let duration: number;
        // if (widgetStore.showingStatuses.fullDayReservations) {
        //     let firstDatetimeBlock = remainingDates[0];
        //     let lastDatetimeBlock = remainingDates[remainingDates.length - 1];
        //     duration =
        //         (lastDatetimeBlock.getTime() - firstDatetimeBlock.getTime()) /
        //             1000 /
        //             3600 +
        //         MINUTES_DURATION / 60;
        // } else {
        //     duration = widgetStore.settings[
        //         widgetStore.getSettingKey(
        //             'fixed_reservation_duration',
        //             'fixed_reservation_duration_value',
        //         ) || ''
        //     ].value as number;
        // }
        // if (remainingDates.length < duration * (60 / MINUTES_DURATION)) {
        //     toast.error(
        //         wTrans(
        //             'calendar.you-cannot-make-a-reservation-during-these-hours',
        //         ).value,
        //     );
        //     return;
        // }
        // widgetStore.form.duration = duration * 60;
        // widgetStore.selectDatetime(formatCalendarDateWithTime(date));
        // return;
    }

    if (!hasTime(startAt)) {
        widgetStore.form.duration = min.value || MINUTES_DURATION;
        widgetStore.selectDatetime(formatCalendarDateWithTime(date));

        return;
    }

	const durationLimitExceeded = duration >= widgetStore.gameDateWidgetDurationLimit;

    const indexDifference = calculateIndexDifference(date);
    const spaceDifference = calculateSpaceDifference();

    if (indexDifference === -1) {
        widgetStore.form.duration = durationLimitExceeded
            ? widgetStore.gameDateWidgetDurationLimit
            : duration + MINUTES_DURATION;
        widgetStore.selectDatetime(formatCalendarDateWithTime(date));
    } else if (indexDifference > 0 && indexDifference < spaceDifference) {
        widgetStore.form.duration = indexDifference * MINUTES_DURATION;
    } else if (indexDifference === spaceDifference) {
        if (durationLimitExceeded) {
            toast.error(
                wTrans('calendar.you-have-exceeded-the-maximum-booking-time')
                    .value,
            );
            return;
        }

        widgetStore.form.duration += MINUTES_DURATION;
    } else {
        toast.error(
            wTrans('calendar.you-can-only-select-adjacent-cells').value,
        );
    }

    if (min.value && widgetStore.form.duration < min.value) {
        widgetStore.form.duration = min.value;
    }
};

watch(
    () => widgetStore.datetimeBlocks,
    (blocks) => {
        const { start_at: startAt } = widgetStore.form;

        if (!hasTime(startAt)) return;

        if (blocks.includes(startAt)) {
            widgetStore.form.duration = MINUTES_DURATION;
        } else {
            widgetStore.form.duration = 0;
            widgetStore.selectDatetime(formatCalendarDate(startAt));
        }
    },
);

watch(
    () => widgetStore.startAtDatesLoadingStatus,
    (value) => {
        if (value) {
            widgetStore.form.duration = 0;
        }
    },
);
</script>
