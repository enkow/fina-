<template>
	<button v-if="slot" class="h-4 w-4" @click="changeBulbStatus">
		<BulbFullyIcon :class="generateClass" />
	</button>
</template>

<script setup lang="ts">
import BulbFullyIcon from '@/Components/Dashboard/Icons/BulbFullyIcon.vue';
import { Slot, Game } from '@/Types/models';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import axios from 'axios';

const generateClass = computed(() => {
	if (!!props.slot) {
		if (props.slot.bulb_status === null || props.slot.bulb_status === undefined) {
			return 'hidden';
		} else if (props.slot.bulb_status === 1) {
			return 'text-green-500';
		} else if (props.slot.bulb_status === 2) {
			return 'text-red-500';
		} else if (props.slot.bulb_status === 3) {
			return 'text-red-300 cursor-not-allowed';
		}
	}
});

const changeBulbStatus = () => {
	if (props.slot && props.slot.bulb_status !== 3) {
		const status = props.slot.bulb_status === 2;
		props.slot.bulb_status = status ? 1 : 2;

		axios.put(
			route('club.games.slots.change-bulb', {
				game: props.game.id,
				slot: props.slot.id,
			}),
			{ status },
		);
	}
};

const props = defineProps<{
	slot?: Slot;
	game: Game;
}>();
</script>
