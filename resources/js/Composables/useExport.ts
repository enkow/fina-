import { ref, watch } from 'vue';
import { useQueryString } from '@/Composables/useQueryString';

export function useExport(routeName: string, routeAttributes: Record<string, any>) {
	const exportType = ref(null);
	const { queryArray } = useQueryString();

	watch(exportType, async () => {
		if (exportType.value !== null) {
			let currentQueryArray: Record<string, string> = queryArray(window.location.search);
			currentQueryArray['extension'] = exportType.value;
			for (const [key, value] of Object.entries(routeAttributes)) {
				currentQueryArray[key] = value.toString();
			}
			// @ts-ignore
			window.open(route(routeName, currentQueryArray), '_self');
			exportType.value = null;
		}
	});

	return exportType;
}
