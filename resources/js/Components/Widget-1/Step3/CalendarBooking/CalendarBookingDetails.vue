<template>
    <CalendarBookingContainer>
        <CalendarBookingSectionHeader
            :title="widgetStore.gameNames[widgetStore.form.game_id]"
            :price="widgetStore.price ?? 0"
            :price-before-discount="widgetStore.priceObject.priceBeforeDiscount"
            v-bind="{
                ...(widgetStore.oldPrice && { oldPrice: widgetStore.oldPrice }),
                ...(widgetStore.gameFeatures.person_as_slot?.length === 0 &&
                    widgetStore.form.slot_ids.length && { subTitle }),
            }"
        />
        <ul
            v-if="widgetStore.showingStatuses.pricePerPerson"
            class="space-y-1.5"
        >
            <li>
                {{
                    widgetStore.gameFeatures.price_per_person[0].translations[
                        'person-count'
                    ]
                }}:
                {{
                    widgetStore.form.features[
                        widgetStore.gameFeatures.price_per_person[0].id
                    ]['person_count']
                }}
            </li>
            <li>
                {{
                    widgetStore.gameFeatures.price_per_person[0].translations[
                        'calendar-cost-included'
                    ]
                }}
            </li>
        </ul>
    </CalendarBookingContainer>
</template>

<script lang="ts" setup>
import CalendarBookingSectionHeader from './CalendarBookingSectionHeader.vue';
import CalendarBookingContainer from './CalendarBookingContainer.vue';
import { useWidgetStore } from '@/Stores/widget';
import { formatSelectedSlots } from '@/Utils';
import { capitalize, computed } from 'vue';

const widgetStore = useWidgetStore();

const selectedSlots = computed(() =>
    widgetStore.slots.filter(({ id }) =>
        widgetStore.form.slot_ids.includes(id),
    ),
);

const slotHasTypeValue = computed(
    () =>
        widgetStore.gameFeatures.slot_has_type[0].translations[
            `type-${widgetStore.form.features[widgetStore.gameFeatures.slot_has_type[0]?.id]?.name}`
        ],
);

const slotHasSubTypeValue = computed(
    () =>
        widgetStore.form.features[
            widgetStore.gameFeatures.slot_has_subtype[0]?.id
        ].name,
);

const subTitleKey = computed(() => {
    const slotKey =
        selectedSlots.value.length > 1
            ? 'slot-plural-short'
            : 'slot-singular-short';

    return [
        `${capitalize(widgetStore.gameTranslations[slotKey])}`,
        ...(widgetStore.gameFeatures.slot_has_type.length
            ? [slotHasTypeValue.value]
            : []),
        ...(widgetStore.gameFeatures.slot_has_subtype.length
            ? [slotHasSubTypeValue.value?.toUpperCase()]
            : []),
    ].join(' ');
});
const subTitleValue = computed(() => formatSelectedSlots(selectedSlots.value, widgetStore.currentLocale));
const subTitle = computed(() => `${subTitleKey.value}: ${subTitleValue.value}`);
</script>
