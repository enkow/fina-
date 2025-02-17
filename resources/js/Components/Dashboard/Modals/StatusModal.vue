<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50 delay-150">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto w-156 -translate-y-1/2 transform rounded-md bg-white !pb-5.5 text-center">
				<div class="pt-9 text-center text-3xl font-medium text-gray-7">
					<div v-if="type === 'success'" class="block w-full">
						<Mascot :type="15" class="max-w-40 m-auto" />
					</div>
					<div v-if="type === 'danger'">
						<Mascot :type="23" class="max-w-40 m-auto" />
					</div>
					<div class="mt-2 capitalize">
						<slot name="header"></slot>
					</div>
					<div
						class="absolute right-7 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>
				<div class="m-auto mt-3.5 max-w-132 font-light">
					<slot></slot>
				</div>
				<div class="modal-footer mt-6">
					<div v-if="type === 'success'" @click="hide">
						<Button class="brand lg mx-auto">OK</Button>
					</div>
					<div v-if="type === 'danger'" @click="hide">
						<Button class="danger lg mx-auto">OK</Button>
					</div>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		type?: string;
	}>(),
	{
		showing: false,
		type: 'success',
	},
);

const emit = defineEmits<{
	(e: 'close'): void;
}>();

const hide = () => {
	emit('close');
};
</script>
