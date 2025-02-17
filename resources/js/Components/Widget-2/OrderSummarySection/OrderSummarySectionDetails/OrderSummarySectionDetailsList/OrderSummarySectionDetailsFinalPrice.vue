<template>
	<WidgetUnstyledFinalPrice v-slot="{ finalPrice, currency, priceShowing }">
		<li>
			<WalletIcon />
			<OrderSummarySectionDetailsListContent>
				<template v-if="priceShowing">{{ finalPrice }} {{ currency }}</template>
				<template v-else>---</template>
			</OrderSummarySectionDetailsListContent>
		</li>
	</WidgetUnstyledFinalPrice>
</template>

<script lang="ts" setup>
import WidgetUnstyledFinalPrice from '@/Components/Widget/Unstyled/WidgetUnstyledFinalPrice.vue';
import OrderSummarySectionDetailsListContent from './OrderSummarySectionDetailsListContent.vue';
import WalletIcon from '@/Components/Widget-2/Icons/WalletIcon.vue';
</script>
