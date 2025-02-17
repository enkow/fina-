<template>
	<div class="overflow-hidden bg-white shadow sm:rounded-lg">
		<SettlementTab>
			<template #title>
				<p class="font-semibold">{{ $t('settlement.fee-list-label') }}</p>
			</template>
			<template v-if="feeTable.length">
				<div v-for="fee in feeTable" class="flex w-full justify-between py-2 text-sm">
					<div class="font-light">{{ fee.label }}</div>
					<div class="font-bold">{{ fee.value }}</div>
				</div>
			</template>
			<template v-else>
				{{ $t('settlement.no-fees') }}
			</template>
		</SettlementTab>
	</div>
</template>

<script lang="ts" setup>
import SettlementTab from '@/Components/Dashboard/SettlementTab.vue';
import { Invoice } from '@/Types/models';
import { usePage } from '@inertiajs/vue3';
import { wTrans } from 'laravel-vue-i18n';
import { computed } from 'vue';

const props = defineProps<{
	currencySymbol: string;
	invoice: Invoice;
}>();

const onlinePaymentSettings = props.invoice?.items?.filter((item) => !item.settings.period)[0]?.settings
	.online_payment_method;

function getFixedPartOfFee(fixedFee: string | number): string {
	if (typeof fixedFee === 'string') {
		fixedFee = parseInt(fixedFee);
	}
	if (fixedFee !== 0) {
		return ` + ${fixedFee / 100} ${props.currencySymbol}`;
	}
	return '';
}

function getFee(percentFee: number | string, fixedFee: number)
{
  let resultParts: string[] = [];
  if(percentFee && percentFee.length && !['0.00','0,00',''].includes(percentFee)) {
    resultParts.push(`${percentFee}%`);
  }
  if(onlinePaymentSettings.fee_fixed) {
    resultParts.push((fixedFee / 100).toString() + ' ' + props.currencySymbol);
  }
  return resultParts.length ? resultParts.join(' + ') : "---";
}

const feeTable = computed(() => [
	...(props.invoice?.items
		?.filter((item) => !item.settings.period)
		.map((item) => ({
			label: wTrans('settlement.fee-per-game', {
				game: usePage().props.gameTranslations[item.model.id]['game-name'],
			}).value,
			value: getFee(item.settings.fee_percent, item.settings.fee_fixed),
		})) || []),
	...(onlinePaymentSettings
		? [
				{
					label: wTrans('settlement.tpay-fee-single', { paymentProvider: onlinePaymentSettings.name }).value,
					value: getFee(onlinePaymentSettings.fee_percent, onlinePaymentSettings.fee_fixed),
				},
		  ]
		: []),
]);
</script>
