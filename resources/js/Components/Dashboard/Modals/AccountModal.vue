<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto w-156 -translate-y-1/2 transform rounded-md bg-white !pb-5.5 text-center">
				<div class="h-23 border-b pt-10 text-center text-3xl font-medium text-gray-7">
					{{ $t('auth.you-are-logged-in-as') }}
					<div
						class="absolute right-7 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>
				<div class="text-2xl text-brand-base">
					<p class="pt-7 font-bold leading-9">
						{{ usePage().props.user.first_name + ' ' + usePage().props.user.last_name }}
					</p>
					<p v-if="usePage().props.user.club" class="font-medium">
						{{ usePage().props.user.club.name }}
					</p>
					<div class="flex w-full items-center justify-center space-x-3">
						<Button class="grey mt-7 text-sm" @click="goToSettings">{{ $t('settings.plural') }}</Button>
						<Button class="brand mt-7 text-sm" @click="$emit('logout')">{{ $t('auth.logout') }}</Button>
					</div>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import { router, usePage } from '@inertiajs/vue3';

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
	(e: 'logout'): void;
}>();
const hide = () => {
	if (props.showing) emit('close');
};

function goToSettings(): void {
	emit('close');
	router.visit(route('profile.show'));
}
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
	transition-delay: 150ms;
}
</style>
