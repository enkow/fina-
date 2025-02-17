<template>
	<div class="mt-5.5 space-y-8 md:mt-7 lg:mt-14">
		<p>{{ $t('calendar.your-booking-status') }}</p>
		<ul class="flex flex-col gap-x-8.5 gap-y-5.5 md:flex-row">
			<li :class="ITEM_STYLES">
				<component :is="success ? TickIcon : CardSlash" :class="ICON_STYLES" />
				{{ statusMessage }}
			</li>
			<li :class="ITEM_STYLES">
				<WalletIcon :class="ICON_STYLES" />
				{{ paymentMessage }}
			</li>
		</ul>
	</div>
</template>

<script lang="ts" setup>
import TickIcon from '@/Components/Widget-1/Icons/TickIcon.vue';
import CardSlash from '@/Components/Widget-1/Icons/CardSlash.vue';
import WalletIcon from '@/Components/Widget-1/Icons/WalletIcon.vue';

defineProps<{
	success?: boolean;
	statusMessage: string;
	paymentMessage: string;
}>();

const ITEM_STYLES =
	'w-full p-7 bg-ui-green-300 text-ui-green-950 rounded-10 md:text-xl font-medium flex items-center text-start gap-x-6';
const ICON_STYLES = 'text-ui-green flex-shrink-0 w-12 aspect-square';
</script>
