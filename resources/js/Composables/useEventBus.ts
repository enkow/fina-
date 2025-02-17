import { ref } from 'vue';
const bus = ref(new Map());

export default function useEventsBus() {
	function emitCustomEvent(event: any, ...args: any[]) {
		bus.value.set(event, args);
	}

	return {
		emitCustomEvent,
		bus,
	};
}
