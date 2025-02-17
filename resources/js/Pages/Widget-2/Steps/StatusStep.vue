<template>
	<div
		class="widget-2-scrollbar flex h-full flex-col space-y-5 overflow-auto md:mx-auto md:h-fit md:max-w-md md:items-center md:overflow-visible">
		<h2 class="my-0 text-base font-bold md:text-lg">
			{{ statusHeading }}
			<span class="text-ui-green">#{{ reservationNumber }}</span>
		</h2>
		<p class="!mt-2 text-xs text-ui-black/60 md:!mt-3">{{ $t('widget-2.status-of-your-booking') }}</p>
		<ul class="w-full space-y-3 text-sm md:max-w-xs md:text-base">
			<li :class="LIST_ITEM_STYLES">
				<component :is="isSuccess ? TickIcon : CrossIcon" />
				{{ statusBadge }}
			</li>
			<li :class="LIST_ITEM_STYLES">
				<WalletIcon />
				{{ paymentBadge }}
			</li>
		</ul>
		<p class="text-xs text-ui-black/60 md:text-center" v-html="$t('widget-2.status-alert')"></p>
		<button type="button" class="text-sm font-medium text-ui-green-950" @click="widgetStore.fullFormReset">
			{{ $t('widget-2.start-over') }}
		</button>
	</div>
</template>

<script lang="ts" setup>
import CrossIcon from '@/Components/Widget-2/Icons/CrossIcon.vue';
import TickIcon from '@/Components/Widget-2/Icons/TickIcon.vue';
import WalletIcon from '@/Components/Widget-2/Icons/WalletIcon.vue';
import { useWidgetPayment } from '@/Composables/useWidgetPayment';
import { useWidgetStore } from '@/Stores/widget';
import { useRoutingStore } from '@/Stores/routing';

const LIST_ITEM_STYLES =
	'flex items-center gap-x-3 rounded-full bg-ui-green-300 p-3 font-medium text-ui-green-950 [&>svg]:text-ui-green';

const { statusHeading, reservationNumber, statusBadge, paymentBadge, isSuccess } = useWidgetPayment();
const widgetStore = useWidgetStore();
const routingStore = useRoutingStore();
</script>
