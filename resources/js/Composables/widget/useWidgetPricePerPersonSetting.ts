import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useWidgetPricePerPersonSetting = () => {
    const widgetStore = useWidgetStore();

    const active = computed(() => widgetStore.showingStatuses.pricePerPerson);
    const icon = computed(
        () => widgetStore.gameFeatures.price_per_person[0].data.icon,
    );
    const label = computed(
        () =>
            widgetStore.gameFeatures.price_per_person[0].translations[
                'person-count'
                ],
    );
    const min = computed(() => widgetStore.gameDateSlotPeopleMinLimit * widgetStore.form.slots_count);
    const max = computed(() => {
        const isSlotsSelected = widgetStore.form.slots_count !== 0;

        if (!isSlotsSelected) {
            return 1000;
        }

        return (
            widgetStore.gameDateSlotPeopleMaxLimit *
            widgetStore.form.slots_count
        );
    });
    const model = computed(
        () =>
            widgetStore.form.features[
                widgetStore.gameFeatures.price_per_person[0]?.id
                ],
    );

    return { active, icon, label, min, max, model };
};
