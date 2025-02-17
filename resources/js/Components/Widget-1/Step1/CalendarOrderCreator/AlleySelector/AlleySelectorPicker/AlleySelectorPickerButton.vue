<template>
    <button
        :key="slotMessage.content || 1"
        v-tippy="slotMessage"
        type="button"
        @click="onClick"
        :class="
            twMerge(
                'relative flex aspect-square w-full flex-col items-center justify-center rounded-2.5xl border-2 border-ui-green font-medium transition-colors duration-150 md:text-lg',
                slotHasConvenienceData?.status && 'bg-ui-green-200',
                selected && 'bg-ui-green text-white',
            )
        "
    >
        <KidIcon
            v-if="slotHasConvenienceData?.status"
            class="absolute right-0.5 top-0.5 h-auto w-6.5"
        />
        {{ slot.name.length > 4 ? `${slot.name.slice(0, 4)}...` : slot.name }}
        <span
            v-if="bookSingularSlotByCapacityData"
            class="text-xs text-ui-black/40"
        >
            {{ bookSingularSlotByCapacityData.capacity }}-os
        </span>
    </button>
</template>

<script lang="ts" setup>
import KidIcon from '@/Components/Widget-1/Icons/KidIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';
import { twMerge } from 'tailwind-merge';
import type { Slot } from '@/Types/models';
import { useLoungeSlot } from '@/Composables/widget/useLoungeSlot';

const props = defineProps<{
    slot: Slot;
    selected?: boolean;
    message: string | null;
    onClick?: () => void;
}>();

const widgetStore = useWidgetStore();
const { loungeMessage } = useLoungeSlot(props.slot)

const bookSingularSlotByCapacityFeature = computed(() =>
    props.slot.features.find(
        ({ id }) =>
            widgetStore.gameFeatures.book_singular_slot_by_capacity[0]?.id ===
            id,
    ),
);

const slotHasConvenienceFeature = computed(() =>
    props.slot.features.find(
        ({ id }) => widgetStore.gameFeatures.slot_has_convenience[0]?.id === id,
    ),
);

const bookSingularSlotByCapacityData = computed(() =>
    bookSingularSlotByCapacityFeature.value?.pivot?.data
        ? (JSON.parse(bookSingularSlotByCapacityFeature.value.pivot.data) as {
              capacity: number;
          })
        : null,
);

const slotHasConvenienceData = computed(() =>
    slotHasConvenienceFeature.value?.pivot?.data
        ? (JSON.parse(slotHasConvenienceFeature.value.pivot.data) as {
              status: boolean;
          })
        : null,
);

const slotMessage = computed(() => {
    if(props.message){
        return {content: props.message};
    }

    if(props.slot.name.length > 4){
        return {content: props.slot.name};
    }

    if(loungeMessage.value){
        return {content: loungeMessage.value};
    }

    return {}
});
</script>
