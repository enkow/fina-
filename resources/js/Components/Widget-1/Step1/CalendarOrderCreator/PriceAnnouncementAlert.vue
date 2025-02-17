<template>
    <WidgetAlert
        v-if="
            widgetStore.gameFeatures.has_price_announcements_settings.length &&
            hasPriceAnnouncementsSettings.value?.toString().length 
        "
    >
        <template #title>{{ $t('calendar.pay-attention') }}</template>
        <template #description>
            {{ hasPriceAnnouncementsSettings.value }}
        </template>
    </WidgetAlert>
</template>

<script lang="ts" setup>
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';
import { hasTime } from '@/Utils';

const widgetStore = useWidgetStore();

const hasPriceAnnouncementsSettings = computed(() => {
  return widgetStore.settings[
  widgetStore.getSettingKey(
      'has_price_announcements_settings',
      widgetStore.freeDay
          ? 'price_zero_announcement'
          : 'price_non_zero_announcement',
  ) || ''
      ];
});
</script>
