<template>
    <ul class="grid grid-cols-2 gap-x-2 gap-y-6 lg:grid-cols-4">
        <CalendarOrderSummaryListItem
            :name="`${$t('calendar.date')}:`"
            :value="date"
        />
        <CalendarOrderSummaryListItem
            :name="`${$t('calendar.time')}:`"
            :value="
                hasTime(widgetStore.form.start_at)
                    ? `${time.from} - ${time.to}`
                    : '-'
            "
        />
        <CalendarOrderSummaryListItem
            v-if="widgetStore.showingStatuses.pricePerPerson && isSomeSlotIsSelected"
            :name="`${widgetStore.gameFeatures.price_per_person[0].translations['person-count']}:`"
            :value="
                String(
                    widgetStore.form.features[
                        widgetStore.gameFeatures.price_per_person[0].id
                    ]['person_count'],
                )
            "
        />
        <CalendarOrderSummaryListItem
            v-if="widgetStore.showingStatuses.personAsSlot"
            :name="`${$t('calendar.number-of-people')}:`"
            :value="widgetStore.form.slots_count"
        />
        <CalendarOrderSummaryListItem
            v-if="
                widgetStore.gameFeatures.person_as_slot?.length === 0 &&
                widgetStore.form.slot_ids.length &&
                widgetStore.form.duration !== 1
            "
            :name="`${widgetStore.gameTranslations['slot-plural-short']}:`"
            :value="
                formatedSelectedSlots == []
                    ? slotCheckedNumber
                    : formatedSelectedSlots
            "
            :isSingular="isSingular"
            :singleTitle="`${widgetStore.gameTranslations['slot-singular-short']}:`"
        />
        <CalendarOrderSummaryListItem
            v-if="
                widgetStore.showingStatuses.pricePerPerson &&
                !widgetStore.priceLoadingStatus && isSomeSlotIsSelected
            "
            :name="`${widgetStore.gameFeatures.price_per_person[0].translations['calendar-cost-label']}:`"
            :value="`${formatPrice(costOfShoes)} ${widgetStore.getCurrencySymbol()}`"
        >
            <template v-if="pricePerPersonIcon" #icon>
                <span v-html="pricePerPersonIcon" />
            </template>
        </CalendarOrderSummaryListItem>
        <CalendarOrderSummaryListItem
            v-if="widgetStore.specialOffer"
            :name="`${$t('calendar.discount')}:`"
            :value="`${widgetStore.specialOffer.name} (-${widgetStore.specialOffer.value}%)`"
        />
    </ul>
</template>

<script lang="ts" setup>
import CalendarOrderSummaryListItem from './CalendarOrderSummaryListItem.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed, watch, ref } from 'vue';
import {
    formatCalendarOrderDate,
    formatCalendarOrderTime,
    formatPrice,
    hasTime,
} from '@/Utils';
import { useFormatSelectedSlots } from '@/Composables/useFormatSelectedSlots';

const widgetStore = useWidgetStore();
const { formatedSelectedSlots } = useFormatSelectedSlots();

const pricePerPersonType = computed(() => {
    const pricePerPersonProps =
        widgetStore.getSettingKey('price_per_person', 'price_per_person') || '';
    const id = widgetStore.settings[pricePerPersonProps]?.feature?.id;
    const pricePerPersonTypeKey = `price_per_person_type_${id}`;

    return widgetStore.settings[pricePerPersonTypeKey]?.value;
});

const pricePerPersonIcon = computed(
    () => widgetStore.gameFeatures.price_per_person[0].data.icon,
);

const date = computed(() =>
    formatCalendarOrderDate(
        widgetStore.form.start_at,
        widgetStore.currentLocale,
    ),
);
const time = computed(() =>
    formatCalendarOrderTime(
        widgetStore.form.start_at,
        widgetStore.form.duration,
    ),
);

const personCount = computed(() => {
    return widgetStore.form.features[
        widgetStore.gameFeatures.price_per_person[0].id
    ].person_count;
});

const isSingular = computed(() => {
    return widgetStore.form.slots_count && widgetStore.form.slots_count === 1;
});

const slotCheckedNumber = computed(() => {
    return widgetStore.form.slots_count;
});

const pricePerPerson = ref<number>(0);
const costOfShoes = ref<number>(0);

watch(
    () => widgetStore.priceLoadingStatus,
    () => {
        if (widgetStore.priceLoadingStatus) return;

        const pricePerPersonProps =
            widgetStore.getSettingKey('price_per_person', 'price_per_person') ||
            '';

        if (pricePerPersonType.value === 2) {
            pricePerPerson.value = widgetStore.finalPrice / personCount.value;
            costOfShoes.value = widgetStore.finalPrice / personCount.value;
        } else {
            pricePerPerson.value = Number(
                widgetStore.settings[pricePerPersonProps]?.value,
            );
            costOfShoes.value =
                personCount.value *
                (typeof pricePerPerson.value === 'number'
                    ? pricePerPerson.value
                    : 1);
        }
    },
    {
        immediate: true,
    },
);



const isSomeSlotIsSelected = computed<boolean>(() => {
	return (widgetStore.showingStatuses.fullDayReservations || 
            hasTime(widgetStore.form.start_at)) &&
            widgetStore.form.slots_count > 0;
})
</script>
