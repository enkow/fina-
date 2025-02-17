<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto w-156 -translate-y-1/2 transform rounded-md bg-white !pb-5.5 text-center">
				<div class="pt-9 text-center text-3xl font-medium text-gray-7">
					<div
						class="absolute right-7 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>
				<div class="m-auto mt-5.5 max-w-132 font-light">
					{{ usePage().props.user.club.first_login_message }}
				</div>
				<div class="modal-footer mt-6">
					<Button class="brand lg mx-auto" @click="hide">OK</Button>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { usePage } from '@inertiajs/vue3';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		type?: string;
	}>(),
	{
		showing: true,
		type: 'success',
	},
);

const emit = defineEmits<{
	(e: 'close'): void;
}>();

const hide = () => {
	axios.post(route('global.hide-first-login-message'));
	emit('close');
};
</script>
