<template>
	<button type="button" @click="handleButtonClick">
		<slot />
	</button>
</template>

<script lang="ts" setup>
import { useWidgetStore } from '@/Stores/widget';
import { signOut } from '@/Services/auth';

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
