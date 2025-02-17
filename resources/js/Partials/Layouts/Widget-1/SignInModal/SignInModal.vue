<template>
  <WidgetModal :is-open="isOpen" :on-close="onClose">
    <CalendarAuth>
      <template #title="{ selectedTabIndex }">
        {{ $t(TITLES[selectedTabIndex]) }}
      </template>
    </CalendarAuth>
  </WidgetModal>
</template>

<script setup lang="ts">
import WidgetModal from "@/Components/Widget/Ui/WidgetModal/WidgetModal.vue";
import CalendarAuth from "@/Components/Widget-1/Step3/CalendarAuth/CalendarAuth.vue";

defineProps<{
  isOpen: boolean;
  onClose: () => void;
  onForgotPasswordClick?: () => void;
}>();

const TITLES = ["calendar.sign-in", "calendar.sign-up"];
</script>
