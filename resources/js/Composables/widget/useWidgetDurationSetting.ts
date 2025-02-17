import { useWidgetStore } from '@/Stores/widget';
import { computed, watch } from 'vue';

export const useWidgetDurationSetting = (isWatcher = true) => {
  const widgetStore = useWidgetStore();

  const active = computed(() => widgetStore.showingStatuses.duration);
  const label = computed(() => widgetStore.gameTranslations['game-time']);
  const durationLabel = computed(() => widgetStore.durationLabel);
  const min = computed(
      () =>
          widgetStore.specialOfferDuration ??
          widgetStore.gameDateWidgetDurationLimitMinimum ??
          widgetStore.durationStep,
  );
  const max = computed(
      () =>
          widgetStore.specialOfferDuration ??
          widgetStore.gameDateWidgetDurationLimit ??
          300,
  );
  const step = computed(() => widgetStore.durationStep);
  const model = computed(() => widgetStore.form);

  watch(
      () => widgetStore.form.duration,
      (value) => {
        if (value && isWatcher) {
          widgetStore.loadAvailableStartAtDatesTriggerer++;
        }
      },
  );

  return { active, label, durationLabel, min, max, step, model };
};
