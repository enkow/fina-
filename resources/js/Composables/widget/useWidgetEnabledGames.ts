import { useWidgetStore } from '@/Stores/widget';

export const useWidgetEnabledGames = () => {
	const widgetStore = useWidgetStore();

	return widgetStore.club.games?.filter(({ pivot }) => pivot.enabled_on_widget) || [];
};
