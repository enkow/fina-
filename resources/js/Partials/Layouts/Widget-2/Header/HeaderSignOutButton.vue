<template>
	<WidgetUnstyledSignOutButton
		v-if="isAuth"
		aria-label="Sign out"
		class="text-ui-green-800"
		:content="
			createSignOutButtonLabel({
				color: 'var(--ui-green-950)',
				email: widgetStore.customer?.email || '',
				text: $t('auth.logout'),
			})
		"
		v-tippy="{ placement: 'bottom', allowHTML: true, offset: [0, 15] }">
		<SignOutIcon />
	</WidgetUnstyledSignOutButton>
</template>

<script lang="ts" setup>
import WidgetUnstyledSignOutButton from '@/Components/Widget/Unstyled/WidgetUnstyledSignOutButton.vue';
import SignOutIcon from '@/Components/Widget-2/Icons/SignOutIcon.vue';
import { useWidgetStore } from '@/Stores/widget';
import { useAuth } from '@/Composables/useAuth';
import { createSignOutButtonLabel } from '@/Utils';

const widgetStore = useWidgetStore();
const { isAuth } = useAuth();
</script>
