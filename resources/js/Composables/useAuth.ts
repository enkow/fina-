import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';

export const useAuth = () => {
	const widgetStore = useWidgetStore();
	const isAuth = computed(() => Boolean(widgetStore.customer?.verified));

	return { isAuth };
};
