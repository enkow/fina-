<template>
	<div
		v-if="showing"
		:class="{
			'bg-brand-light text-brand-base': type === 'success',
			'bg-danger-light text-danger-base': type === 'danger',
			'bg-warning-light/10 text-warning-base': type === 'warning',
		}"
		class="alert">
		<div v-if="type === 'success'" class="icon">
			<SuccessCircleIcon />
		</div>
		<div v-if="type === 'danger'" class="icon">
			<DangerCircleIcon />
		</div>
		<div v-if="type === 'warning'" class="icon">
			<DangerCircleIcon />
		</div>
		<slot></slot>
		<div v-if="closeButton" class="ml-1 w-5">
			<XIcon class="ml-2 h-3 w-3 cursor-pointer" @click="showing = false" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import SuccessCircleIcon from '@/Components/Dashboard/Icons/SuccessCircleIcon.vue';
import DangerCircleIcon from '@/Components/Dashboard/Icons/DangerCircleIcon.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import { Ref, ref } from 'vue';

const props = withDefaults(
	defineProps<{
		type: string;
		closeButton: boolean;
	}>(),
	{
		type: 'success',
		closeButton: true,
	},
);

const showing: Ref<Boolean> = ref(true);
</script>

<style scoped>
.alert {
	@apply flex w-full content-center items-center rounded-md px-6 py-4 align-middle font-medium;

	&.small {
		@apply flex h-9 rounded px-3 font-semibold;

		.icon {
			@apply mr-3;

			svg {
				@apply h-3.5 w-3.5;
			}
		}
	}

	.icon {
		@apply mr-5.5;

		svg {
			@apply h-6 w-6;
		}
	}
}
</style>
