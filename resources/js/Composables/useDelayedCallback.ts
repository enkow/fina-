import { computed, ref, watch } from 'vue';

export const useDelayedCallback = (getter: () => boolean, callback: () => void) => {
	const active = ref(false);
	const computedGetter = computed(getter);

	watch([active, computedGetter], ([isActive, status]) => {
		if (isActive && status) {
			active.value = false;

			callback();
		}
	});

	return () => {
		active.value = true;
	};
};
