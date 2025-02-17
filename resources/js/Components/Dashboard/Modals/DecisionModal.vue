<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto flex w-156 -translate-y-1/2 transform flex-wrap rounded-md bg-white text-center">
				<div class="w-full py-9 text-3xl font-medium leading-10">
					<slot></slot>
				</div>
				<div class="flex w-full pb-10">
					<div class="mr-3 w-1/2">
						<Button class="secondary ml-auto w-56 capitalize" @click="$emit('decline')">
							{{ $t('main.no') }}
						</Button>
					</div>
					<div class="ml-3 w-1/2 text-left">
						<Button class="danger w-56 capitalize" @click="$emit('confirm')">
							{{ $t('main.yes') }}
						</Button>
					</div>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
	}>(),
	{
		showing: false,
	},
);

const emit = defineEmits<{
	(e: 'close'): void;
	(e: 'confirm'): void;
	(e: 'decline'): void;
}>();

const hide = () => {
	emit('decline');
};
</script>
