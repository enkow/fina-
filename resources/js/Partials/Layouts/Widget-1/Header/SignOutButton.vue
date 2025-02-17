<template>
	<button type="button" @click="handleButtonClick" class="flex gap-x-3 text-ui-green sm:w-33">
		<span class="hidden sm:inline">{{ $t('calendar.sign-out') }}</span>
		<LoginIcon class="rotate-180 text-ui-black" />
	</button>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { signOut } from '@/Services/auth';
import LoginIcon from '@/Components/Widget-1/Icons/LoginIcon.vue';

const widgetStore = useWidgetStore();

const handleButtonClick = async () => {
	await signOut(
		{ club: widgetStore.club },
		{
			onSuccess: () => {
				widgetStore.customer = null;
			},
		},
	);
};
</script>
