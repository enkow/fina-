import { ref } from 'vue';

export const useDynamicResource = (source: Promise<{ default: string }>) => {
	const url = ref('');

	source.then(({ default: value }) => {
		url.value = value;
	});

	return url;
};
