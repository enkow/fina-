<template>
    <section
        v-if="
            widgetStore.gameFeatures.person_as_slot.length === 0 &&
            (widgetStore.isSlotSelectionEnabled ||
                widgetStore.gameFeatures.book_singular_slot_by_capacity
                    .length === 0)
        "
    >
        <SectionTitleContainer>
            <SectionTitle
                >{{ widgetStore.gameTranslations['choose-slot'] }}
            </SectionTitle>
            <ServiceState v-if="widgetStore.isSlotSelectionEnabled">
                <ServiceStateItem
                    variant="available"
                    :title="$t('calendar.available')"
                />
                <ServiceStateItem
                    v-if="widgetStore.gameFeatures.slot_has_convenience.length"
                    variant="optional"
                    :title="$t('calendar.can-be-used-for-children')"
                >
                    <template #icon>
                        <KidIcon class="h-auto w-5" />
                    </template>
                </ServiceStateItem>
                <ServiceStateItem
                    variant="selected"
                    :title="$t('calendar.selected')"
                />
            </ServiceState>
        </SectionTitleContainer>
        <AlleySelectorPicker v-if="widgetStore.isSlotSelectionEnabled" />
        <div v-else class="grid grid-cols-1 items-center gap-x-10 gap-y-7 lg:grid-cols-3">
            <AlleySelectorInput   />
        </div>
        <AlleySelectorClubMapButton />
    </section>
</template>

<script lang="ts" setup>
import SectionTitle from '../SectionTitle.vue';
import SectionTitleContainer from '../SectionTitleContainer.vue';
import ServiceState from '../ServiceState/ServiceState.vue';
import ServiceStateItem from '../ServiceState/ServiceStateItem.vue';
import AlleySelectorClubMapButton from './AlleySelectorClubMapButton.vue';
import AlleySelectorPicker from './AlleySelectorPicker/AlleySelectorPicker.vue';
import KidIcon from '@/Components/Widget-1/Icons/KidIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import AlleySelectorInput from './AlleySelectorInput.vue';

const widgetStore = useWidgetStore();
</script>
