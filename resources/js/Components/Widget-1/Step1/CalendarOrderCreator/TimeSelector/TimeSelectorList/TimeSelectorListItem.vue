<template>
    <li>
        <button
            type="button"
            :disabled="disabled"
            v-bind="disabled || { onClick: () => onClick?.(date) }"
            class="relative w-full select-none rounded-lg border-2 border-ui-green py-1.5 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:border-gray-2 disabled:text-gray-2 md:text-lg"
            :class="selected ? 'bg-ui-green text-white' : 'text-ui-black'"
        >
            {{ value }}
            <DiscountIcon
                class="absolute right-0 top-0 -mr-3 -mt-[10px] text-ui-green"
                v-if="isDiscount"
            />
        </button>
    </li>
</template>

<script lang="ts" setup>
import DiscountIcon from '@/Components/Widget-3/Icons/DiscountIcon.vue';
import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';

const props = defineProps<{
    selected?: boolean;
    disabled?: boolean;
    onClick?: (date: Date) => void;
    date: Date;
    value: string;
}>();

const widgetStore = useWidgetStore();

function formatDate(date: Date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

const isDiscount = computed(() => {
    return (
        (widgetStore.specialOffer &&
            widgetStore.isStartAtInSpecialOfferTimeRanges(
                widgetStore.specialOffer,
                formatDate(props.date),
                true,
                widgetStore.form.duration,
            )) ||
        ((widgetStore.specialOffer === null ||
            widgetStore.specialOffer.active_by_default === true) &&
            widgetStore.getActiveByDefaultSpecialOfferForGivenTimeBlock(
                formatDate(props.date),
            ) !== null)
    );
});
</script>
