import { computed } from 'vue';
import { useWidgetStore } from '@/Stores/widget';
import { SlotWithParent } from '@/Types/models';

export function useFormatSelectedSlots() {
    const widgetStore = useWidgetStore();

    const slotsData = computed(() => {
        return (
            widgetStore.featuresData[
                widgetStore.gameFeatures?.book_singular_slot_by_capacity[0]?.id
            ]?.slots_data || []
        );
    });

    const selectedSlots = computed(() => {
        const slots = widgetStore.slots.filter(({ id }) =>
            widgetStore.form.slot_ids.includes(id),
        );
        const slotsWithParent: SlotWithParent[] = slots.map((slot) => {
            return {
                ...slot,
                parent_slot_id: slotsData.value[slot.id]?.parent_slot_id,
            };
        });

        return slotsWithParent;
    });

    const formatedSelectedSlots = computed(() => {
        const locale = widgetStore.currentLocale;

        return selectedSlots.value
            .map((slot) => {
                const convenienceSlot = slot.features.find(
                    (feature) => feature.code === 'slot_has_convenience',
                );

                if (
                    convenienceSlot &&
                    JSON.parse(convenienceSlot?.pivot?.data as string)?.status
                ) {
                    const convenienceName = `${convenienceSlot.translations[locale]['slot-with-convenience']}`;
                    return `#${slot.name} (${convenienceName})`;
                }

                if (slot.parent_slot_id) {
                    const parentSlotName =
                        widgetStore.gameFeatures.slot_has_parent[0]
                            .translations['parent-slot'];
                    const parentSlotNumber = widgetStore.gameParentSlots.find(
                        (parentSlot) => parentSlot.id === slot.parent_slot_id,
                    )?.name;
                    return `(${parentSlotName} ${parentSlotNumber}) #${slot.name}`;
                }

                return `#${slot.name}`;
            })
            .join(', ');
    });

    return { formatedSelectedSlots };
}
