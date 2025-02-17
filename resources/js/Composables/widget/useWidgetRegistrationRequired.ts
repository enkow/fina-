import { useWidgetStore } from "@/Stores/widget";
import { computed } from "vue";

export const useWidgetRegistrationRequired = () => {
  const widgetStore = useWidgetStore();

  const isRegistrationRequired = computed<boolean>(() => {
    return widgetStore.club.customer_registration_required;
  });

  return { isRegistrationRequired };
};
