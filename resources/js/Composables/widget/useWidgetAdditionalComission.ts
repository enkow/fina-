import { computed } from "vue";
import { useWidgetStore } from "@/Stores/widget";
import { useNumber } from '@/Composables/useNumber';


export const useWidgetAdditionalComission = () => {
    const { formatAmount } = useNumber();
    const widgetStore = useWidgetStore();

    const finalPrice = computed(() => +widgetStore.finalPrice);

    const commisionPercentCost = computed(() => {
        const commisionPercent = ((widgetStore.settings['additional_commission_percent']?.['value']) ?? 0) as number
        
        return finalPrice.value * commisionPercent / 10000;
    })

    const commisionFixedCost = computed(() => {
        return (widgetStore.settings['additional_commission_fixed']?.['value'] ?? 0) as number;
    });

    const isCommision = computed(() => commisionFixedCost.value || commisionPercentCost.value)

    const commisionPrice = computed(() => formatAmount(
        Math.round(commisionPercentCost.value + commisionFixedCost.value),
        widgetStore.club.country?.currency,
    ));

    return { commisionPrice, isCommision };
};